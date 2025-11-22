# Google Cloud Storage Migration Guide

## Overview

This guide explains how to migrate your Python face recognition system from ephemeral container storage to Google Cloud Storage (GCS) for persistent data storage.

## What Changed

### 1. File Storage Location
- **Before**: Files stored in `/app/db_pictures/` (ephemeral container storage)
- **After**: Files stored in `gs://<bucket>/db_pictures/<safe_name>/<timestamp>/`

### 2. File Structure
```
GCS Bucket Structure:
gs://<bucket>/
└── db_pictures/
    └── <safe_name>/
        └── <timestamp>/
            ├── <safe_name>_1.jpg
            ├── <safe_name>_2.jpg
            ├── <safe_name>_3.jpg
            ├── <safe_name>_4.jpg
            ├── <safe_name>_5.jpg
            ├── encoding.npy
            └── meta.json
```

### 3. Response Format
The API response format remains **identical** for PHP compatibility:
```json
{
  "status": "success",
  "folder": "gs://<bucket>/db_pictures/<safe_name>/<timestamp>",
  "encoding": "<base64-encoded-npy-data>"
}
```

## Setup Instructions

### 1. Create GCS Bucket

```bash
# Create a new bucket (replace with your preferred name)
gsutil mb gs://your-face-recognition-bucket

# Set appropriate permissions
gsutil iam ch serviceAccount:your-cloud-run-service@your-project.iam.gserviceaccount.com:roles/storage.objectAdmin gs://your-face-recognition-bucket
```

### 2. Set Environment Variable

Update your Cloud Run service with the GCS bucket name:

```bash
gcloud run services update your-service-name \
  --set-env-vars GCS_BUCKET=your-face-recognition-bucket \
  --region=your-region
```

Or in the Cloud Console:
- Go to Cloud Run → Your Service → Edit & Deploy New Revision
- Add environment variable: `GCS_BUCKET=your-face-recognition-bucket`

### 3. Deploy Updated Code

```bash
# Build and deploy the updated image
gcloud run deploy your-service-name \
  --source . \
  --platform managed \
  --region=your-region \
  --allow-unauthenticated
```

## Fallback Behavior

The system includes automatic fallback:
- If `GCS_BUCKET` environment variable is not set → uses local storage
- If GCS client initialization fails → uses local storage
- If GCS operations fail → logs errors and continues with local storage

## Testing the Migration

### 1. Register a New User
```bash
curl -X POST https://your-cloud-run-url/register \
  -F "name=Test User" \
  -F "images[]=data:image/jpeg;base64,<base64-image-data>"
```

### 2. Verify GCS Storage
```bash
# List files in your bucket
gsutil ls -r gs://your-face-recognition-bucket/db_pictures/

# Expected output:
# gs://your-face-recognition-bucket/db_pictures/test_user/20231121_143022/test_user_1.jpg
# gs://your-face-recognition-bucket/db_pictures/test_user/20231121_143022/encoding.npy
# gs://your-face-recognition-bucket/db_pictures/test_user/20231121_143022/meta.json
```

### 3. Test Recognition
```bash
curl -X POST https://your-cloud-run-url/recognize \
  -H "Content-Type: application/json" \
  -d '{"image": "data:image/jpeg;base64,<base64-image-data>"}'
```

## PHP Compatibility

The only required PHP change is in `backend/face_register_save.php`:

```php
// OLD (Windows path separators)
$face_path_db = rtrim($folder, "/\\") . DIRECTORY_SEPARATOR . 'encoding.npy';

// NEW (Unix path separators for GCS compatibility)  
$face_path_db = rtrim($folder, "/") . '/encoding.npy';
```

This change handles both local paths and GCS URLs correctly.

## Monitoring and Troubleshooting

### View Logs
```bash
gcloud run services logs read your-service-name --region=your-region
```

### Common Issues

1. **Permission Errors**
   - Ensure Cloud Run service account has `Storage Object Admin` role
   - Verify bucket exists and is accessible

2. **Environment Variable Missing**
   - Check `GCS_BUCKET` is set in Cloud Run environment
   - System will fall back to local storage if missing

3. **Network Issues**
   - GCS operations include error handling and logging
   - Check Cloud Run logs for specific error messages

### Debug Endpoints

- `GET /model_status` - Check system status
- `GET /admin/list_users_files` - List user data (local storage only)

## Data Migration (Optional)

If you have existing local data to migrate:

1. **Export existing data** from a running container
2. **Upload to GCS** using gsutil or Cloud Console
3. **Follow the folder structure** above
4. **Restart the service** to reload from GCS

## Performance Considerations

- **First Load**: Slightly slower as encodings are downloaded from GCS
- **Recognition**: Same performance after initial load (data cached in memory)
- **Registration**: Slightly slower due to GCS upload time
- **Scalability**: Multiple container instances can now share user data

## Cost Estimation

- **Storage**: ~$0.02/GB/month for Standard storage
- **Operations**: ~$0.05/10K operations
- **Expected costs**: <$1/month for typical usage patterns

## Rollback Plan

To rollback to local storage:
1. Remove `GCS_BUCKET` environment variable
2. Redeploy the service
3. System automatically falls back to local storage