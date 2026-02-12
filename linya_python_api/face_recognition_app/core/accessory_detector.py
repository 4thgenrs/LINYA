import os
import torch
from ultralytics import YOLO
import numpy as np

class AccessoryDetector:
    def __init__(self):
        model_path = os.path.join(
            os.path.dirname(__file__),
            "models",
            "accessory_model.pt"
        )

        self.model = YOLO(model_path)

        # Allowed accessory classes to block
        self.block_classes = {
            "eyeglasses",
            "sunglasses",
            "face_mask",
            "mask",
            "hat",
            "cap",
            "hood"
        }

    def detect(self, img_rgb: np.ndarray):
        results = self.model.predict(img_rgb, verbose=False)

        detected = []

        for r in results:
            for box in r.boxes:
                cls_id = int(box.cls[0])
                label = self.model.names[cls_id]

                if label.lower() in self.block_classes:
                    detected.append(label.lower())

        return list(set(detected))
