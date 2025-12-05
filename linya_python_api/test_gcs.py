#!/usr/bin/env python3
"""
Test script for GCS integration.

This script can be run locally or in Cloud Run to test GCS connectivity
and verify that the face recognition system can read/write to Google Cloud Storage.
"""

import os
import sys
import json
import numpy as np
from google.cloud import storage
from io import BytesIO

def test_gcs_connection():
    """Test basic GCS connectivity."""
    try:
        bucket_name = os.environ.get('GCS_BUCKET')
        if not bucket_name:
            print("❌ GCS_BUCKET environment variable not set")
            return False
            
        print(f"✅ GCS_BUCKET set to: {bucket_name}")
        
        # Initialize client
        client = storage.Client()
        bucket = client.bucket(bucket_name)
        
        # Test bucket access
        if bucket.exists():
            print(f"✅ Bucket {bucket_name} exists and is accessible")
        else:
            print(f"❌ Bucket {bucket_name} does not exist or is not accessible")
            return False
            
        return True, client, bucket
        
    except Exception as e:
        print(f"❌ GCS connection failed: {e}")
        return False

def test_file_operations(client, bucket):
    """Test basic file upload/download operations."""
    try:
        # Test data
        test_data = b"Hello, GCS!"
        test_blob_name = "test/test_file.txt"
        
        # Upload test
        print("🧪 Testing file upload...")
        blob = bucket.blob(test_blob_name)
        blob.upload_from_string(test_data)
        print("✅ File upload successful")
        
        # Download test
        print("🧪 Testing file download...")
        downloaded_data = blob.download_as_bytes()
        if downloaded_data == test_data:
            print("✅ File download successful")
        else:
            print("❌ Downloaded data doesn't match uploaded data")
            return False
            
        # Cleanup
        blob.delete()
        print("✅ Cleanup successful")
        
        return True
        
    except Exception as e:
        print(f"❌ File operations test failed: {e}")
        return False

def test_numpy_operations(client, bucket):
    """Test numpy array upload/download (simulating encoding.npy files)."""
    try:
        # Create test numpy array (simulating face encoding)
        test_array = np.random.rand(5, 128).astype('float32')  # 5 encodings, 128 dimensions
        test_blob_name = "test/test_encoding.npy"
        
        print("🧪 Testing numpy array upload...")
        
        # Upload numpy array
        with BytesIO() as buffer:
            np.save(buffer, test_array)
            buffer.seek(0)
            blob = bucket.blob(test_blob_name)
            blob.upload_from_file(buffer)
        print("✅ Numpy upload successful")
        
        # Download and verify
        print("🧪 Testing numpy array download...")
        downloaded_bytes = blob.download_as_bytes()
        downloaded_array = np.load(BytesIO(downloaded_bytes))
        
        if np.array_equal(test_array, downloaded_array):
            print("✅ Numpy download and verification successful")
        else:
            print("❌ Downloaded array doesn't match uploaded array")
            return False
            
        # Cleanup
        blob.delete()
        print("✅ Cleanup successful")
        
        return True
        
    except Exception as e:
        print(f"❌ Numpy operations test failed: {e}")
        return False

def test_face_recognition_structure(client, bucket):
    """Test the actual folder structure used by face recognition system."""
    try:
        from datetime import datetime
        
        # Simulate user registration
        safe_name = "test_user"
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        folder_path = f"db_pictures/{safe_name}/{timestamp}"
        
        print(f"🧪 Testing face recognition folder structure: {folder_path}")
        
        # Create test files
        files_to_create = [
            (f"{folder_path}/{safe_name}_1.jpg", b"fake-jpg-data-1"),
            (f"{folder_path}/{safe_name}_2.jpg", b"fake-jpg-data-2"),
            (f"{folder_path}/meta.json", json.dumps({"display_name": "Test User"}).encode()),
        ]
        
        # Upload files
        for blob_name, data in files_to_create:
            blob = bucket.blob(blob_name)
            blob.upload_from_string(data)
            print(f"✅ Uploaded {blob_name}")
        
        # Upload encoding.npy
        test_encoding = np.random.rand(2, 128).astype('float32')
        with BytesIO() as buffer:
            np.save(buffer, test_encoding)
            encoding_blob = bucket.blob(f"{folder_path}/encoding.npy")
            encoding_blob.upload_from_file(buffer)
            print(f"✅ Uploaded {folder_path}/encoding.npy")
        
        # List files to verify structure
        print("🧪 Verifying folder structure...")
        blobs = list(bucket.list_blobs(prefix=f"db_pictures/{safe_name}/"))
        expected_files = 4  # 2 JPGs + 1 meta.json + 1 encoding.npy
        
        if len(blobs) == expected_files:
            print(f"✅ Folder structure correct ({len(blobs)} files)")
            for blob in blobs:
                print(f"   📁 {blob.name}")
        else:
            print(f"❌ Expected {expected_files} files, found {len(blobs)}")
            return False
        
        # Cleanup
        for blob in blobs:
            blob.delete()
        print("✅ Cleanup successful")
        
        return True
        
    except Exception as e:
        print(f"❌ Face recognition structure test failed: {e}")
        return False

def main():
    """Run all GCS tests."""
    print("🚀 Starting GCS Integration Tests")
    print("=" * 50)
    
    # Test 1: Basic connectivity
    result = test_gcs_connection()
    if not result:
        print("\n❌ GCS connectivity test failed. Aborting.")
        sys.exit(1)
    
    success, client, bucket = result
    
    # Test 2: Basic file operations
    if not test_file_operations(client, bucket):
        print("\n❌ File operations test failed.")
        sys.exit(1)
    
    # Test 3: Numpy operations
    if not test_numpy_operations(client, bucket):
        print("\n❌ Numpy operations test failed.")
        sys.exit(1)
    
    # Test 4: Face recognition folder structure
    if not test_face_recognition_structure(client, bucket):
        print("\n❌ Face recognition structure test failed.")
        sys.exit(1)
    
    print("\n" + "=" * 50)
    print("🎉 All GCS integration tests passed!")
    print("✅ Your system is ready for production with GCS storage.")

if __name__ == "__main__":
    main()