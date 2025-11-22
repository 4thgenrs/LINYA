import os
import mysql.connector
from face_recognition_app.app import FaceRecognitionApp

def init_database():
    try:
        conn = mysql.connector.connect(
            host="localhost",
            user="root",
            password=""
        )
        cursor = conn.cursor()
        cursor.execute("CREATE DATABASE IF NOT EXISTS face_db")
        cursor.execute("USE face_db")
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS face_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                image VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)
        conn.commit()
        cursor.close()
        conn.close()
    except Exception as e:
        print(f"Database initialization error: {e}")

def main():
    init_database()
    BASE_DIR = os.path.dirname(os.path.abspath(__file__))
    DB_DIR = os.path.join(BASE_DIR, "db_pictures")
    os.makedirs(DB_DIR, exist_ok=True)

    app = FaceRecognitionApp(DB_DIR)
    app.run(host='0.0.0.0', port=5000)

if __name__ == '__main__':
    main()