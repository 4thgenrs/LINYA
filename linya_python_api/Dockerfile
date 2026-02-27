FROM python:3.11-slim

WORKDIR /app

RUN apt-get update && apt-get install -y \
    cmake \
    build-essential \
    libgl1 \
    libglib2.0-0 \
    libgomp1 \
    libstdc++6 \
    && rm -rf /var/lib/apt/lists/*

COPY requirements.txt .

# ✅ Install everything in one resolver run, but allow torch CPU wheels
RUN pip install --no-cache-dir -r requirements.txt \
    --extra-index-url https://download.pytorch.org/whl/cpu

COPY . .

RUN mkdir -p db_pictures

EXPOSE 8080

CMD exec gunicorn --bind 0.0.0.0:8080 --workers 1 --timeout 900 \
  --log-level info --capture-output --access-logfile - --error-logfile - app:app