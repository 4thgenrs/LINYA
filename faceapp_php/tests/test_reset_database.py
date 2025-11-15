import os
import shutil
import tempfile
import numpy as np
import json
from face_recognition_app.core.face_processor import FaceProcessor


def create_sample_db(base_dir):
    # create a user folder with an image and encoding.npy
    user_dir = os.path.join(base_dir, 'alice')
    os.makedirs(user_dir, exist_ok=True)
    img_path = os.path.join(user_dir, 'alice.jpg')
    with open(img_path, 'wb') as f:
        f.write(b'JPEGDATA')

    enc_path = os.path.join(user_dir, 'encoding.npy')
    np.save(enc_path, np.array([[0.1]*128], dtype='float32'))

    # create global serialized files
    global_pkl = os.path.join(base_dir, 'encodings.pkl')
    with open(global_pkl, 'wb') as f:
        f.write(b'PICKLE')

    global_json = os.path.join(base_dir, 'face_data.json')
    with open(global_json, 'w', encoding='utf-8') as f:
        json.dump({'test': True}, f)

    return user_dir, img_path, enc_path, global_pkl, global_json


def test_reset_database_default_keeps_user_dirs():
    td = tempfile.mkdtemp()
    try:
        user_dir, img_path, enc_path, global_pkl, global_json = create_sample_db(td)

        fp = FaceProcessor(td)
        # call reset_database without removing user dirs
        ok = fp.reset_database(remove_user_dirs=False)
        assert ok is True

        # global files must be removed
        assert not os.path.exists(global_pkl)
        assert not os.path.exists(global_json)

        # encoding.npy in user folder should be removed
        assert not os.path.exists(enc_path)

        # user image should still exist and folder should remain
        assert os.path.exists(img_path)
        assert os.path.isdir(user_dir)
    finally:
        shutil.rmtree(td)


def test_reset_database_remove_user_dirs():
    td = tempfile.mkdtemp()
    try:
        user_dir, img_path, enc_path, global_pkl, global_json = create_sample_db(td)

        fp = FaceProcessor(td)
        # call reset_database with remove_user_dirs True
        ok = fp.reset_database(remove_user_dirs=True)
        assert ok is True

        # global files must be removed
        assert not os.path.exists(global_pkl)
        assert not os.path.exists(global_json)

        # user folder should be removed entirely
        assert not os.path.exists(user_dir)
    finally:
        if os.path.exists(td):
            shutil.rmtree(td)
