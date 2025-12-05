"""
Entry point for Cloud Run and gunicorn deployment.

This module re-exports the Flask app created by face_recognition_app.app
so that Cloud Run, gunicorn, and other WSGI servers can discover and run it.

For local development: python app.py
For Cloud Run: gcloud run deploy will find 'app' variable
For gunicorn: gunicorn app:app
"""

from face_recognition_app.app import app

__all__ = ['app']

if __name__ == "__main__":
    import os
    port = int(os.environ.get("PORT", 5000))
    app.run(host="0.0.0.0", port=port, debug=False)
