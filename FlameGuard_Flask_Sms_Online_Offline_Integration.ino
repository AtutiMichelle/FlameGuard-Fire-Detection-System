#include <Arduino.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>

// ------------------- SIM800L -------------------
#include <HardwareSerial.h>
#define SIM800_RX 25
#define SIM800_TX 26
HardwareSerial sim800l(1);
const char* ALERT_PHONE_NUMBER = "+254741677043";

// ------------------- WIFI ----------------------
//Home Wifi
const char* ssid = "Strong_Wee";
const char* password = "XGETDJWT";
const char* flaskURL = "http://192.168.1.248:5000/predict";

// Hotspot Wifi IP
//const char* ssid = "@rgentum";
//const char* password = "Argentum9576";
//const char* flaskURL = "http://192.168.248.165:5000/predict";
                                                             


// const char* ssid = "Sunchaser";
// const char* password = "miami@2020";
// const char* flaskURL = "http://192.168.0.115:5000/predict";


// ------------------- SENSORS -------------------
#define DHTPIN 15
#define DHTTYPE DHT22
DHT dht(DHTPIN, DHTTYPE);

const int MQ2_PIN = 34;
const int RED_LED = 18;
const int GREEN_LED = 19;
const int BUZZER_PIN = 21;

// ------------------- TIMING -------------------
unsigned long previousMillis = 0;
const long interval = 5000;
unsigned long lastAlertTime = 0;
const long SMS_COOLDOWN = 60000;

// ------------------- ML MODEL -------------------
#include "tensorflow/lite/micro/micro_interpreter.h"
#include "tensorflow/lite/micro/micro_mutable_op_resolver.h"
#include "tensorflow/lite/micro/tflite_bridge/micro_error_reporter.h"
#include "tensorflow/lite/schema/schema_generated.h"
#include "tensorflow/lite/c/common.h"
#include "flameguard_model_quant.h"

constexpr int kTensorArenaSize = 5 * 1024;
uint8_t tensor_arena[kTensorArenaSize];

const tflite::Model* model;
tflite::MicroInterpreter* interpreter;
TfLiteTensor* input;
TfLiteTensor* output;

tflite::MicroMutableOpResolver<10> resolver;

// ------------------- NORMALIZATION -------------------
float means[3]  = {718.7809425, 36.781925, 37.749115};
float scales[3] = {287.32882595, 9.37746187, 9.89468179};

// -----------------------------------------------------
void sendCommand(const char* cmd);
void sendSMS(const char* number, const char* message);
void triggerAlarm();

// -----------------------------------------------------
float readMQ2() { return analogRead(MQ2_PIN); }
float readTemperature() {
  float t = dht.readTemperature();
  return isnan(t) ? 25.0 : t;
}
float readHumidity() {
  float h = dht.readHumidity();
  return isnan(h) ? 60.0 : h;
}

// ------------------- SERIAL LOG ----------------------
void logToSerial(float gas, float temp, float hum,
                 float features[3], float fire_prob, bool fireDetected) {
  Serial.println("---------- FlameGuard Log ----------");
  Serial.printf("Gas: %.2f\n", gas);
  Serial.printf("Temp: %.2f C\n", temp);
  Serial.printf("Humidity: %.2f %%\n", hum);

  Serial.print("Normalized: ");
  for (int i = 0; i < 3; i++) {
    Serial.printf("%.3f ", features[i]);
  }
  Serial.println();

  Serial.printf("Fire Probability: %.3f\n", fire_prob);

  if (fireDetected) Serial.println(" FIRE DETECTED!");
  else Serial.println(" Environment Safe");

  Serial.println("-------------------------------------\n");
}

// ===================== SETUP ==========================
void setup() {
  Serial.begin(115200);

  // SIM800L Init
  sim800l.begin(9600, SERIAL_8N1, SIM800_RX, SIM800_TX);
  delay(2000);
  sendCommand("AT");
  sendCommand("AT+CSQ");
  sendCommand("AT+CREG?");
  sendCommand("AT+CMGF=1");

  // WiFi Init
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  unsigned long wifiStart = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - wifiStart < 7000) {
    Serial.print(".");
    delay(500);
  }
  if (WiFi.status() == WL_CONNECTED)
    Serial.println("\n WiFi Connected");
  else
    Serial.println("\n WiFi Offline Mode Enabled");

  // Sensors
  pinMode(MQ2_PIN, INPUT);
  pinMode(RED_LED, OUTPUT);
  pinMode(GREEN_LED, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  dht.begin();

  digitalWrite(GREEN_LED, HIGH);

  Serial.println(" FlameGuard Ready (Online + Offline)");

  // ML Model Load
  model = tflite::GetModel(flameguard_model_quant);

  resolver.AddFullyConnected();
  resolver.AddRelu();
  resolver.AddQuantize();
  resolver.AddDequantize();
  resolver.AddSoftmax();
  resolver.AddLogistic();

  static tflite::MicroInterpreter static_interpreter(
    model, resolver, tensor_arena, kTensorArenaSize);

  interpreter = &static_interpreter;

  if (interpreter->AllocateTensors() != kTfLiteOk) {
    Serial.println("❌ Tensor Allocation Failed");
    while (1);
  }

  input = interpreter->input(0);
  output = interpreter->output(0);
}

// ===================== LOOP ==========================
// Add these global variables
bool httpInProgress = false;
unsigned long httpStartTime = 0;
float pending_mq2, pending_temp, pending_hum, pending_fire_prob;
bool pending_fire;

void loop() {
  unsigned long currentMillis = millis();

  // Handle pending HTTP request
  if (httpInProgress) {
    if (currentMillis - httpStartTime > 3000) { // Timeout after 3 seconds
      Serial.println(" HTTP request timeout");
      httpInProgress = false;
    }
  }

  if (currentMillis - previousMillis < interval) return;
  previousMillis = currentMillis;

  float mq2 = readMQ2();
  float temp = readTemperature();
  float hum = readHumidity();

  // ----- ML Inference -----
  float features[3];
  features[0] = (mq2 - means[0]) / scales[0];
  features[1] = (temp - means[1]) / scales[1];
  features[2] = (hum - means[2]) / scales[2];

  for (int i = 0; i < 3; i++) input->data.f[i] = features[i];

  if (interpreter->Invoke() != kTfLiteOk) {
    Serial.println(" Inference Failed");
    return;
  }

  float fire_prob = output->data.f[0];
  bool fire = fire_prob >= 0.4;
  float confidence = fire ? fire_prob : (1.0 - fire_prob);

  //  NON-BLOCKING: Store data for async HTTP
  if (WiFi.status() == WL_CONNECTED && !httpInProgress) {
    pending_mq2 = mq2;
    pending_temp = temp;
    pending_hum = hum;
    pending_fire_prob = fire_prob;
    pending_fire = fire && confidence > 0.7;
    
    sendToDashboardAsync(); // Start async request
  }

  // Log to serial (real-time)
  logToSerial(mq2, temp, hum, features, fire_prob, fire && confidence > 0.7);

  // Handle alarms & LEDs
  if (fire && confidence > 0.7) {
    digitalWrite(RED_LED, HIGH);
    digitalWrite(GREEN_LED, LOW);
    triggerAlarm();

    if (millis() - lastAlertTime > SMS_COOLDOWN) {
      char msg[160];
      snprintf(msg, sizeof(msg),
        "🚨 FIRE ALERT!\nConfidence: %.1f%%\nGas: %.1f\nTemp: %.1f°C\nHum: %.1f%%",
        confidence * 100, mq2, temp, hum);
      sendSMS(ALERT_PHONE_NUMBER, msg);
      lastAlertTime = millis();
    }
  }
  else {
    digitalWrite(GREEN_LED, HIGH);
    digitalWrite(RED_LED, LOW);
    digitalWrite(BUZZER_PIN, LOW);
  }
}

void sendToDashboardAsync() {
  httpInProgress = true;
  httpStartTime = millis();
  
  HTTPClient http;
  http.begin(flaskURL);
  http.addHeader("Content-Type", "application/json");
  http.setTimeout(3000);

  DynamicJsonDocument doc(300);
  doc["device_id"] = "esp32_flameguard_001";
  doc["mq2"] = pending_mq2;
  doc["temp"] = pending_temp;
  doc["humidity"] = pending_hum;
  doc["fire_probability"] = pending_fire_prob;
  doc["fire_detected"] = pending_fire;

  String payload;
  serializeJson(doc, payload);

  // This happens in background
  int code = http.POST(payload);
  
  if (code > 0) {
    Serial.println(" Data sent online");
  } else {
    Serial.printf(" HTTP Failed: %d\n", code);
  }
  http.end();
  
  httpInProgress = false;
}
  // ------------------- ONLINE MODE -------------------


// ===================== SIM800 FUNCTIONS ==========================
void sendCommand(const char* cmd) {
  sim800l.println(cmd);
  delay(500);
  while (sim800l.available()) Serial.write(sim800l.read());
}

void sendSMS(const char* number, const char* message) {
  sim800l.println("AT+CMGF=1");
  delay(500);
  sim800l.print("AT+CMGS=\"");
  sim800l.print(number);
  sim800l.println("\"");
  delay(500);
  sim800l.print(message);
  delay(500);
  sim800l.write(26);
  delay(5000);
  Serial.println("📨 SMS Sent!");
}

void triggerAlarm() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    delay(200);
    digitalWrite(BUZZER_PIN, LOW);
    delay(150);
  }
}
