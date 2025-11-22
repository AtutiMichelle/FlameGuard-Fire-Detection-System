# FlameGuard – Smart Fire Alert System Technical Report

**Final Year Project Report**  
**Date:** November 23, 2025  
**Models:** Tiny TensorFlow Lite ML Model deployed on ESP32

---

## 1. Executive Summary

**FlameGuard** is a **smart fire alert system that tightly integrates IoT sensing with embedded ML inference**, operating as a **dual-mode system**. The system continuously monitors environmental parameters using **DHT22 (temperature & humidity)** and **MQ2 (gas)** sensors, while the **ESP32 performs TinyML inference locally** for instant alerts. Simultaneously, all sensor readings and ML predictions are streamed to an **online platform** for real-time monitoring, historical analytics, and multi-device management.  

Key integrated features:

* **Embedded ML on IoT:** TinyML model runs on the ESP32 for **instant local fire detection** ensuring safety even if the network is unavailable.  
* **Continuous IoT sensing:** Real-time sensor acquisition, preprocessing, and filtering provide high-quality data for ML inference.  
* **Immediate alerting:** Critical fire events trigger **LED/buzzer alerts** and **SMS via SIM800L**.  
* **Online monitoring and analytics:** Sensor data and predictions streamed to **Flask API → Firebase → Laravel + Filament dashboard**, enabling multi-device oversight and historical analysis.  
* **Detection performance:** Embedded ML achieves **96.5% accuracy**, demonstrating that IoT-integrated intelligence enhances responsiveness and reliability.  

> “FlameGuard’s strength lies in the seamless integration of embedded ML within the IoT system, combining **instant local action** with **scalable online monitoring and analytics**.”

---

## 2. Approach

### 2.1 Integrated IoT & ML Workflow

**Challenge:** Detect fires in real-time using noisy sensor data while operating under the constraints of ESP32 hardware.  

**Solution:**

* **Sensors & IoT Hardware:**  
  - MQ2 gas sensor, DHT22 (temperature & humidity)  
  - ESP32 Dev Kit for embedded computation  
  - SIM800L for SMS notifications  

* **Data Acquisition & Preprocessing:**  
  - Sensors sampled continuously at fixed intervals (~500 ms)  
  - Real-time filtering removes NaN or outlier readings  
  - Normalization and scaling applied **on-device** before feeding into the TinyML model  

* **Embedded ML Inference:**  
  - TinyML model compiled into ESP32 firmware (`.h` file)  
  - Performs **real-time fire prediction** locally for **immediate response**  

* **Dual-Mode Integration:**  
  - **Offline:** ESP32 triggers local alerts (LED, buzzer, SMS)  
  - **Online:** Sensor readings and predictions streamed to Flask API → Firebase → Laravel dashboard for **centralized analytics, historical tracking, and multi-device management**  

**Result:** A **single, integrated pipeline** where IoT sensing, embedded ML inference, and online monitoring work as a unified system.

---

### 2.2 TinyML Model Architecture Embedded in IoT

* **Input Features:** MQ2, Temperature, Humidity  
* **Model Layers:**  
  - Dense (8 units, ReLU)  
  - Dense (4 units, ReLU)  
  - Dense (1 unit, Sigmoid)  
* **Total Parameters:** 73  
* **Size:** <50 KB, optimized for ESP32 memory  
* **Training Details:**  
  - Optimizer: Adam  
  - Loss: Binary Cross-Entropy  
  - Epochs: 30, Batch size: 32  
  - Accuracy on Test Set: 96.5%  
  - Class Weights: `{0: 0.587, 1: 4.027}`  

**Integration Insight:** The model is **embedded directly in the IoT device**, turning real-time sensor data into actionable fire alerts **on the edge**, while simultaneously feeding the online monitoring system.

---

### 2.3 System Workflow: Integrated Offline + Online Operation

1. **Sensors continuously monitor** environmental parameters.  
2. **ESP32 preprocesses and normalizes** sensor data in real-time.  
3. **Embedded ML inference** runs locally to generate immediate fire alerts.  
4. **Offline alerting:**  
   - LED and buzzer activation  
   - SMS alerts via SIM800L  
5. **Online pipeline:**  
   - Sensor data and predictions sent to Flask API  
   - Logged in Firebase  
   - Visualized on Laravel + Filament dashboard for **historical analysis and multi-device management**  
6. **Redundancy & reliability:** Offline inference ensures safety during network outages; online analytics enhance monitoring, reporting, and system scaling.

> FlameGuard exemplifies a **fully integrated IoT + ML system**, where intelligence and sensing are inseparable, providing both **instant safety actions** and **centralized monitoring**.

---

## 3. Results Summary

### 3.1 Test Set Performance

| Metric                     | Value       |
|-----------------------------|------------|
| Test Accuracy               | 0.9650     |
| Test Loss                   | 0.0990     |
| Custom Threshold (0.4) Accuracy | 0.9650 |
| Confusion Matrix (0.4 threshold) | [[829, 29], [6, 136]] |

**Classification Report:**

| Class    | Precision | Recall | F1-score | Support |
|----------|-----------|--------|----------|---------|
| No Fire  | 0.99      | 0.97   | 0.98     | 858     |
| Fire     | 0.82      | 0.95   | 0.88     | 142     |
| **Accuracy** | - | - | 0.965 | 1000 |
| **Macro avg** | 0.91 | 0.96 | 0.93 | 1000 |
| **Weighted avg** | 0.97 | 0.96 | 0.97 | 1000 |

---

### 3.2 Integrated System Metrics

| Metric                 | ESP32 Embedded ML | Online API | Notes                                   |
|------------------------|-----------------|------------|----------------------------------------|
| Accuracy               | 96.5%           | 96.5%      | Predictions consistent across offline & online modes |
| Fire Detection Latency | <50 ms          | 150 ms     | Offline provides instant alerts, online adds logging & analytics |
| SMS Alerts             | Yes             | Yes        | Triggered based on embedded ML output  |
| Dashboard Logging      | Optional        | Yes        | Online mode stores historical data     |
| Sensor Sampling Rate   | 500 ms          | 500 ms     | Continuous monitoring                   |
| Data Filtering         | Yes             | Yes        | NaN readings removed in real-time       |

---

### 3.3 Real-Time Performance

* **Instant local alerts:** Embedded ML on ESP32 guarantees immediate response (<50 ms)  
* **Scalable online monitoring:** Flask API, Firebase, and Laravel dashboard provide real-time analytics, historical trends, and multi-device oversight  
* **Dual-mode operation:** Offline alerts ensure safety, online pipeline enables monitoring, reporting, and analytics  

---

### 3.4 Key Findings

* **Embedded ML on IoT devices** enables autonomous, real-time fire detection  
* **Offline mode** ensures safety-critical responsiveness  
* **Online mode** enables monitoring, historical analytics, and multi-device management  
* Integration of IoT sensing and ML inference improves reliability, speed, and system intelligence  
* Sensor calibration and environmental factors remain critical for accuracy  

---

## 4. Challenges Faced

* Sensor noise & missing data handled via real-time filtering on ESP32  
* ESP32 memory constraints solved by optimizing TinyML model (<50 KB)  
* SMS integration required asynchronous handling due to serial timing and power  
* Offline + Online dual-mode increased system complexity but ensured reliability and modularity  

---

## 5. Production Improvements

**Short-term (1–2 weeks):**  
- Add additional sensors (smoke, CO2)  
- Tune thresholds and optimize ESP32 processing loops  

**Medium-term (1–2 months):**  
- Retrain model with expanded sensor data  
- Enhance dashboard analytics and IoT health monitoring  
- Multi-channel notifications  

**Long-term (3–6 months):**  
- Deploy multiple ESP32 nodes with synchronized dashboards  
- Predictive fire risk modeling using historical sensor data  
- Scalable multi-device monitoring with robust analytics  

---

## 6. API & Deployment

* **ESP32 Firmware:** Arduino IDE, TinyML embedded for local inference  
* **Flask API:** Receives sensor data and ML predictions  
* **Firebase & Laravel:** Real-time storage, dashboard visualization, historical analytics  
* **Communication:** HTTP + SMS via SIM800L  
* **Redundancy:** Offline embedded ML ensures autonomous operation; online system adds monitoring, reporting, and scaling  

---

## 7. Conclusion

FlameGuard demonstrates a **fully integrated IoT + ML system** with **dual offline/online operation**:

* Embedded ML on ESP32 provides **instant, autonomous fire detection**  
* Continuous IoT sensing supplies **high-quality real-time inputs**  
* SMS and dashboard notifications provide **reliable alerts and historical analytics**  
* Achieves **96.5% accuracy**, low latency, and resilient dual-mode operation  

**Next Steps:** Expand sensors, improve model robustness, scale for multi-node deployments, and enhance analytics.


---

## 8. Appendix

### Deliverables Checklist

* [x] ESP32 firmware with TinyML model  
* [x] Flask API for online ML inference  
* [x] Firebase integration for real-time data  
* [x] Laravel + Filament dashboard  
* [x] SMS alert system via SIM800L  
* [x] Technical documentation & report  

### Repository & Project Structure

```
flameguard/
├── app/                          # Laravel application
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Middleware/
│   ├── Models/
│   └── Providers/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
├── resources/
│   ├── views/
│   ├── js/
│   └── css/
├── routes/
│   ├── web.php
│   └── api.php
├── tests/
├── ML-API/                        # TinyML training & model scripts
│   ├── model_training.py
│   ├── preprocessing.py
│   └── flameguard_model.tflite
├── esp32_firmware/                # ESP32 firmware
│   ├── flameguard.ino
│   └── flameguard_model.h
├── firebase/                      # Firebase setup
│   └── serviceAccountKey.json
├── dashboard/                     # Laravel + Filament dashboard
├── docs/
│   └── FlameGuard_Report.md
├── README.md
└── LICENSE
```

### Computational Environment

* **Hardware:** ESP32 Dev Kit, SIM800L, DHT22, MQ2  
* **Firmware:** Arduino IDE  
* **Flask API:** Python 3.9+, TensorFlow Keras, Firebase Admin SDK  
* **Dashboard:** Laravel 10, Filament 3  
* **Communication:** HTTP, SMS via SIM800L  

### Time Investment

* ESP32 Firmware & Offline ML: ~3 hours  
* Flask API & Firebase Integration: ~2 hours  
* Dashboard Setup (Laravel + Filament): ~4 hours  
* Testing & SMS Integration: ~2 hours  
* Documentation: ~1 hour  
* **Total:** ~12 hours

---

**End of Report**
