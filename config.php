<?php
/**
 * LINYA Face Recognition API Configuration
 * 
 * This file configures the connection to the Python Face API backend.
 * 
 * For LOCAL DEVELOPMENT:
 *   - The Python API runs on localhost:5000
 *   - Uncomment the localhost line below
 * 
 * For PRODUCTION (Hostinger → Google Cloud Run):
 *   - Update PYTHON_API_BASE to your Cloud Run service URL
 *   - Example: https://face-recognition-api-xxxxxx-uc.a.run.app
 */

// ============== LOCAL DEVELOPMENT ==============
// define('PYTHON_API_BASE', 'http://127.0.0.1:5000');

// ============== PRODUCTION (Cloud Run) ==============
// Your Cloud Run service URL - deployed and tested
define('PYTHON_API_BASE', 'https://face-recognition-api-546052063562.asia-southeast1.run.app');

// ============== QZ TRAY TRUST CONFIGURATION ==============
// Update these paths after placing your real QZ files on the server.
if (!defined('QZ_CERTIFICATE_PATH')) {
	define('QZ_CERTIFICATE_PATH', __DIR__ . '/resources/qz/digital-certificate.txt');
}

if (!defined('QZ_PRIVATE_KEY_PATH')) {
	define('QZ_PRIVATE_KEY_PATH', __DIR__ . '/resources/qz/private-key.pem');
}

if (!defined('QZ_SIGNATURE_ALGORITHM')) {
	define('QZ_SIGNATURE_ALGORITHM', 'SHA256');
}

if (!defined('PUBLIC_APP_URL')) {
	define('PUBLIC_APP_URL', 'https://linya.ucc-bsit.org/LINYA');
}

/**
 * NOTE ON STORAGE:
 * 
 * The Python API stores user face encodings in a local "db_pictures" folder.
 * On Cloud Run, this is ephemeral storage (will be lost when container restarts).
 * 
 * For production persistence, consider:
 * - Google Cloud Storage (GCS) for face encoding files
 * - Cloud SQL or Firestore for metadata
 * - Persistent disks (if using Compute Engine instead)
 * 
 * For now, we accept ephemeral storage as acceptable for this MVP.
 */
?>
