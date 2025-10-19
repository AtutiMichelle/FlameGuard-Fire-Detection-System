import os
from flask import Flask, request, jsonify
from flask_cors import CORS
import numpy as np
import joblib
import time
from pyngrok import ngrok
import threading
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

app = Flask(__name__)
CORS(app)

# Configuration from environment
NGROK_AUTH_TOKEN = os.getenv('NGROK_AUTH_TOKEN')

# Load ML model
try:
    model = joblib.load('flameguard_model.pkl')
    scaler = joblib.load('scaler.pkl')
    print("✅ ML Model loaded successfully!")
except Exception as e:
    print(f"❌ Error loading model: {e}")
    exit(1)

def start_ngrok():
    """Start ngrok tunnel"""
    try:
        # Use environment variable instead of hardcoded token
        if NGROK_AUTH_TOKEN:
            ngrok.set_auth_token(NGROK_AUTH_TOKEN)
        
        public_url = ngrok.connect(5000, bind_tls=True)
        print(f"\n🚀 Ngrok Tunnel: {public_url}")
        print(f"📡 Use this URL in ESP32 and Laravel app")
        return public_url
    except Exception as e:
        print(f"❌ Ngrok error: {e}")
        return None

@app.route('/')
def home():
    return jsonify({"message": "FlameGuard ML API", "status": "online"})

@app.route('/predict', methods=['POST'])
def predict():
    try:
        data = request.json
        
        # All 4 features are required
        mq2 = float(data['mq2'])
        # mq7 = float(data['mq7'])
        temp = float(data['temp'])
        humidity = float(data['humidity'])
        
        features = np.array([[mq2, temp, humidity]])
        features_scaled = scaler.transform(features)
        
        prediction = model.predict(features_scaled)[0]
        probability = model.predict_proba(features_scaled)[0]
        
        response = {
            'fire_detected': bool(prediction),
            'confidence': float(max(probability)),
            'timestamp': time.time(),
            'sensor_data': {
                'mq2': mq2,
                # 'mq7': mq7,
                'temp': temp,
                'humidity': humidity
            }
        }
        
        print(f"🔍 Prediction - Fire: {prediction}, Confidence: {max(probability):.3f}")
        # print(f"📊 Sensors - MQ2: {mq2}, MQ7: {mq7}, Temp: {temp}°C, Humidity: {humidity}%")
        print(f"📊 Sensors - MQ2: {mq2}, Temp: {temp}°C, Humidity: {humidity}%")
        return jsonify(response)
        
    except KeyError as e:
        return jsonify({'error': f'Missing required field: {str(e)}'}), 400
    except Exception as e:
        return jsonify({'error': str(e)}), 400

if __name__ == '__main__':
    print("🔥 Starting FlameGuard ML API...")
    
    # Start ngrok
    ngrok_thread = threading.Thread(target=start_ngrok, daemon=True)
    ngrok_thread.start()
    time.sleep(2)
    
    app.run(host='0.0.0.0', port=5000, debug=False)