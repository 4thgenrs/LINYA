# rebuild trigger
import os
import traceback
from typing import List, Optional, Tuple

import numpy as np

# ✅ PyTorch 2.6+ safe unpickling fix imports
try:
    import torch
    from torch.serialization import add_safe_globals
    TORCH_AVAILABLE = True
except Exception:
    torch = None
    add_safe_globals = None
    TORCH_AVAILABLE = False

try:
    from ultralytics import YOLO
    ULTRALYTICS_AVAILABLE = True
except Exception:
    YOLO = None
    ULTRALYTICS_AVAILABLE = False


class AccessoryDetector:
    """
    Uses Ultralytics YOLO classification model to detect accessories.

    Your .pt is a *classification* model (names like: no_glasses/with_glasses/with_hat)
    We do TORCH forward pass on self.model.model and use softmax probabilities.

    IMPORTANT FIX:
    - Do NOT rely only on top-1.
    - If with_glasses OR with_hat prob crosses threshold => block.
    """

    def __init__(
        self,
        model_path: str,
        conf: float = 0.35,          # classification threshold
        iou: float = 0.45,           # unused (kept for compatibility)
        max_det: int = 10,           # unused (kept for compatibility)
        block_classes: Optional[List[str]] = None,
        image_size: int = 640,       # kept for compatibility, but we’ll infer at 224
    ):
        self.model_path = model_path
        self.conf = float(conf)
        self.iou = float(iou)
        self.max_det = int(max_det)
        self.image_size = int(image_size)

        # If provided, ONLY these classes will be treated as "blocking"
        self.block_classes = set([c.strip() for c in (block_classes or []) if c.strip()])

        self.available = False
        self.model = None
        self.names = {}
        self.last_error = None  # exposed in model_status

        print(
            "[AccessoryDetector] init model_path=", self.model_path,
            "exists=", os.path.isfile(self.model_path),
            "ultralytics=", ULTRALYTICS_AVAILABLE,
            "torch=", TORCH_AVAILABLE,
            "conf=", self.conf,
            flush=True
        )

        if not ULTRALYTICS_AVAILABLE:
            self.last_error = "ultralytics not available"
            print("[AccessoryDetector] ultralytics not available.", flush=True)
            return

        if not os.path.isfile(self.model_path):
            self.last_error = f"model not found: {self.model_path}"
            print(f"[AccessoryDetector] model not found: {self.model_path}", flush=True)
            return

        try:
            # ✅ Fix PyTorch 2.6+ safe unpickling warnings/errors
            if TORCH_AVAILABLE and add_safe_globals is not None:
                try:
                    from ultralytics.nn.tasks import ClassificationModel
                    add_safe_globals([ClassificationModel])
                    print("[AccessoryDetector] Added safe global: ClassificationModel", flush=True)
                except Exception as e:
                    print("[AccessoryDetector] safe_globals skip:", e, flush=True)

            self.model = YOLO(self.model_path)
            self.names = getattr(self.model, "names", {}) or {}
            print("[AccessoryDetector] class names =", self.names, flush=True)

            if not TORCH_AVAILABLE or torch is None:
                self.last_error = "torch not available for inference"
                print("[AccessoryDetector] torch not available for inference", flush=True)
                return

            torch_model = getattr(self.model, "model", None)
            if torch_model is None:
                self.last_error = "cannot access underlying torch model (self.model.model is None)"
                print("[AccessoryDetector] cannot access underlying torch model", flush=True)
                return

            self.available = True
            print(f"[AccessoryDetector] Loaded model: {self.model_path}", flush=True)

        except Exception as e:
            self.last_error = f"{type(e).__name__}: {e}"
            print(f"[AccessoryDetector] Failed to load model: {e}", flush=True)
            print(traceback.format_exc(), flush=True)
            self.available = False
            self.model = None

    def _maybe_face_crop(
        self,
        img_rgb: np.ndarray,
        face_box: Optional[Tuple[int, int, int, int]] = None,  # (top, right, bottom, left)
    ) -> np.ndarray:
        if face_box is None:
            return img_rgb

        try:
            h, w = img_rgb.shape[:2]
            top, right, bottom, left = face_box

            pad_x = int(0.25 * (right - left))
            pad_top = int(0.35 * (bottom - top))
            pad_bot = int(0.15 * (bottom - top))

            x1 = max(0, left - pad_x)
            x2 = min(w, right + pad_x)
            y1 = max(0, top - pad_top)
            y2 = min(h, bottom + pad_bot)

            crop = img_rgb[y1:y2, x1:x2]
            if crop.size == 0:
                return img_rgb
            return crop
        except Exception:
            return img_rgb

    def detect(self, img_rgb: np.ndarray) -> List[str]:
        """
        Returns ["with_glasses"] / ["with_hat"] or [] if OK.

        FIX:
        - Block if with_glasses prob >= conf OR with_hat prob >= conf,
          even if top-1 is no_glasses.
        """
        if not self.available or self.model is None or torch is None:
            return []

        if img_rgb is None or not isinstance(img_rgb, np.ndarray) or img_rgb.size == 0:
            return []

        try:
            if img_rgb.dtype != np.uint8:
                img_rgb = img_rgb.astype(np.uint8)
            if img_rgb.ndim != 3 or img_rgb.shape[2] != 3:
                print(f"[AccessoryDetector] bad image shape: {getattr(img_rgb, 'shape', None)}", flush=True)
                return []
        except Exception as e:
            self.last_error = f"input normalize error: {type(e).__name__}: {e}"
            print("[AccessoryDetector] input normalize error:", e, flush=True)
            print(traceback.format_exc(), flush=True)
            return []

        try:
            torch_model = getattr(self.model, "model", None)
            if torch_model is None:
                self.last_error = "torch inference: cannot access underlying model"
                return []

            # -------- preprocessing --------
            target = 224
            x = torch.from_numpy(img_rgb).permute(2, 0, 1).float() / 255.0
            x = x.unsqueeze(0)
            x = torch.nn.functional.interpolate(
                x, size=(target, target), mode="bilinear", align_corners=False
            )

            # Put input on same device as model
            try:
                device = next(torch_model.parameters()).device
                x = x.to(device)
            except Exception:
                pass

            torch_model.eval()
            with torch.no_grad():
                out = torch_model(x)
                if isinstance(out, (list, tuple)):
                    out = out[0]
                p = torch.softmax(out, dim=1).detach().cpu()[0]  # (C,)

            # Print top-3 for debugging
            try:
                k = min(3, int(p.numel()))
                topk = torch.topk(p, k=k)
                for score, idx in zip(topk.values.tolist(), topk.indices.tolist()):
                    print(
                        f"[AccessoryDetector] TOP={self.names.get(int(idx), str(int(idx)))} conf={float(score):.3f}",
                        flush=True
                    )
            except Exception:
                pass

            # find indices
            idx_glasses = None
            idx_hat = None
            idx_no = None
            for k_id, k_name in self.names.items():
                if k_name == "with_glasses":
                    idx_glasses = int(k_id)
                elif k_name == "with_hat":
                    idx_hat = int(k_id)
                elif k_name == "no_glasses":
                    idx_no = int(k_id)

            conf_glasses = float(p[idx_glasses].item()) if idx_glasses is not None else 0.0
            conf_hat = float(p[idx_hat].item()) if idx_hat is not None else 0.0
            conf_no = float(p[idx_no].item()) if idx_no is not None else 0.0

            print(
                f"[AccessoryDetector] conf_no={conf_no:.3f} conf_glasses={conf_glasses:.3f} conf_hat={conf_hat:.3f} thr={self.conf:.3f}",
                flush=True
            )

            detected: List[str] = []

            # ✅ FIX: block by accessory probabilities, not only top-1
            if conf_glasses >= self.conf:
                if (not self.block_classes) or ("with_glasses" in self.block_classes):
                    detected.append("with_glasses")

            if conf_hat >= self.conf:
                if (not self.block_classes) or ("with_hat" in self.block_classes):
                    detected.append("with_hat")

            # If both pass threshold, return both (caller blocks anyway)
            print("[AccessoryDetector] detected =", detected, flush=True)
            return detected

        except Exception as e:
            self.last_error = f"torch detect error: {type(e).__name__}: {e}"
            print("[AccessoryDetector] torch detect error:", e, flush=True)
            print(traceback.format_exc(), flush=True)
            return []