# 🔥 FlameGuard - Fire Detection System

**FlameGuard** is a smart fire detection and alert system that combines IoT sensor data with machine learning to detect and notify users about potential fire hazards in real time.  
This repository contains the complete system including the **Laravel web dashboard** and **Python ML API** for intelligent fire prediction.

---

## 🏗️ System Architecture

```
FlameGuard System/
├── 🔧 Laravel Web App (Dashboard & Management)
├── 🤖 Python ML API (Fire Detection Intelligence)  
├── 📱 ESP32 IoT Device (Sensor Data Collection)
└── 🗄️ MySQL Database (Data Storage)
```

---

## 🚀 Current Progress

### ✅ Completed
**Laravel Web Application:**
- Laravel 11 project setup with FilamentPHP v4
- Database configuration with role-based access control
- Custom authentication (Laravel Breeze + Google OAuth)
- Admin/User dashboards with role-based redirects

**Machine Learning API:**
- Flask API with trained Decision Tree model
- Ngrok tunnel for public accessibility
- Real-time fire prediction endpoint (`/predict`)
- Sensor data processing and scaling

**IoT Integration:**
- ESP32 code for sensor data collection
- MQ2 (Gas/Smoke), MQ7 (CO), DHT22 (Temp/Humidity) sensors
- HTTP communication with ML API
- Visual/audible alerts (LEDs + Buzzer)

### 🔧 In Progress
- Real-time data visualization on dashboards
- Alert notifications (email/SMS)
- Historical data analysis

---

## 🧰 Tech Stack

| Component | Technology |
|-----------|-------------|
| **Web Dashboard** | Laravel 11 + FilamentPHP v4 |
| **ML API** | Python + Flask + Scikit-learn |
| **Database** | MySQL |
| **IoT Device** | ESP32 (Arduino C++) |
| **Authentication** | Laravel Breeze + Socialite (Google OAuth) |
| **Communication** | HTTP/REST API + Ngrok |

---

## 📁 Project Structure

```
flameguard-system/
├── 📱 laravel-app/                 # Web Dashboard
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   ├── public/
│   ├── resources/views/
│   └── composer.json
├── 🤖 ml-api/                      # Machine Learning API
│   ├── app.py                     # Flask API server
│   ├── requirements.txt           # Python dependencies
│   └── README.md                 # API documentation
└── 📚 docs/                       # Documentation
```

---

## ⚙️ Installation & Setup

### 1️⃣ Web Dashboard (Laravel)

```bash
# Clone repository
git clone https://github.com/AtutiMichelle/FlameGuard-Fire-Detection-System.git
cd flameguard-system/laravel-app

# Install dependencies
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Update .env with:
# - Database credentials
# - Google OAuth credentials
# - ML_API_URL (from ngrok)

# Setup database
php artisan migrate --seed

# Serve application
php artisan serve
```

### 2️⃣ Machine Learning API

```bash
cd ml-api

# Install Python dependencies
pip install -r requirements.txt

# Download model files (if not included)

# Run API server

# - cd into ML API folder from terminal then run:
python app.py
```

**📝 Note:** The API will provide a ngrok URL like `https://abc123.ngrok-free.app` - use this in your ESP32 code and Laravel `.env` file.

### 3️⃣ ESP32 Setup

1. **Hardware Connections:**
   - MQ2 Sensor → GPIO 34
   - MQ7 Sensor → GPIO 23  
   - DHT22 → GPIO 15
   - Red LED → GPIO 18 (Alarm)
   - Green LED → GPIO 19 (Safe)
   - Buzzer → GPIO 21

2. **Upload Code:**
   - Open `esp32-code/flameguard_esp32.ino` in Arduino IDE
   - Update WiFi credentials and ML API URL
   - Upload to ESP32

---

## 🔌 API Endpoints

### ML API (`https://your-ngrok-url.ngrok-free.app`)
```http
POST /predict
Content-Type: application/json

{
  "mq2": 450.5,
  "temp": 25.3,
  "humidity": 60.2,
  "device_id": "esp32_001"
}

Response:
{
  "fire_detected": false,
  "confidence": 0.95,
  "timestamp": 1760906213.2905915
}
```

### Web Dashboard API
```http
POST /api/fire/check          # Manual fire check
GET  /fire-monitoring/dashboard  # Monitoring dashboard
```

---

## 👑 Default Admin Access

After seeding, access the admin dashboard:

**URL:** `http://localhost:8000/admin`  
**Credentials:**
- Email: `admin@flameguard.com`
- Password: `password`

---

## 🔐 Authentication Flow

| Method | Description | Redirect To |
|--------|-------------|-------------|
| **Email/Password** | Traditional Laravel Breeze | Role-based dashboard |
| **Google OAuth** | Social login via Socialite | Role-based dashboard |

**Role-based Access:**
- 🧑‍💼 **Admin** → `/admin` (Full system control)
- 👤 **User** → `/user` (Monitoring & alerts)

---

## 🎯 Fire Detection Logic

The system uses a trained Decision Tree model with these features:
- **MQ2 Sensor**: Gas/Smoke concentration (0-1000+ PPM)
- **Temperature**: Ambient temperature (°C) 
- **Humidity**: Relative humidity (%)

**Alert Threshold:** Fire detected with >70% confidence

---

## 🚨 Real-time Alerts

When fire is detected:
- 🔴 Red LED activates
- 🟢 Green LED turns off  
- 🔔 Buzzer sounds pattern
- 📱 Web dashboard shows alert
- 📧 Email/SMS notifications (in progress)

---

## 📊 Model Performance

- **Algorithm**: Decision Tree Classifier
- **Accuracy**: >95% on test data
- **Features**: MQ2, MQ7, Temperature, Humidity
- **Inference Time**: <100ms

---

## 🔧 Development

### Adding New Sensors
1. Update ESP32 code to read sensor
2. Modify ML API to accept new feature
3. Retrain model with new data
4. Update Laravel dashboard to display new metric

### Customizing Alerts
Edit `processPrediction()` in ESP32 code and Laravel notification handlers.

---

## 📝 License

This project is licensed under the **MIT License**.

---


> Developed with ❤️ by **Michelle Atuti** | IoT & Machine Learning Fire Detection System
```
