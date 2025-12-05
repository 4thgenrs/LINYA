# Use Python 3.11 slim image
FROM python:3.11-slim

# Set working directory
WORKDIR /app

# Install system dependencies required by face-recognition and opencv
RUN apt-get update && apt-get install -y \
    cmake \
    build-essential \
    && rm -rf /var/lib/apt/lists/*

# Copy requirements and install Python dependencies
COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# Copy application files
COPY . .

# Create db_pictures directory
RUN mkdir -p db_pictures

# Cloud Run listens on port 8080
EXPOSE 8080

# Start Gunicorn server
CMD exec gunicorn --bind 0.0.0.0:8080 --workers 1 --timeout 300 app:app
