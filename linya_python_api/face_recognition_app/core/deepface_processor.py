# deepface_processor.py
# Stub implementation: glasses/accessory detection disabled.
# Kept only so existing imports/calls do not break.

from typing import Any, Tuple

import numpy as np  # only for type hints; optional


class DeepFaceProcessor:
    """
    Stub processor. We keep the same public methods:
      - available
      - detect_glasses(...)
      - analyze_face(...)
    but they always report no accessories.
    """

    def __init__(self, model_path: str | None = None):
        # In the original design, 'available' meant DeepFace stack is loaded.
        # We keep it False to indicate no advanced analysis.
        self.available = False
        self.using_yolo = False  # kept for compatibility

    def detect_glasses(
        self,
        img_rgb: Any,
        face_location: Any,
        return_confidence: bool = False
    ) -> Any:
        """
        Always report no glasses.
        """
        if return_confidence:
            return ("none", 0.0)
        return "none"

    def analyze_face(self, img_rgb: Any, face_location: Any) -> dict:
        """
        Minimal analysis dictionary for compatibility with callers.
        """
        return {
            "glasses": "none",
            "glasses_confidence": 0.0,
            "method": "none",
        }
