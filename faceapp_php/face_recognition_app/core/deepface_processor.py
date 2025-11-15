# deepface_processor.py
# Lightweight replacement for DeepFace-based processor.
# - Uses YOLO (ultralytics) if accessory_model.pt exists (lazy-loaded)
# - Falls back to OpenCV Haar cascades for a heuristic glasses/eye check
# - Exposes the same high-level API your app expects:
#     - .available (bool)
#     - .detect_glasses(img_rgb, face_location, return_confidence=False)
#     - .analyze_face(img_rgb, face_location)
#
# NOTE: This file intentionally avoids importing DeepFace/TensorFlow.
#       YOLO model is loaded only when first needed (lazy loading).

import os
import threading
import numpy as np
from typing import Tuple, Any, Optional

# Try imports; if missing, set placeholders
try:
    # ultralytics uses 'ultralytics' package and provides YOLO class
    from ultralytics import YOLO
    YOLO_AVAILABLE = True
except Exception:
    YOLO_AVAILABLE = False

try:
    import cv2
    CV2_AVAILABLE = True
except Exception:
    CV2_AVAILABLE = False

# Haar cascade file (use bundled OpenCV frontal face/eyes if available)
_EYE_CASCADE = None
if CV2_AVAILABLE:
    try:
        _EYE_CASCADE = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_eye.xml")
        if _EYE_CASCADE.empty():
            _EYE_CASCADE = None
    except Exception:
        _EYE_CASCADE = None


class DeepFaceProcessor:
    """
    Minimal replacement for the original DeepFaceProcessor.
    YOLO is lazy-loaded to reduce startup time and memory pressure.
    """

    def __init__(self, model_path: Optional[str] = None):
        """
        model_path: optional path to a YOLO accessory model (.pt).
                    If not provided we will try "./core/models/accessory_model.pt"
        """
        # Public flag: deepface heavy stack removed -> False (means DeepFace analysis not available)
        self.available = False

        # YOLO attributes (lazy)
        self._yolo = None
        self._yolo_path = model_path or os.path.join(os.path.dirname(__file__), "models", "accessory_model.pt")
        self._yolo_lock = threading.Lock()
        # Indicate whether we *intend* to use YOLO (based on package + file existence).
        # Actual `self._yolo` remains None until loaded.
        self.using_yolo = YOLO_AVAILABLE and os.path.exists(self._yolo_path)

        # If YOLO not present, we'll rely on Haar heuristics when cv2 exists.
        # Note: self.available refers to DeepFace availability (kept False).
        # Keep object usable even if neither ultralytics nor cv2 are installed.
        # No eager loading here -> lazy loading on demand.

    # ---------- YOLO lazy loader ----------
    def _ensure_yolo(self) -> None:
        """
        Thread-safe loader for the YOLO model. If loading fails, disables YOLO usage.
        """
        if not self.using_yolo:
            return

        if self._yolo is not None:
            return

        with self._yolo_lock:
            if self._yolo is not None:
                return
            try:
                # Attempt to import & load model (do dynamic import just in case)
                if not YOLO_AVAILABLE:
                    # If package not available at runtime, mark as unavailable
                    self.using_yolo = False
                    return
                # Load the model (this is the expensive step)
                self._yolo = YOLO(self._yolo_path)
            except Exception:
                # Loading failed — disable YOLO usage to avoid repeated attempts
                self._yolo = None
                self.using_yolo = False

    # ---------- YOLO detection wrapper ----------
    def _yolo_detect_accessories(self, face_rgb: np.ndarray) -> Tuple[str, float]:
        """
        Run YOLO on the cropped face image. We look for classes that likely correspond
        to glasses / sunglasses. Because accessory model label names may vary,
        we search for substrings like "glass", "sunglass", "shade", "spectacl".
        Returns (label, confidence)
        """
        # Ensure model loaded (lazy)
        self._ensure_yolo()

        if not self._yolo:
            return ("none", 0.0)

        try:
            # ultralytics new API: call model on image (it accepts numpy arrays)
            results = self._yolo(face_rgb, verbose=False)
            for res in results:
                # results may contain res.boxes and res.names
                names = getattr(res, "names", {}) or {}
                boxes = getattr(res, "boxes", None)
                if boxes is None:
                    continue

                # boxes may be a tensor-like object; iterate items
                for box in boxes:
                    conf = 0.0
                    cls_idx = None
                    # Try to extract conf and cls robustly
                    try:
                        if hasattr(box, "conf"):
                            conf = float(box.conf.cpu().numpy())
                        elif hasattr(box, "data"):
                            # fallback: older APIs
                            data = box.cpu().numpy()
                            conf = float(data[4]) if len(data) >= 6 else 0.0
                    except Exception:
                        conf = 0.0

                    try:
                        if hasattr(box, "cls"):
                            cls_idx = int(box.cls.cpu().numpy())
                    except Exception:
                        cls_idx = None

                    label = names.get(cls_idx, "") if names and cls_idx is not None else ""
                    label_l = label.lower() if isinstance(label, str) else ""
                    if any(k in label_l for k in ("glass", "sunglass", "spectacl", "shade", "shades", "sun")):
                        return (label or "glasses", float(conf))
                    # if confident detection of anything, return it as accessory
                    if conf >= 0.5:
                        return (label or "accessory", float(conf))

            return ("none", 0.0)
        except Exception:
            return ("none", 0.0)

    # ---------- Haar cascade fallback ----------
    def _haar_eye_heuristic(self, face_gray: Any, face_rgb: Any) -> Tuple[str, float]:
        """
        Rough heuristic using Haar cascade:
        - If eyes are detected clearly -> likely NO sunglasses (return 'none', low conf)
        - If eyes are NOT detected -> could be occluded by sunglasses -> return 'sunglasses', medium conf
        """
        if _EYE_CASCADE is None or face_gray is None:
            return ("none", 0.0)

        try:
            eyes = _EYE_CASCADE.detectMultiScale(face_gray, scaleFactor=1.1, minNeighbors=3, minSize=(10, 10))
            if len(eyes) >= 1:
                return ("none", 0.0)
            else:
                return ("sunglasses", 0.6)
        except Exception:
            return ("none", 0.0)

    # ---------- Public API ----------
    def detect_glasses(self, img_rgb: Any, face_location: Any, return_confidence: bool = False) -> Any:
        """
        img_rgb: full image in RGB (numpy array)
        face_location: face bbox or coords. Flexible formats supported.
        return_confidence: if True, return (label, confidence), else return label (string)
        """
        # defensive shape check
        try:
            h, w = img_rgb.shape[:2]
        except Exception:
            if return_confidence:
                return ("none", 0.0)
            return "none"

        # Interpret face_location flexibly
        top = left = bottom = right = None
        try:
            if isinstance(face_location, (list, tuple)) and len(face_location) == 4:
                a, b, c, d = face_location
                # Heuristic: if a,b,c,d look like (top, right, bottom, left)
                if int(c) <= h and int(b) <= w and int(a) >= 0 and int(d) >= 0:
                    top, right, bottom, left = int(a), int(b), int(c), int(d)
                else:
                    # maybe (x,y,w,h)
                    x, y, ww, hh = face_location
                    left, top = int(x), int(y)
                    right, bottom = int(x + ww), int(y + hh)
            elif isinstance(face_location, dict):
                left = int(face_location.get("left") or face_location.get("x") or 0)
                top = int(face_location.get("top") or face_location.get("y") or 0)
                right = int(face_location.get("right") or left + int(face_location.get("width", 0)))
                bottom = int(face_location.get("bottom") or top + int(face_location.get("height", 0)))
        except Exception:
            left, top, right, bottom = 0, 0, w, h

        # clamp coords
        left = max(0, left or 0)
        top = max(0, top or 0)
        right = min(w, right or w)
        bottom = min(h, bottom or h)

        # crop face
        try:
            face_crop = img_rgb[top:bottom, left:right].copy()
        except Exception:
            face_crop = img_rgb

        # If configured to use YOLO, attempt lazy load + detection
        if self.using_yolo:
            label, conf = self._yolo_detect_accessories(face_crop)
            if return_confidence:
                return (label, float(conf))
            return label

        # fallback: Haar heuristics
        if CV2_AVAILABLE:
            try:
                face_gray = cv2.cvtColor(face_crop, cv2.COLOR_RGB2GRAY)
            except Exception:
                face_gray = None
            label, conf = self._haar_eye_heuristic(face_gray, face_crop)
            if return_confidence:
                return (label, float(conf))
            return label

        # last fallback: no detection
        if return_confidence:
            return ("none", 0.0)
        return "none"

    def analyze_face(self, img_rgb: Any, face_location: Any) -> dict:
        """
        Return a minimal analysis dictionary so app.debug_glasses and other
        callers don't break. This is not DeepFace analysis.
        """
        label, conf = self.detect_glasses(img_rgb, face_location, return_confidence=True)
        method = "yolo" if self.using_yolo and self._yolo else ("haar" if CV2_AVAILABLE else "none")
        return {
            "glasses": label,
            "glasses_confidence": float(conf),
            "method": method
        }
