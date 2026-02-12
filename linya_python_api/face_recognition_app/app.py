
import os
import base64
import json
import numpy as np
import cv2
import re
import logging
import tempfile
from datetime import datetime
from typing import Dict, Any, List, Tuple, Optional
from flask import Flask, jsonify, request
from google.cloud import storage
from io import BytesIO
from .core.accessory_detector import AccessoryDetector


# Robust imports: Werkzeug and Flask-CORS may vary by environment/version.
try:
    from werkzeug.exceptions import RequestEntityTooLarge
except Exception:
    # Fallback: define a simple sentinel exception class so errorhandler can be registered.
    class RequestEntityTooLarge(Exception):
        pass

try:
    from flask_cors import CORS
    CORS_AVAILABLE = True
except Exception:
    CORS_AVAILABLE = False
from .core.face_processor import FaceProcessor
from .core.deepface_processor import DeepFaceProcessor
from .utils.image_utils import decode_base64_to_rgb, save_temp_image

class FaceRecognitionApp:
    def __init__(self, db_dir: str):
        self.app = Flask(__name__)
        # Configure CORS. Keep it simple: allow localhost origins and enable credentials support.
        # Using the top-level parameters ensures flask-cors applies headers correctly.
        if CORS_AVAILABLE:
            CORS(self.app,
                 resources={r"/*": {"origins": "*"}},
                 supports_credentials=True,
                 allow_headers=["Content-Type", "Authorization", "Accept"])
        else:
            # If flask_cors is not available, rely on the explicit after_request to set headers.
            logging.warning('flask_cors not available — proceeding without CORS extension')

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
        
        # Initialize storage client first
        self._init_storage_client()
        
        # Initialize face processor with GCS support
        self.face_processor = FaceProcessor(
            db_dir, 
            gcs_client=self.storage_client if self.use_gcs else None,
            bucket=self.bucket if self.use_gcs else None,
            use_gcs=self.use_gcs
        )
        self.face_processor.reset_database(remove_user_dirs=False)
        self.deepface = DeepFaceProcessor()
        self.accessory_detector = AccessoryDetector()

        # Initialize face recognition system
        # If the DB folder has no user data, reset any serialized caches and keep memory empty.
        try:
            # Always rebuild encodings from the current contents of db_pictures
            self.face_processor.refresh_face_database()
        except Exception as e:
            # On any error, ensure we have a clean in-memory state
            print(f"Error initializing face database: {e}")
            self.face_processor.reset_database()
        
        # Register routes
        self._register_routes()

    def _init_storage_client(self):
        """Initialize Google Cloud Storage client."""
        try:
            self.bucket_name = os.environ.get('GCS_BUCKET')
            if self.bucket_name:
                self.storage_client = storage.Client()
                self.bucket = self.storage_client.bucket(self.bucket_name)
                self.use_gcs = True
                logging.info(f"GCS initialized with bucket: {self.bucket_name}")
            else:
                self.storage_client = None
                self.bucket = None
                self.use_gcs = False
                logging.warning("GCS_BUCKET environment variable not set. Using local storage.")
        except Exception as e:
            self.storage_client = None
            self.bucket = None
            self.use_gcs = False
            logging.warning(f"Failed to initialize GCS: {e}. Using local storage.")

    def _upload_to_gcs(self, blob_name: str, data: bytes) -> bool:
        """Upload data to GCS bucket."""
        if not self.use_gcs:
            return False
        try:
            blob = self.bucket.blob(blob_name)
            blob.upload_from_string(data)
            return True
        except Exception as e:
            logging.error(f"Failed to upload {blob_name} to GCS: {e}")
            return False

    def _download_from_gcs(self, blob_name: str) -> bytes:
        """Download data from GCS bucket."""
        if not self.use_gcs:
            return None
        try:
            blob = self.bucket.blob(blob_name)
            return blob.download_as_bytes()
        except Exception as e:
            logging.error(f"Failed to download {blob_name} from GCS: {e}")
            return None

    def _list_gcs_blobs(self, prefix: str) -> List[str]:
        """List blob names in GCS bucket with given prefix."""
        if not self.use_gcs:
            return []
        try:
            blobs = self.bucket.list_blobs(prefix=prefix)
            return [blob.name for blob in blobs]
        except Exception as e:
            logging.error(f"Failed to list GCS blobs with prefix {prefix}: {e}")
            return []

    def _delete_gcs_blob(self, blob_name: str) -> bool:
        """Delete a blob from GCS bucket."""
        if not self.use_gcs:
            return False
        try:
            blob = self.bucket.blob(blob_name)
            blob.delete()
            return True
        except Exception as e:
            logging.error(f"Failed to delete {blob_name} from GCS: {e}")
            return False

    def _register_routes(self):
        """Register all API endpoints."""
        self.app.route('/recognize', methods=['POST', 'OPTIONS'])(self.recognize)
        # Allow OPTIONS preflight on /register so browsers can preflight before POST
        self.app.route('/register', methods=['POST', 'OPTIONS'])(self.register_user)
        self.app.route('/check_angle', methods=['POST', 'OPTIONS'])(self.check_angle)
        self.app.route('/model_status', methods=['GET'])(self.model_status)
        # Admin endpoints
        self.app.route('/admin/delete_user', methods=['POST', 'OPTIONS'])(self.admin_delete_user)
        self.app.route('/admin/refresh', methods=['POST', 'OPTIONS'])(self.admin_refresh)
        # Admin helper: list user folders and files under db_pictures (for debugging)
        self.app.route('/admin/list_users_files', methods=['GET'])(self.admin_list_users_files)

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

            # 🔵 Accessory Detection
            accessories = self.accessory_detector.detect(unknown_image)
            if accessories:
                return jsonify({
                    "status": "accessory_detected",
                    "message": f"Accessory detected: {', '.join(accessories)}. Please remove it and try again.",
                    "accessories": accessories
                })

            # 🔹 No more accessory detection / blocking here.
            # Just refresh DB and try recognition.
            self.face_processor.refresh_face_database()
            face_exists, name = self.face_processor.face_exists(unknown_encodings[0])

            if face_exists:
                # compute a safe_name to identify the folder / user key
                safe_name = re.sub(r"[^0-9a-zA-Z]+", "_", name.strip()).lower()
                return jsonify({
                    "status": "recognized",
                    "name": name,
                    "safe_name": safe_name,
                    # kept for compatibility with frontend, always 'none' now
                    "glasses": "none"
                })
            else:
                return jsonify({
                    "status": "unknown",
                    "message": "Unknown user. Please register first.",
                    "glasses": "none"
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

            # 🔵 Accessory Detection
            accessories = self.accessory_detector.detect(unknown_img)
            if accessories:
                return jsonify({
                    "status": "accessory_detected",
                    "message": f"Accessory detected: {', '.join(accessories)}. Please remove it and try again.",
                    "accessories": accessories
                }), 400

            # 🔹 No more glasses/accessory blocking here.
            # We just ensure a face is present.

            # Check if this face is already registered
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
        """Save user images and compute face encodings to GCS or local storage."""
        safe_name = re.sub(r"[^0-9a-zA-Z]+", "_", name.strip()).lower()
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        
        saved_files = []
        temp_files = []  # For GCS, we'll need temp files to compute encodings
        
        if self.use_gcs:
            # GCS path structure: db_pictures/<safe_name>/<timestamp>/
            folder_path = f"db_pictures/{safe_name}/{timestamp}"
            
            # Save images to GCS
            for idx, img_data in enumerate(images, start=1):
                if ',' in img_data:
                    img_data = img_data.split(',')[1]

                img_bytes = base64.b64decode(img_data)
                blob_name = f"{folder_path}/{safe_name}_{idx}.jpg"
                
                # Upload to GCS
                if self._upload_to_gcs(blob_name, img_bytes):
                    saved_files.append(blob_name)
                    
                    # Create temp file for encoding computation
                    temp_file = tempfile.NamedTemporaryFile(suffix='.jpg', delete=False)
                    temp_file.write(img_bytes)
                    temp_file.close()
                    temp_files.append(temp_file.name)
        else:
            # Local fallback
            folder_path = os.path.join(self.db_dir, safe_name)
            os.makedirs(folder_path, exist_ok=True)
            
            for idx, img_data in enumerate(images, start=1):
                if ',' in img_data:
                    img_data = img_data.split(',')[1]

                img_bytes = base64.b64decode(img_data)
                img_path = os.path.join(folder_path, f"{safe_name}_{idx}.jpg")
                with open(img_path, "wb") as f:
                    f.write(img_bytes)
                saved_files.append(img_path)

        # Compute encodings
        enc_list = []
        files_to_process = temp_files if self.use_gcs else saved_files
        
        for img_path in files_to_process:
            try:
                img = cv2.imread(img_path)
                img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
                _, encodings = self.face_processor.get_face_encoding(img_rgb)
                if encodings:
                    enc_list.append(encodings[0])
            except Exception as e:
                print(f"Encoding error for {img_path}: {e}")

        # Clean up temp files
        for temp_file in temp_files:
            try:
                os.unlink(temp_file)
            except Exception:
                pass

        encoding_b64 = None
        if enc_list:
            enc_array = np.stack(enc_list).astype('float32')
            
            if self.use_gcs:
                # Upload encoding.npy to GCS
                with BytesIO() as npy_buffer:
                    np.save(npy_buffer, enc_array)
                    npy_bytes = npy_buffer.getvalue()
                    self._upload_to_gcs(f"{folder_path}/encoding.npy", npy_bytes)
                    encoding_b64 = base64.b64encode(npy_bytes).decode('utf-8')
                
                # Upload meta.json to GCS
                meta = {"display_name": name}
                meta_bytes = json.dumps(meta, ensure_ascii=False).encode('utf-8')
                self._upload_to_gcs(f"{folder_path}/meta.json", meta_bytes)
                
            else:
                # Local fallback
                np.save(os.path.join(folder_path, "encoding.npy"), enc_array)
                
                meta = {"display_name": name}
                with open(os.path.join(folder_path, "meta.json"), "w", encoding="utf-8") as f:
                    json.dump(meta, f, ensure_ascii=False)
                
                # Encode the NPY file to base64 for transmission to PHP/DB
                enc_npy_path = os.path.join(folder_path, "encoding.npy")
                with open(enc_npy_path, "rb") as f:
                    encoding_b64 = base64.b64encode(f.read()).decode('utf-8')

            # Update cache
            self.face_processor.user_encodings[safe_name] = {
                "display_name": name,
                "encodings": enc_array
            }
            self.face_processor.build_flat_cache()

        # Return folder path (GCS URL for GCS, local path for local)
        if self.use_gcs:
            final_folder = f"gs://{self.bucket_name}/{folder_path}"
        else:
            final_folder = folder_path
        
        return jsonify({
            'status': 'success',
            'folder': final_folder,
            'encoding': encoding_b64
        })

    def check_angle(self):
        """Handle angle comparison during registration (now only validates face presence)."""
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

            # 🔹 No more glasses check or angle similarity check.
            # As long as a face is detected, we consider this angle acceptable.
            return jsonify({"status": "ok"})

        except Exception as e:
            return jsonify({"status": "error", "message": str(e)})



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


    def admin_delete_user(self):
        """
        Admin endpoint: safely delete user face data from GCS storage and memory cache.
        
        Accepts JSON:
          - { "safe_name": "john_doe" } OR
          - { "folder": "gs://bucket/db_pictures/john_doe/20251122_123456Z" }
        
        Security:
          - Requires X-ADMIN-KEY header if ADMIN_KEY env var is set
          - Only deletes under db_pictures/ prefix (safety guard)
          - Returns 500 if GCS_BUCKET not configured (prevents orphaned DB records)
        
        Returns:
          - 200: {"status":"ok", "deleted_count": N, "prefix": "..."}
          - 400: {"status":"error", "message": "Invalid safe_name"}
          - 403: {"status":"error", "message": "Unauthorized"}
          - 500: {"status":"error", "message": "GCS error: ..."}
        """
        if request.method == 'OPTIONS':
            return jsonify({"status": "ok"}), 200

        try:
            # 1. Security: check ADMIN_KEY if configured
            admin_key = os.environ.get('ADMIN_KEY')
            if admin_key:
                provided_key = request.headers.get('X-ADMIN-KEY', '').strip()
                if provided_key != admin_key:
                    logging.warning("Unauthorized delete_user attempt (invalid ADMIN_KEY)")
                    return jsonify({"status": "error", "message": "Unauthorized"}), 403

            # 2. Parse request
            data = request.get_json(force=True, silent=True) or {}
            folder = data.get('folder', '').strip()
            safe_name = data.get('safe_name', '').strip()

            # 3. Compute deletion prefix
            prefix = None
            if folder:
                # If folder starts with gs://, strip bucket and validate prefix
                if folder.startswith('gs://'):
                    # Remove gs://<bucket>/ to get the path within bucket
                    parts = folder.replace('gs://', '', 1).split('/', 1)
                    if len(parts) == 2:
                        prefix = parts[1]
                    else:
                        return jsonify({"status": "error", "message": "Invalid folder format"}), 400
                else:
                    prefix = folder
            elif safe_name:
                # Validate safe_name: only alphanumeric, underscore, dot, dash
                if not re.match(r'^[A-Za-z0-9_.-]+$', safe_name):
                    return jsonify({"status": "error", "message": "Invalid safe_name format"}), 400
                prefix = f"db_pictures/{safe_name}/"
            else:
                return jsonify({"status": "error", "message": "Missing safe_name or folder"}), 400

            # 4. Safety check: prefix must start with db_pictures/
            if not prefix.startswith('db_pictures/'):
                logging.error(f"Unsafe delete prefix rejected: {prefix}")
                return jsonify({"status": "error", "message": "Unsafe prefix - must be under db_pictures/"}), 400

            # 5. Require GCS to be configured
            if not self.use_gcs or not self.bucket:
                logging.error("GCS_BUCKET not configured - cannot delete user data")
                return jsonify({
                    "status": "error",
                    "message": "GCS_BUCKET environment variable not set. Cannot perform deletion."
                }), 500

            logging.info(f"[admin_delete_user] Starting deletion with prefix: {prefix}")

            # 6. List and delete all blobs under prefix
            deleted_count = 0
            try:
                blobs = list(self.bucket.list_blobs(prefix=prefix))
                for blob in blobs:
                    try:
                        blob.delete()
                        deleted_count += 1
                        logging.debug(f"Deleted blob: {blob.name}")
                    except Exception as e:
                        logging.error(f"Failed to delete blob {blob.name}: {e}")
                        raise  # Fail fast on deletion errors

            except Exception as e:
                logging.error(f"GCS deletion error for prefix {prefix}: {e}")
                return jsonify({
                    "status": "error",
                    "message": f"GCS error: {str(e)}"
                }), 500

            # 7. Clear from in-memory cache
            # Extract safe_name from prefix for cache removal
            if safe_name:
                cache_key = safe_name
            else:
                # Extract from prefix: db_pictures/<safe_name>/...
                parts = prefix.split('/')
                if len(parts) >= 2:
                    cache_key = parts[1]
                else:
                    cache_key = None

            if cache_key and cache_key in self.face_processor.user_encodings:
                del self.face_processor.user_encodings[cache_key]
                self.face_processor.build_flat_cache()
                logging.info(f"Removed {cache_key} from in-memory cache")

            logging.info(f"[admin_delete_user] Successfully deleted {deleted_count} objects with prefix: {prefix}")

            return jsonify({
                "status": "ok",
                "deleted_count": deleted_count,
                "prefix": prefix
            }), 200

        except Exception as e:
            logging.error(f"Unexpected error in admin_delete_user: {e}", exc_info=True)
            return jsonify({
                "status": "error",
                "message": f"Internal server error: {str(e)}"
            }), 500


    def admin_refresh(self):
        """Admin endpoint: force the face processor to reload encodings from disk.

        Returns the list of users loaded.
        """
        try:
            self.face_processor.refresh_face_database()
            users = list(self.face_processor.user_encodings.keys())
            return jsonify({'status': 'ok', 'users': users, 'count': len(users)})
        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500

    def admin_list_users_files(self):
        """Admin endpoint: list the contents of the db_pictures folder.

        Returns JSON mapping safe_name -> list of files (meta.json, encoding.npy, images...)
        """
        try:
            users = {}
            if not os.path.isdir(self.db_dir):
                return jsonify({'status': 'error', 'message': 'db_pictures directory not found', 'path': self.db_dir}), 404

            for name in sorted(os.listdir(self.db_dir)):
                user_dir = os.path.join(self.db_dir, name)
                if os.path.isdir(user_dir):
                    try:
                        files = sorted(os.listdir(user_dir))
                    except Exception:
                        files = []
                    users[name] = files

            return jsonify({'status': 'ok', 'users': users, 'count': len(users)})
        except Exception as e:
            return jsonify({'status': 'error', 'message': str(e)}), 500

    def run(self, host='0.0.0.0', port=5000):
        """Run the Flask application."""
        self.app.run(host=host, port=port)


# ============================================================
# ENTRY POINT FOR CLOUD RUN & GUNICORN
# ============================================================
# This creates the Flask app instance at module level for deployment
# platforms like Cloud Run and Gunicorn to discover and run.

def create_app():
    """
    Application factory for Cloud Run and WSGI servers.
    
    Returns a Flask application instance configured for face recognition.
    The db_dir (for storing user encodings) is set relative to this module,
    allowing flexible deployment to local, Docker, or Cloud Run environments.
    
    Note on persistence:
    - On Cloud Run, db_pictures/ is ephemeral (lost on container restart)
    - For production, consider plugging in Google Cloud Storage or Cloud SQL
    - See config.php comments for migration path
    """
    base_dir = os.path.dirname(os.path.abspath(__file__))
    db_dir = os.path.join(base_dir, '..', 'db_pictures')  # ../db_pictures relative to app.py
    os.makedirs(db_dir, exist_ok=True)
    
    face_app = FaceRecognitionApp(db_dir)
    return face_app.app


# Instantiate the Flask app at module level
# Cloud Run and Gunicorn will import this as the WSGI application
app = create_app()


if __name__ == "__main__":
    # Local development: run on configurable port (default 5000)
    import os as os_module
    port = int(os_module.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port, debug=False)