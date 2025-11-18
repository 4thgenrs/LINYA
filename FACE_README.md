# LINYA - Face Recognition Integration Notes

This project includes a face-recognition login/register integration that uses a local Flask face API and stores registered clients in a MySQL `clients` table.

Quick setup
1. Create the `clients` table (if not already present):

   - Run the migration SQL provided:

     ```powershell
     mysql -u root -p linya_db < .\linya_db\add_clients_table.sql
     ```

2. Install and run the Flask face API (from `faceapp_php`):

   ```powershell
   cd .\faceapp_php
   python -m venv .venv
   .\.venv\Scripts\Activate.ps1
   pip install -r requirements.txt
   python -m face_recognition_app.app
   ```

   The PHP pages expect the Flask API at `http://127.0.0.1:5000`.

3. Web UI
   - Register: `http://localhost/LINYA/faceapp_php/kioskRegister.php`
   - Login: `http://localhost/LINYA/faceapp_php/kioskLogin.php`
   - Kiosk: `http://localhost/LINYA/includes/kioskMenu.php`
   - Admin clients: `http://localhost/LINYA/includes/admin_clients.php` (lists registered clients)

How it works
- Registration: Flask `/register` saves images and `encoding.npy` on disk. The register page then calls `backend/face_register_save.php` to persist a `clients` DB row (with `face_data` blob when available).
- Recognition: Flask `/recognize` returns `{status: 'recognized', name: 'Display Name', safe_name: 'display_name'}`. The login page posts `safe_name` to `backend/face_login.php` which creates a PHP `$_SESSION['client']`.
- Kiosk flow: When `$_SESSION['client']` exists the kiosk menu pre-fills visitor details and proceeds.
- Receipt: When a face-logged-in user presses Back to menu, the receipt page triggers `backend/face_logout.php` and redirects to the face-login page.

Security & notes
- For production you should add CSRF protection, stronger auth for admin pages, and consider storing face encodings as files instead of database blobs.
- The current matching uses `safe_name` (derived from the display name). For robust behavior consider returning and using a numeric `client_id` from the recognition step.

If you want, I can:
- Update the Flask API to return a `client_id` by storing a small mapping file when registering (requires writing to a central registry), or
- Add an admin UI to manage encodings and export/import client data in bulk.
