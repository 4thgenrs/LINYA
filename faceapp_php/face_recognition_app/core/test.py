"""
Test script to verify YOLO glasses detection is working correctly.
Save this as: test_yolo_detection.py
Run from project root directory
"""

import cv2
import numpy as np
from ultralytics import YOLO
import os

def test_yolo_detection():
    # Load model
    yolo_path = "face_recognition_app/core/models/accessory_model.pt"
    print(f"Loading YOLO model from: {yolo_path}")
    
    if not os.path.exists(yolo_path):
        print(f"❌ ERROR: Model file not found at {yolo_path}")
        return
    
    model = YOLO(yolo_path)
    print(f"✅ Model loaded successfully")
    print(f"Classes: {model.names}")
    
    # Test with webcam
    cap = cv2.VideoCapture(0)
    
    if not cap.isOpened():
        print("❌ ERROR: Could not open webcam")
        return
    
    print("\n" + "="*60)
    print("YOLO GLASSES DETECTION TEST")
    print("="*60)
    print("Instructions:")
    print("  - Press 'c' to capture and test detection")
    print("  - Press 'q' to quit")
    print("="*60 + "\n")
    
    while True:
        ret, frame = cap.read()
        if not ret:
            break
        
        # Display frame
        cv2.putText(frame, "Press 'c' to test detection, 'q' to quit", 
                   (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
        cv2.imshow('YOLO Test - Webcam', frame)
        
        key = cv2.waitKey(1) & 0xFF
        
        if key == ord('c'):
            print("\n" + "-"*60)
            print("Testing detection...")
            
            # Save temp image
            temp_path = "temp_test.jpg"
            cv2.imwrite(temp_path, frame)
            
            # Run YOLO
            results = model(temp_path, verbose=False)
            
            if results and len(results) > 0:
                probs = results[0].probs
                
                # Get all class probabilities
                print("\n📊 Class Probabilities:")
                for idx, prob in enumerate(probs.data):
                    class_name = results[0].names[idx]
                    print(f"  {class_name}: {prob:.4f} ({prob*100:.2f}%)")
                
                # Get top prediction
                pred_idx = int(probs.top1)
                confidence = float(probs.top1conf)
                label = results[0].names[pred_idx]
                
                print(f"\n🎯 Top Prediction:")
                print(f"  Class: {label}")
                print(f"  Confidence: {confidence:.4f} ({confidence*100:.2f}%)")
                
                # Map to detection result
                if label == "with_glasses":
                    result = "glasses"
                elif label == "with_hat":
                    result = "hat"
                elif label == "no_glasses":
                    result = "none"
                else:
                    result = "unknown"
                
                print(f"\n✅ Detection Result: '{result}'")
            else:
                print("❌ No results from YOLO")
            
            # Cleanup
            try:
                os.remove(temp_path)
            except:
                pass
            
            print("-"*60)
        
        elif key == ord('q'):
            break
    
    cap.release()
    cv2.destroyAllWindows()
    print("\n✅ Test completed")

if __name__ == "__main__":
    test_yolo_detection()