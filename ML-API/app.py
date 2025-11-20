from flask import Flask, request, jsonify
import numpy as np
from datetime import datetime
import pickle
from tensorflow.keras.models import load_model
import firebase_admin
from firebase_admin import credentials, db
import requests
import os
import threading
from pyngrok import ngrok
import time

# -----------------------
# Flask app
# -----------------------
app = Flask(__name__)

# -----------------------
# Load ML model & scaler
# -----------------------
model = load_model("flameguard_tiny_model.h5")
with open("tiny_model_scaler.pkl", "rb") as f:
    scaler = pickle.load(f)

# -----------------------
# Initialize Firebase
# -----------------------
cred = credentials.Certificate("serviceAccountKey.json")
firebase_admin.initialize_app(cred, {
    'databaseURL': 'https://flameguard-6f54d-default-rtdb.firebaseio.com/'
})

# -----------------------
# Laravel API URL
# -----------------------
#Hotspot IP
# LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://192.168.231.165:8000/api/sensor-data')  # Use actual LAN IP
# #Home WiFi IP
# LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://192.168.1.247:8000/api/sensor-data')  # Use actual LAN IP

#Bnb WiFi IP
LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://192.168.0.16:8000/api/sensor-data')  # Use actual LAN IP


# -----------------------
# ngrok tunnel function
# -----------------------
NGROK_AUTH_TOKEN = os.getenv('NGROK_AUTH_TOKEN')  # optional

def start_ngrok():
    if NGROK_AUTH_TOKEN:
        ngrok.set_auth_token(NGROK_AUTH_TOKEN)
    public_url = ngrok.connect(5000, bind_tls=True)
    print(f"\n🚀 Ngrok Tunnel: {public_url}")

# -----------------------
# /predict route
# -----------------------
@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        device_id = data.get('device_id', 'unknown_device')
        mq2 = float(data['mq2'])
        temp = float(data['temp'])
        humidity = float(data['humidity'])
        threshold = float(data.get('threshold', 0.5))

        # ----- ML Prediction -----
        features = np.array([[mq2, temp, humidity]])
        features_scaled = scaler.transform(features)
        prob_fire = model.predict(features_scaled)[0][0]

        prediction = int(prob_fire >= threshold)
        confidence = float(prob_fire if prediction == 1 else 1 - prob_fire)

        # ----- Prepare Firebase data -----
        firebase_data = {
            "device_id": device_id,
            "mq2": mq2,
            "temp": temp,
            "humidity": humidity,
            "fire_detected": bool(prediction),
            "confidence": confidence,
            "timestamp": datetime.utcnow().isoformat()
        }

        # ----- Push to Firebase in a background thread -----
        def push_to_firebase(data):
            try:
                # Latest reading
                ref_latest = db.reference(f'sensor_data/{data["device_id"]}/latest')
                ref_latest.set(data)

                # Append to historical data
                ref_history = db.reference(f'sensor_data/{data["device_id"]}/history')
                ref_history.push(data)  # creates a unique key for each reading

                print(f"✅ Data stored in Firebase for device: {data['device_id']}")
            except Exception as e:
                print(f"⚠️ Firebase push failed: {e}")

        threading.Thread(target=push_to_firebase, args=(firebase_data,), daemon=True).start()

        # ----- Forward data to Laravel -----
        laravel_payload = {
            "device_id": device_id,
            "mq2": mq2,
            "temp": temp,
            "humidity": humidity,
            "fire_detected": bool(prediction),
            "confidence": confidence
        }

        success = False
        retries = 3
        for attempt in range(1, retries + 1):
            try:
                response = requests.post(LARAVEL_API_URL, json=laravel_payload, timeout=15)
                if response.status_code in [200, 201]:
                    data_id = response.json().get('data_id')
                    print(f"✅ Data logged to Laravel: ID {data_id}")
                    success = True
                    break
                else:
                    print(f"⚠️ Laravel logging attempt {attempt} failed: {response.text}")
            except requests.exceptions.RequestException as e:
                print(f"⚠️ Laravel connection attempt {attempt} failed: {e}")
            time.sleep(2)

        if not success:
            print(f"❌ Could not log data to Laravel after {retries} attempts")

        # ----- Return result -----
        result = {
            "fire_detected": bool(prediction),
            "confidence": confidence,
            "threshold": threshold,
            "sensor_data": {"mq2": mq2, "temp": temp, "humidity": humidity},
            "firebase_status": f"Push initiated for device {device_id}",
            "laravel_status": "Logged" if success else "Failed"
        }
        return jsonify(result)

    except KeyError as e:
        return jsonify({'error': f'Missing required field: {str(e)}'}), 400
    except Exception as e:
        return jsonify({'error': str(e)}), 400

# -----------------------
# Run Flask app with ngrok
# -----------------------
if __name__ == "__main__":
    print("🔥 Starting Flask ML API with Firebase + Laravel MySQL logging...")

    # Start ngrok in a separate thread
    threading.Thread(target=start_ngrok, daemon=True).start()

    # Start Flask server
    app.run(host='0.0.0.0', port=5000)
    


# import time
# import numpy as np
# import joblib
# import requests
# from flask import Flask, request, jsonify
# from flask_cors import CORS
# from pyngrok import ngrok
# from dotenv import load_dotenv
# import threading

# # Load environment variables
# load_dotenv()

# app = Flask(__name__)
# CORS(app)

# # Environment variables
# NGROK_AUTH_TOKEN = os.getenv('NGROK_AUTH_TOKEN')
# LARAVEL_API_URL = os.getenv('LARAVEL_API_URL', 'http://192.168.1.247:8000/api/sensor-data')

# # Load ML model and scaler
# try:
#     model = joblib.load('flameguard_model.pkl')
#     scaler = joblib.load('scaler.pkl')
#     print("✅ ML Model loaded successfully")
# except Exception as e:
#     print(f"❌ Error loading model: {e}")
#     exit(1)

# def start_ngrok():
#     """Start ngrok tunnel"""
#     if NGROK_AUTH_TOKEN:
#         ngrok.set_auth_token(NGROK_AUTH_TOKEN)
#     public_url = ngrok.connect(5000, bind_tls=True)
#     print(f"\n🚀 Ngrok Tunnel: {public_url}")
#     return public_url

# @app.route('/')
# def home():
#     return jsonify({"message": "FlameGuard ML API", "status": "online"})

# @app.route('/predict', methods=['POST'])
# def predict():
#     try:
#         data = request.json
#         mq2 = float(data['mq2'])
#         temp = float(data['temp'])
#         humidity = float(data['humidity'])
#         device_id = data.get('device_id', 'unknown_device')

#         # ML Prediction
#         features = np.array([[mq2, temp, humidity]])
#         features_scaled = scaler.transform(features)
#         prediction = model.predict(features_scaled)[0]
#         probability = model.predict_proba(features_scaled)[0]

#         result = {
#             "fire_detected": bool(prediction),
#             "confidence": float(max(probability)),
#             "timestamp": time.time(),
#             "sensor_data": {"mq2": mq2, "temp": temp, "humidity": humidity}
#         }

#         # Forward data to Laravel for storage
#         try:
#             response = requests.post(LARAVEL_API_URL, json={
#                 "device_id": device_id,
#                 "mq2": mq2,
#                 "temp": temp,
#                 "humidity": humidity,
#                 "fire_detected": result["fire_detected"],
#                 "confidence": result["confidence"]
#             }, timeout=5)

#             if response.status_code == 201:
#                 print(f"✅ Data logged to Laravel: ID {response.json().get('data_id')}")
#             else:
#                 print(f"⚠️ Laravel logging failed: {response.text}")
#         except Exception as e:
#             print(f"⚠️ Could not send data to Laravel: {e}")

#         # Return prediction to ESP32
#         print(f"🔥 Fire: {prediction}, Confidence: {max(probability):.3f}")
#         return jsonify(result)

#     except KeyError as e:
#         return jsonify({'error': f'Missing required field: {str(e)}'}), 400
#     except Exception as e:
#         return jsonify({'error': str(e)}), 400

# if __name__ == '__main__':
#     print("🔥 Starting Flask ML API...")
#     # Start ngrok in a separate thread (optional for public testing)
#     threading.Thread(target=start_ngrok, daemon=True).start()
#     time.sleep(2)
#     app.run(host='0.0.0.0', port=5000)
