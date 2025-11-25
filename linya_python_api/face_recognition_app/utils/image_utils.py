import base64
import cv2
import numpy as np
from typing import Optional

def decode_base64_to_rgb(image_b64: str, target_size=(128, 128)) -> Optional[np.ndarray]:
    """
    Decode base64 image (without data: prefix) into an RGB numpy array.
    Automatically resizes to YOLO's expected input size for consistency.
    """
    try:
        # Remove data URL prefix if present
        if ',' in image_b64:
            image_b64 = image_b64.split(',')[1]

        # Decode base64 to bytes
        img_bytes = base64.b64decode(image_b64)
        nparr = np.frombuffer(img_bytes, np.uint8)

        # Convert bytes to OpenCV image
        img_bgr = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        if img_bgr is None:
            return None

        # Convert to RGB color space
        img_rgb = cv2.cvtColor(img_bgr, cv2.COLOR_BGR2RGB)

        # ✅ Resize to 128x128 for YOLO (matches your training data)
        if target_size:
            img_rgb = cv2.resize(img_rgb, target_size, interpolation=cv2.INTER_AREA)

        return img_rgb

    except Exception as e:
        print(f"[decode_base64_to_rgb] Error: {e}")
        return None


def save_temp_image(image_data: str, file_path: str = './.tmp.jpg') -> str:
    """Save base64 image data to a temporary file."""
    try:
        if ',' in image_data:
            image_data = image_data.split(',')[1]

        with open(file_path, 'wb') as f:
            f.write(base64.b64decode(image_data))

        return file_path
    except Exception as e:
        print(f"[save_temp_image] Error: {e}")
        return ''
