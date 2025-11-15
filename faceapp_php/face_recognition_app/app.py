
import os
import base64
import json
import numpy as np
import cv2
import re
from typing import Dict, Any, List, Tuple, Optional
from flask import Flask, jsonify, request
from werkzeug.exceptions import RequestEntityTooLarge
from flask_cors import CORS
from .core.face_processor import FaceProcessor
from .core.deepface_processor import DeepFaceProcessor
from .utils.image_utils import decode_base64_to_rgb, save_temp_image

class FaceRecognitionApp:
    def __init__(self, db_dir: str):
        self.app = Flask(__name__)
        # Configure CORS. Keep it simple: allow localhost origins and enable credentials support.
        # Using the top-level parameters ensures flask-cors applies headers correctly.
        CORS(self.app,
             resources={r"/*": {"origins": "*"}},
             supports_credentials=True,
             allow_headers=["Content-Type", "Authorization", "Accept"])

        # Add a defensive after_request that always sets CORS headers for known origins.
        # This makes it explicit during debugging and prevents responses missing the header.
        @self.app.after_request
        def after_request(response):
            """
            Ensure CORS headers are present for browser requests.
            Echo the Origin header when present (so ports like http://localhost:80 are allowed)
            and fall back to wildcard for non-browser or missing Origin cases.
            """
            origin = request.headers.get('Origin')
            try:
                if origin:
                    # Echo the incoming Origin (this is safe for local/dev use and allows ports)
                    response.headers['Access-Control-Allow-Origin'] = origin
                else:
                    # No Origin header (server-to-server), allow all
                    response.headers['Access-Control-Allow-Origin'] = '*'

                response.headers['Access-Control-Allow-Headers'] = 'Content-Type,Authorization,Accept'
                response.headers['Access-Control-Allow-Methods'] = 'GET,POST,OPTIONS'
                # We allow credentials for development; echoing Origin above makes this valid
                response.headers['Access-Control-Allow-Credentials'] = 'true'
            except Exception:
                # Defensive: if headers can't be set, return response unchanged
                pass
            return response

        # Allow larger uploads (50 MB). Adjust as needed for your environment.
        # This avoids 413 Request Entity Too Large errors when clients upload several images.
        self.app.config['MAX_CONTENT_LENGTH'] = 50 * 1024 * 1024  # 50 MB

        # Register a friendly JSON error when payloads exceed the limit
        @self.app.errorhandler(RequestEntityTooLarge)
        def handle_request_too_large(error):
            return jsonify({
                'status': 'error',
                'message': 'Request entity too large. Reduce image size or increase server MAX_CONTENT_LENGTH.'
            }), 413
        self.db_dir = db_dir
        self.face_processor = FaceProcessor(db_dir)
        self.face_processor.reset_database(remove_user_dirs=False)
        self.deepface = DeepFaceProcessor()
        
        # Initialize face recognition system
        # If the DB folder has no user data, reset any serialized caches and keep memory empty.
        try:
            if not self.face_processor.has_user_data():
                # No user folders or encoding files present: ensure we don't load stale caches
                self.face_processor.reset_database()
            else:
                # Load existing encodings from disk
                self.face_processor.load_encodings_cache()
        except Exception as e:
            # On any error, ensure we have a clean in-memory state
            print(f"Error initializing face database: {e}")
            self.face_processor.reset_database()
        
        # Register routes
        self._register_routes()

    def _detect_glasses_compat(self, img_rgb, face_location):
        """
        Call deepface.detect_glasses and support both:
          - new signature: detect_glasses(img, loc, return_confidence=True) -> (label, conf)
          - old signature: detect_glasses(img, loc) -> label
        Always returns (label, confidence) where confidence is 0.0 when not available.
        """
        try:
            # Try calling with return_confidence (newer implementation)
            result = self.deepface.detect_glasses(img_rgb, face_location, return_confidence=True)
            # If the method returns a tuple (label, conf) — good
            if isinstance(result, (tuple, list)) and len(result) >= 2:
                return result[0], float(result[1] or 0.0)
            # If it returned a single string unexpectedly, handle below
            if isinstance(result, str):
                return result, 0.0
        except TypeError:
            # Method signature doesn't accept return_confidence — fallback
            try:
                single = self.deepface.detect_glasses(img_rgb, face_location)
                return (single, 0.0) if isinstance(single, str) else (str(single), 0.0)
            except Exception as e:
                # Any other error — return none
                return "none", 0.0
        except Exception:
            # Any unexpected error
            return "none", 0.0

        # Fallback
        return "none", 0.0


    def _register_routes(self):
        """Register all API endpoints."""
        self.app.route('/recognize', methods=['POST', 'OPTIONS'])(self.recognize)
        # Allow OPTIONS preflight on /register so browsers can preflight before POST
        self.app.route('/register', methods=['POST', 'OPTIONS'])(self.register_user)
        self.app.route('/check_angle', methods=['POST', 'OPTIONS'])(self.check_angle)
        self.app.route('/debug_glasses', methods=['POST', 'OPTIONS'])(self.debug_glasses)
        self.app.route('/model_status', methods=['GET'])(self.model_status)

    def recognize(self):
        """Handle face recognition requests."""
        try:
            data = request.get_json()
            if not data or 'image' not in data:
                return jsonify({"status": "error", "message": "No image provided"})

            # Process image
            unknown_image = decode_base64_to_rgb(data['image'], target_size=None)
            if unknown_image is None:
                return jsonify({"status": "error", "message": "Could not decode image"})

            # Get face locations and encodings
            locations, unknown_encodings = self.face_processor.get_face_encoding(unknown_image)
            if not unknown_encodings:
                return jsonify({"status": "no_person_found"})

            # Detect accessories with confidence
            glasses_type = 'none'
            confidence = 0.0
            if locations:
                # Assume detect_glasses can return (glasses_type, confidence)
                glasses_type, confidence = self._detect_glasses_compat(unknown_image, locations[0])

            # --- THRESHOLD CHECK ---
            CONFIDENCE_THRESHOLD = 0.5  # 50%
            if confidence < CONFIDENCE_THRESHOLD:
                glasses_type = 'none'
            # --- END THRESHOLD CHECK ---

            # ❌ Block if wearing any accessories above threshold
            if glasses_type != 'none':
                return jsonify({
                    "status": "accessory_detected",
                    "message": f"Login blocked: Please remove your {glasses_type} before continuing."
                })

            # Refresh database and try recognition
            self.face_processor.refresh_face_database()
            face_exists, name = self.face_processor.face_exists(unknown_encodings[0])

            if face_exists:
                # compute a safe_name to identify the folder / user key
                safe_name = re.sub(r"[^0-9a-zA-Z]+", "_", name.strip()).lower()
                return jsonify({
                    "status": "recognized",
                    "name": name,
                    "safe_name": safe_name,
                    "glasses": glasses_type
                })
            else:
                return jsonify({
                    "status": "unknown",
                    "message": "Unknown user. Please register first.",
                    "glasses": glasses_type
                })

        except Exception as e:
            return jsonify({"status": "error", "message": str(e)})


    def register_user(self):
        """Handle user registration requests."""
        if request.method == 'OPTIONS':
            # Handle preflight request
            response = jsonify({"status": "ok"})
            response.headers.add('Access-Control-Allow-Origin', request.headers.get('Origin', '*'))
            response.headers.add('Access-Control-Allow-Headers', 'Content-Type')
            response.headers.add('Access-Control-Allow-Methods', 'POST, OPTIONS')
            return response

        try:
            # Refresh in-memory face DB to avoid stale cache
            self.face_processor.refresh_face_database()

            name = request.form.get('name')
            images = request.form.getlist('images[]')

            if not name or not images:
                return jsonify({
                    'status': 'error',
                    'message': 'Missing name or images'
                }), 400

            # Process first image for validation
            img_data = images[0].split(',')[1] if ',' in images[0] else images[0]
            unknown_img = decode_base64_to_rgb(img_data, target_size=None)
            
            if unknown_img is None:
                return jsonify({
                    'status': 'error',
                    'message': 'Could not decode uploaded image.'
                }), 400

            # Check for face
            locations, face_encodings = self.face_processor.get_face_encoding(unknown_img)

            if not face_encodings:
                return jsonify({
                    'status': 'error',
                    'message': 'No face detected in the image. Please try again.'
                }), 400

            # Check for glasses/accessories
            if locations:
                glass_type = self.deepface.detect_glasses(unknown_img, locations[0])
                if glass_type and glass_type != 'none':
                    return jsonify({
                        'status': 'glasses',
                        'type': glass_type,
                        'message': f'Please remove your {glass_type} and retake the Front View.'
                    })

            # 🔥 OPTION B (DO NOT overwrite existing user)
            face_exists, existing_name = self.face_processor.face_exists(face_encodings[0])
            if face_exists:
                return jsonify({
                    'status': 'already_registered',
                    'message': f'Face already registered as {existing_name}. Please go to Login instead.',
                    'existing_name': existing_name
                }), 409  # HTTP Conflict

            # No conflict → Save user data
            return self._save_user_data(name, images)

        except Exception as e:
            import traceback
            print(f"Registration error: {e}")
            print(traceback.format_exc())

            return jsonify({
                'status': 'error',
                'message': str(e),
                'traceback': traceback.format_exc()
            }), 500


    def _save_user_data(self, name: str, images: List[str]) -> Dict[str, Any]:
        """Save user images and compute face encodings."""
        safe_name = name.strip().replace(" ", "_").lower()
        user_dir = os.path.join(self.db_dir, safe_name)
        os.makedirs(user_dir, exist_ok=True)

        saved_files = []
        for idx, img_data in enumerate(images, start=1):
            if ',' in img_data:
                img_data = img_data.split(',')[1]

            img_bytes = base64.b64decode(img_data)
            img_path = os.path.join(user_dir, f"{safe_name}_{idx}.jpg")
            
            with open(img_path, "wb") as f:
                f.write(img_bytes)
            saved_files.append(img_path)

        # Compute and save encodings
        enc_list = []
        for img_path in saved_files:
            try:
                img = cv2.imread(img_path)
                img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
                _, encodings = self.face_processor.get_face_encoding(img_rgb)
                if encodings:
                    enc_list.append(encodings[0])
            except Exception as e:
                print(f"Encoding error for {img_path}: {e}")

        if enc_list:
            enc_array = np.stack(enc_list).astype('float32')
            np.save(os.path.join(user_dir, "encoding.npy"), enc_array)

            # Save metadata
            meta = {"display_name": name}
            with open(os.path.join(user_dir, "meta.json"), "w", encoding="utf-8") as f:
                json.dump(meta, f, ensure_ascii=False)

            # Update cache
            self.face_processor.user_encodings[safe_name] = {
                "display_name": name,
                "encodings": enc_array
            }
            self.face_processor.build_flat_cache()

        return jsonify({
            'status': 'success',
            'message': f'{len(saved_files)} images saved for {name}',
            'folder': user_dir,
            'files': saved_files,
            'safe_name': safe_name
        })

    def check_angle(self):
        """Handle angle comparison during registration."""
        if request.method == 'OPTIONS':
            return jsonify({"status": "ok"}), 200, {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Methods': 'POST, OPTIONS',
                'Access-Control-Allow-Headers': 'Content-Type'
            }

        try:
            data = request.get_json()
            if not data or 'image' not in data:
                return jsonify({"status": "error", "message": "No image provided"})

            # Process image
            new_image = decode_base64_to_rgb(data['image'], target_size=None)
            if new_image is None:
                return jsonify({"status": "error", "message": "Could not decode image"})

            # Get face encoding
            locations, new_encodings = self.face_processor.get_face_encoding(new_image)
            
            if not new_encodings:
                return jsonify({"status": "error", "message": "No face detected"})

            # For front view, check for glasses
            current_angle = data.get('current_angle', '')
            captured_angles = data.get('captured_angles', [])
            
            if not captured_angles and current_angle.lower().startswith('front'):
                if locations:
                    glass_type = self.deepface.detect_glasses(new_image, locations[0])
                    if glass_type != 'none':
                        return jsonify({"status": "glasses", "type": glass_type})
                return jsonify({"status": "ok"})

            # Check angle similarity
            is_similar, similar_to, similarity = self.face_processor.check_angle_similarity(
                new_encodings[0],
                captured_angles,
                current_angle
            )

            if is_similar:
                return jsonify({
                    "status": "duplicate",
                    "similar_to": similar_to,
                    "similarity": similarity
                })

            return jsonify({"status": "ok"})

        except Exception as e:
            return jsonify({"status": "error", "message": str(e)})

    def debug_glasses(self):
        """Debug endpoint for glasses detection."""
        try:
            data = request.get_json()
            if not data or 'image' not in data:
                return jsonify({'status': 'error', 'message': 'No image provided'}), 400

            img = decode_base64_to_rgb(data['image'], target_size=None)
            if img is None:
                return jsonify({'status': 'error', 'message': 'Could not decode image'}), 400

            locations, _ = self.face_processor.get_face_encoding(img)
            
            if not locations:
                return jsonify({'status': 'error', 'message': 'No face detected'}), 400

            # Analyze face and detect glasses
            face_analysis = self.deepface.analyze_face(img, locations[0])
            # Assuming detect_glasses can return confidence as a second value
            glasses_type, confidence = self._detect_glasses_compat(img, locations[0])

            # --- THRESHOLD CHECK ---
            CONFIDENCE_THRESHOLD = 0.5  # 50%
            if confidence < CONFIDENCE_THRESHOLD:
                glasses_type = 'none'
            # --- END THRESHOLD CHECK ---

            return jsonify({
                'status': 'ok',
                'face_location': list(locations[0]),
                'glasses_type': glasses_type,
                'analysis': face_analysis,
                'deepface_available': self.deepface.available
            })

        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500


    def model_status(self):
        """Get status of available models and processors."""
        try:
            return jsonify({
                'deepface_available': self.deepface.available,
                'heuristic_fallback': True,
                'face_recognition_loaded': True
            })
        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500

    def run(self, host='0.0.0.0', port=5000):
        """Run the Flask application."""
        self.app.run(host=host, port=port)