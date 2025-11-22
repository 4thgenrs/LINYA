"""
Integration test for /admin/delete_user endpoint.

Prerequisites:
- GCS_BUCKET environment variable must be set
- Google Cloud credentials must be configured
- ADMIN_KEY environment variable (optional, tested if present)

Run with: pytest tests/test_delete_user.py -v
"""

import os
import pytest
import requests
import uuid
from google.cloud import storage
from io import BytesIO
from PIL import Image

# Test configuration
API_BASE_URL = os.environ.get('TEST_API_URL', 'http://localhost:8080')
GCS_BUCKET = os.environ.get('GCS_BUCKET')
ADMIN_KEY = os.environ.get('ADMIN_KEY')

@pytest.fixture
def storage_client():
    """Create GCS storage client."""
    if not GCS_BUCKET:
        pytest.skip("GCS_BUCKET environment variable not set")
    return storage.Client()

@pytest.fixture
def test_blob_prefix(storage_client):
    """Create a unique test prefix and upload test blob."""
    if not GCS_BUCKET:
        pytest.skip("GCS_BUCKET environment variable not set")
    
    bucket = storage_client.bucket(GCS_BUCKET)
    
    # Generate unique test folder
    random_id = uuid.uuid4().hex[:8]
    safe_name = f"test_delete_user_{random_id}"
    prefix = f"db_pictures/{safe_name}/"
    blob_name = f"{prefix}test_image.jpg"
    
    # Create a tiny test image (1x1 pixel)
    img = Image.new('RGB', (1, 1), color='red')
    img_bytes = BytesIO()
    img.save(img_bytes, format='JPEG')
    img_bytes.seek(0)
    
    # Upload to GCS
    blob = bucket.blob(blob_name)
    blob.upload_from_file(img_bytes, content_type='image/jpeg')
    
    yield {
        'bucket': bucket,
        'prefix': prefix,
        'blob_name': blob_name,
        'safe_name': safe_name,
        'folder': f"gs://{GCS_BUCKET}/{prefix.rstrip('/')}"
    }
    
    # Cleanup: ensure test blobs are removed
    try:
        blobs = list(bucket.list_blobs(prefix=prefix))
        for b in blobs:
            b.delete()
    except Exception:
        pass

def test_delete_user_by_folder(test_blob_prefix):
    """Test deleting user by folder path."""
    # Verify blob exists before deletion
    blob = test_blob_prefix['bucket'].blob(test_blob_prefix['blob_name'])
    assert blob.exists(), "Test blob should exist before deletion"
    
    # Prepare request
    headers = {'Content-Type': 'application/json'}
    if ADMIN_KEY:
        headers['X-ADMIN-KEY'] = ADMIN_KEY
    
    payload = {'folder': test_blob_prefix['folder']}
    
    # Call delete endpoint
    response = requests.post(
        f"{API_BASE_URL}/admin/delete_user",
        json=payload,
        headers=headers
    )
    
    # Verify response
    assert response.status_code == 200, f"Expected 200, got {response.status_code}: {response.text}"
    data = response.json()
    assert data['status'] == 'ok', f"Expected status ok, got {data}"
    assert data['deleted_count'] >= 1, "Should delete at least 1 object"
    assert data['prefix'] == test_blob_prefix['prefix'].rstrip('/'), "Prefix should match"
    
    # Verify blob no longer exists
    blob.reload()
    assert not blob.exists(), "Test blob should not exist after deletion"

def test_delete_user_by_safe_name(test_blob_prefix):
    """Test deleting user by safe_name."""
    # Verify blob exists
    blob = test_blob_prefix['bucket'].blob(test_blob_prefix['blob_name'])
    assert blob.exists(), "Test blob should exist before deletion"
    
    # Prepare request
    headers = {'Content-Type': 'application/json'}
    if ADMIN_KEY:
        headers['X-ADMIN-KEY'] = ADMIN_KEY
    
    payload = {'safe_name': test_blob_prefix['safe_name']}
    
    # Call delete endpoint
    response = requests.post(
        f"{API_BASE_URL}/admin/delete_user",
        json=payload,
        headers=headers
    )
    
    # Verify response
    assert response.status_code == 200
    data = response.json()
    assert data['status'] == 'ok'
    assert data['deleted_count'] >= 1
    
    # Verify deletion
    blob.reload()
    assert not blob.exists()

def test_delete_user_idempotent(test_blob_prefix):
    """Test that deletion is idempotent (deleting non-existent is ok)."""
    headers = {'Content-Type': 'application/json'}
    if ADMIN_KEY:
        headers['X-ADMIN-KEY'] = ADMIN_KEY
    
    # Delete once
    payload = {'safe_name': test_blob_prefix['safe_name']}
    response1 = requests.post(f"{API_BASE_URL}/admin/delete_user", json=payload, headers=headers)
    assert response1.status_code == 200
    assert response1.json()['status'] == 'ok'
    
    # Delete again (should still return ok with 0 count)
    response2 = requests.post(f"{API_BASE_URL}/admin/delete_user", json=payload, headers=headers)
    assert response2.status_code == 200
    data = response2.json()
    assert data['status'] == 'ok'
    assert data['deleted_count'] == 0

def test_delete_user_invalid_safe_name():
    """Test that invalid safe_name is rejected."""
    headers = {'Content-Type': 'application/json'}
    if ADMIN_KEY:
        headers['X-ADMIN-KEY'] = ADMIN_KEY
    
    # Try with invalid characters
    payload = {'safe_name': '../../../etc/passwd'}
    response = requests.post(f"{API_BASE_URL}/admin/delete_user", json=payload, headers=headers)
    
    assert response.status_code == 400
    data = response.json()
    assert data['status'] == 'error'
    assert 'invalid' in data['message'].lower() or 'unsafe' in data['message'].lower()

def test_delete_user_unauthorized():
    """Test that missing ADMIN_KEY is rejected when required."""
    if not ADMIN_KEY:
        pytest.skip("ADMIN_KEY not set - skipping auth test")
    
    # Request without ADMIN_KEY header
    headers = {'Content-Type': 'application/json'}
    payload = {'safe_name': 'test_user'}
    
    response = requests.post(f"{API_BASE_URL}/admin/delete_user", json=payload, headers=headers)
    
    assert response.status_code == 403
    data = response.json()
    assert data['status'] == 'error'
    assert 'unauthorized' in data['message'].lower()

def test_delete_user_unsafe_prefix():
    """Test that paths outside db_pictures/ are rejected."""
    headers = {'Content-Type': 'application/json'}
    if ADMIN_KEY:
        headers['X-ADMIN-KEY'] = ADMIN_KEY
    
    # Try to delete from root
    payload = {'folder': 'gs://bucket/'}
    response = requests.post(f"{API_BASE_URL}/admin/delete_user", json=payload, headers=headers)
    
    assert response.status_code == 400
    data = response.json()
    assert 'unsafe' in data['message'].lower() or 'db_pictures' in data['message'].lower()
