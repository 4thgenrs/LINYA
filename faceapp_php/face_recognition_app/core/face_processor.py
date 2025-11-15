
import face_recognition
import cv2
import numpy as np
import os
import json
import shutil
from typing import Tuple, List, Dict, Optional, Any

class FaceProcessor:
    def __init__(self, db_dir: str):
        self.db_dir = db_dir
        self.user_encodings = {}  # {safe_name: {'display_name': str, 'encodings': np.ndarray}}
        self.flat_encodings = None  # numpy array (total_M,128)
        self.flat_names = []  # list of safe_name for each row in FLAT_ENCODINGS
        self.temp_angle_encodings = {}  # Temporary storage for registration angles
        
        # Create db_dir if it doesn't exist
        if not os.path.exists(self.db_dir):
            os.makedirs(self.db_dir)

    def resize_for_encoding(self, img_rgb: np.ndarray, max_width: int = 640) -> np.ndarray:
        """Downscale image if wider than max_width to speed up face detection/encoding."""
        try:
            h, w = img_rgb.shape[:2]
            if w <= max_width:
                return img_rgb
            scale = float(max_width) / float(w)
            new_w = int(w * scale)
            new_h = int(h * scale)
            return cv2.resize(img_rgb, (new_w, new_h), interpolation=cv2.INTER_LINEAR)
        except Exception:
            return img_rgb

    def get_face_encoding(self, img_rgb: np.ndarray) -> Tuple[List, List]:
        """Get face locations and encodings from an image."""
        small = self.resize_for_encoding(img_rgb)
        locations = face_recognition.face_locations(small, model='hog')
        encodings = face_recognition.face_encodings(small, known_face_locations=locations, num_jitters=1)
        return locations, encodings

    def build_flat_cache(self):
        """Flatten per-user encodings for fast vectorized comparison."""
        all_encs = []
        all_names = []
        for safe_name, info in self.user_encodings.items():
            user_encs = info.get("encodings")
            if user_encs is None:
                continue
            for e in user_encs:
                all_encs.append(e)
                all_names.append(safe_name)

        if all_encs:
            try:
                self.flat_encodings = np.stack(all_encs).astype('float32')
                self.flat_names = all_names
            except Exception as e:
                print(f"Error building flat cache: {e}")
                self.flat_encodings = None
                self.flat_names = []
        else:
            self.flat_encodings = None
            self.flat_names = []

    def load_encodings_cache(self):
        """Load all user face encodings from disk."""
        self.user_encodings = {}
        if not os.path.exists(self.db_dir):
            os.makedirs(self.db_dir)
            return
            
        for user_folder in os.listdir(self.db_dir):
            user_path = os.path.join(self.db_dir, user_folder)
            if not os.path.isdir(user_path):
                continue

            enc_file = os.path.join(user_path, "encoding.npy")
            info_file = os.path.join(user_path, "meta.json")

            if os.path.exists(enc_file):
                try:
                    enc = np.load(enc_file)
                    if enc.ndim == 1:
                        enc = np.expand_dims(enc, 0)

                    display_name = user_folder.replace("_", " ").title()
                    if os.path.exists(info_file):
                        try:
                            with open(info_file, "r", encoding="utf-8") as f:
                                meta = json.load(f)
                                display_name = meta.get("display_name", display_name)
                        except:
                            pass
                    self.user_encodings[user_folder] = {
                        "display_name": display_name,
                        "encodings": enc
                    }
                except Exception as e:
                    print(f"Failed to load encoding for {user_folder}: {e}")
            else:
                self._compute_and_save_encodings(user_folder, user_path)

        self.build_flat_cache()

    def _compute_and_save_encodings(self, user_folder: str, user_path: str):
        """Compute and save encodings for a user's images."""
        imgs = [os.path.join(user_path, f) for f in os.listdir(user_path) 
               if f.lower().endswith((".jpg",".png"))]
        encs = []
        for img in imgs:
            try:
                known = face_recognition.load_image_file(img)
                ke = face_recognition.face_encodings(known, num_jitters=1)
                if ke:
                    encs.append(ke[0])
            except Exception as e:
                print(e)

        if encs:
            enc_array = np.stack(encs).astype('float32')
            enc_file = os.path.join(user_path, "encoding.npy")
            try:
                np.save(enc_file, enc_array)
                self.user_encodings[user_folder] = {
                    "display_name": user_folder.replace("_", " ").title(),
                    "encodings": enc_array
                }
            except Exception as e:
                print(f"Could not save encoding.npy for {user_folder}: {e}")

    def face_exists(self, face_encoding: np.ndarray, tolerance: float = 0.45) -> Tuple[bool, Optional[str]]:
        """Check if a face encoding matches any known user."""
        if self.flat_encodings is not None:
            try:
                distances = face_recognition.face_distance(self.flat_encodings, face_encoding)
                best_idx = int(np.argmin(distances))
                if distances[best_idx] < tolerance:
                    matched_safe = self.flat_names[best_idx]
                    display_name = self.user_encodings[matched_safe].get(
                        "display_name", matched_safe.replace("_", " ").title()
                    )
                    return True, display_name
            except Exception as e:
                print(f"flat cache compare error: {e}")

        return False, None

    def check_angle_similarity(self, new_encoding: np.ndarray, 
                             captured_angles: List[str], 
                             current_angle: str,
                             tolerance: float = 0.4) -> Tuple[bool, Optional[str], Optional[float]]:
        """Check if a new angle is too similar to previously captured angles."""
        if not captured_angles:
            return False, None, None

        encodings = []
        angles = []
        for angle, enc in self.temp_angle_encodings.items():
            if angle in captured_angles:
                encodings.append(enc)
                angles.append(angle)

        if encodings:
            distances = face_recognition.face_distance(np.array(encodings), new_encoding)
            min_idx = np.argmin(distances)
            min_distance = distances[min_idx]

            if min_distance < tolerance:
                return True, angles[min_idx], float(1 - min_distance)

        # Store the new encoding
        if current_angle:
            self.temp_angle_encodings[current_angle] = new_encoding

        return False, None, None
    
    def clear_user_data(self, name: str) -> bool:
        """
        Completely remove a user's face data from both memory and disk.
        
        Args:
            name: The display name or safe name of the user to remove
            
        Returns:
            bool: True if user was found and removed, False otherwise
        """
        # First try exact match with safe_name
        safe_name = name.strip().replace(" ", "_").lower()
        user_found = False
        
        # Check if user exists by safe_name
        if safe_name in self.user_encodings:
            user_found = True
        else:
            # Try to find by display name
            for existing_safe, info in self.user_encodings.items():
                if info.get("display_name", "").lower() == name.lower():
                    safe_name = existing_safe
                    user_found = True
                    break

        if not user_found:
            return False

        # Remove from memory
        if safe_name in self.user_encodings:
            del self.user_encodings[safe_name]

        # Remove from disk
        user_path = os.path.join(self.db_dir, safe_name)
        if os.path.exists(user_path):
            try:
                shutil.rmtree(user_path)
            except Exception as e:
                print(f"Error removing user directory: {e}")
                return False

        # Rebuild flat cache to reflect changes
        self.build_flat_cache()
        return True

    def refresh_face_database(self):
        """
        Reload all face encodings from disk and rebuild the cache.
        Useful after manual changes to the database directory.
        """
        self.user_encodings = {}
        self.flat_encodings = None
        self.flat_names = []
        # If there is no user data, remove any leftover serialized caches and keep DB clean
        try:
            if not self.has_user_data():
                self.reset_database()
            else:
                self.load_encodings_cache()
        except Exception as e:
            print(f"refresh_face_database error: {e}")
            # Ensure the in-memory state is clean
            self.user_encodings = {}
            self.flat_encodings = None
            self.flat_names = []

    def has_user_data(self) -> bool:
        """
        Check whether the DB directory contains any user data (images, per-user encoding files,
        or global encoding/pickle/json files). Returns True if any user data exists.
        """
        try:
            for entry in os.listdir(self.db_dir):
                path = os.path.join(self.db_dir, entry)
                if os.path.isdir(path):
                    if not os.listdir(path):  # empty folder → remove it
                        os.rmdir(path)
                        continue
                    for f in os.listdir(path):
                        if f.lower().endswith((".jpg", ".jpeg", ".png", ".bmp")):
                            return True
                        if f.lower() in ("encoding.npy", "meta.json"):
                            return True
                elif os.path.isfile(path):
                    if entry.lower().endswith((".pkl", ".json", ".npy")) or entry.lower() in ("encodings.pkl", "face_data.json"):
                        return True
        except Exception:
            return False
        return False

    def reset_database(self, remove_user_dirs: bool = False) -> bool:
        """
        Completely clear in-memory encodings and remove persistent encoding files found in the DB
        directory root. Does not recreate user image folders; intended to remove stale serialized
        caches that might cause "stale" recognition.

        Returns True on success, False on failure.
        """
        # Clear memory
        self.user_encodings = {}
        self.flat_encodings = None
        self.flat_names = []
        self.temp_angle_encodings = {}

        # Recursively remove persistent serialized files anywhere under db_dir
        try:
            for root, dirs, files in os.walk(self.db_dir, topdown=False):
                # remove files that look like caches or encodings
                for fname in files:
                    if fname.lower().endswith((".pkl", ".json", ".npy")) or fname.lower() in ("encodings.pkl", "face_data.json", "encoding.npy", "meta.json"):
                        fpath = os.path.join(root, fname)
                        try:
                            os.remove(fpath)
                        except Exception as e:
                            print(f"Could not remove file {fpath}: {e}")

                # If requested, remove entire subdirectories regardless of contents
                if remove_user_dirs:
                    # don't remove the top-level db_dir itself
                    if root != self.db_dir:
                        try:
                            shutil.rmtree(root)
                        except Exception as e:
                            print(f"Could not remove directory {root}: {e}")
                else:
                    # After removing files, if directory is empty remove it to avoid stale folders
                    # but don't remove the top-level db_dir itself
                    try:
                        if root != self.db_dir and not os.listdir(root):
                            os.rmdir(root)
                    except Exception:
                        # ignore failures to remove non-empty or protected dirs
                        pass
        except Exception as e:
            print(f"reset_database error: {e}")
            return False

        return True