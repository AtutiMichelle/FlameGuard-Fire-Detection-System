You're absolutely right! Let me correct the data flow and keep everything else intact:

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

**Real-Time Data Flow:**
```
ESP32 Sensors → Flask ML API → Laravel API → Database → Web Dashboard
```

---

## 🚀 Current Progress

### ✅ Completed - MVP Fully Operational
**End-to-End Integration Complete:**
- ✅ ESP32 to Flask ML API communication established
- ✅ Flask ML API to Laravel integration working
- ✅ Real-time sensor data storage in MySQL database
- ✅ Fire detection predictions with confidence scoring
- ✅ Complete data pipeline: ESP32 → ML API → Laravel → Database

**Laravel Web Application:**
- Laravel 11 project setup with FilamentPHP v4
- Database configuration with role-based access control
- Custom authentication (Laravel Breeze + Google OAuth)
- Admin/User dashboards with role-based redirects
- REST API endpoints for sensor data reception from ML API
- **Alert Management System with Active Alerts and Alert History pages**
- **Real-time alert monitoring with filtering and search capabilities**

**Machine Learning API:**
- Flask API with trained Decision Tree model
- Ngrok tunnel for public accessibility
- Real-time fire prediction endpoint (`/predict`)
- Sensor data processing and scaling
- Direct communication with ESP32 devices

**IoT Integration:**
- ESP32 code for sensor data collection (MQ2, DHT22)
- HTTP communication with Flask ML API
- Visual/audible alerts (LEDs + Buzzer)
- Real-time data transmission every 5 seconds

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

## 🚨 Alert System Features

### Active Alerts Monitoring
- **Real-time Detection**: Live monitoring of fire detection events
- **24-Hour Window**: Shows only recent alerts from the last 24 hours
- **Auto-Refresh**: Updates every 10 seconds for live data
- **Visual Indicators**: Color-coded badges (🔥 Yes/✅ No)
- **Empty State**: Professional "No Active Alerts" display when system is normal

### Alert History & Analytics
- **Complete Historical Record**: All fire detection events
- **Advanced Filtering**: Date range, device-specific, and status filtering
- **Search Functionality**: Across device IDs, sensor types, and values
- **Pagination**: Configurable results display

---

## 📁 Project Structure

```
flameguard-system/
├── 📱 laravel-app/                 # Web Dashboard
│   ├── app/Http/Controllers/Api/SensorDataController.php
│   ├── app/Models/SensorData.php
│   ├── app/Filament/Admin/Pages/
│   │   ├── ActiveAlerts.php       # Real-time alert monitoring
│   │   └── AlertHistory.php       # Historical alert tracking
│   ├── database/migrations/
│   ├── routes/api.php
│   └── composer.json
├── 🤖 ml-api/                      # Machine Learning API
│   ├── app.py                     # Flask API server
│   ├── requirements.txt           # Python dependencies
│   └── README.md                
└── 📟 esp32-code/                  # IoT Device Code
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

# Serve application (allow external connections)
php artisan serve --host=0.0.0.0 --port=8000
```

### 2️⃣ Machine Learning API

```bash
cd ml-api

# Install Python dependencies
pip install -r requirements.txt

# Run API server
# - cd into ML-API folder then run:
python app.py
```

**📝 Note:** The API will provide a ngrok URL like `https://abc123.ngrok-free.app` - use this in your Laravel `.env` file as `ML_API_URL`.

### 3️⃣ ESP32 Setup

1. **Hardware Connections:**
   - MQ2 Sensor → GPIO 34
   - DHT22 → GPIO 15
   - Red LED → GPIO 18 (Alarm)
   - Green LED → GPIO 19 (Safe)
   - Buzzer → GPIO 21

2. **Upload Code:**
   - Open `esp32-code/FlameGuard_Laravel_Integration.ino` in Arduino IDE
   - Update WiFi credentials and ML API URL
   - Upload to ESP32

---

## 🔌 API Endpoints

### ML API (Flask) - Primary Endpoint (`https://your-ngrok-url.ngrok-free.app`)
```http
POST /predict
Content-Type: application/json

{
  "device_id": "esp32_flameguard_001",
  "mq2": 450.5,
  "temp": 25.3,
  "humidity": 60.2
}

Response:
{
  "fire_detected": false,
  "confidence": 0.95,
  "timestamp": 1760906213.2905915,
  "sensor_data": {
    "mq2": 450.5,
    "temp": 25.3,
    "humidity": 60.2
  }
}
```

### Laravel API (`http://your-server:8000`)
```http
POST /api/sensor-data
Content-Type: application/json

{
  "device_id": "esp32_flameguard_001",
  "ml_results": {
    "fire_detected": false,
    "confidence": 0.95,
    "sensor_data": {...}
  }
}

Response:
{
  "status": "success",
  "message": "Sensor data processed successfully",
  "data_id": 14
}
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
- 📱 Web dashboard shows alert in Active Alerts
- 📧 Email/SMS notifications (in progress)

---

## 📊 Model Performance

- **Algorithm**: Decision Tree Classifier
- **Accuracy**: >95% on test data
- **Features**: MQ2, Temperature, Humidity
- **Inference Time**: <100ms

---

## 🔧 Development

### Adding New Sensors
1. Update ESP32 code to read sensor
2. Modify ML API to accept new feature
3. Retrain model with new data
4. Update Laravel dashboard to display new metric

### Customizing Alerts
Edit alert thresholds in ML API and notification handlers in Laravel.

---

> Developed with ❤️ by **Michelle Atuti** | IoT & Machine Learning Fire Detection System