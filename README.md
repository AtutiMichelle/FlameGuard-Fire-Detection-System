# 🔥 FlameGuard - Fire Detection System

**FlameGuard** is a smart fire detection and alert system that combines IoT sensor data and a machine learning backend to detect and notify users about potential fire hazards in real time.  
This repository hosts the **web application** built using **Laravel** and **FilamentPHP** for dashboard management.

---

## 🚀 Current Progress

### ✅ Completed
- Laravel 11 project setup (`flameguard`)
- Database configuration and migration setup
- Installed and configured **FilamentPHP v4**
- Created `users` table with `role` column for role-based access control (admin / user)
- Added an `AdminUserSeeder` for creating a default admin user
- Basic project structure connected to a MySQL database
- Configured custom authentication (login & register) using Laravel Breeze
- Integrated Google OAuth with Laravel Socialite
- Successfully linked OAuth authentication to Filament dashboards:
  - Admin → Admin Filament Dashboard
  - User → User Filament Dashboard
- Implemented role-based redirects after both traditional and Google logins

### 🔧 In Progress
- Adding Fire Detection data visualization on dashboards
- Integrating real-time IoT + ML fire prediction modules
- Implementing alert notifications (email/SMS)

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend Framework | Laravel 11 |
| Admin/User Dashboards | FilamentPHP v4 |
| Database | MySQL |
| Frontend | Blade Templates |
| Authentication | Laravel Breeze + Socialite (Google OAuth) |

---

## ⚙️ Installation Guide

### 1️⃣ Clone the repository
```bash
git clone https://github.com/AtutiMichelle/FlameGuard-Fire-Detection-System.git
cd FlameGuard-Fire-Detection-System
````

### 2️⃣ Install dependencies

```bash
composer install
```

### 3️⃣ Configure environment

Copy `.env.example` to `.env` and update your database credentials:

```bash
cp .env.example .env
php artisan key:generate
```
Update your .env with:
Database credentials
Google OAuth credentials:

```bash
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

```

### 4️⃣ Run migrations and seeders

```bash
php artisan migrate --seed
```

### 5️⃣ Serve the application

```bash
php artisan serve
```

Now visit [http://localhost:8000](http://localhost:8000) 🎉

---

## 👑 Default Admin Credentials

After seeding, log in as the admin:

```
Email: admin@flameguard.com
Password: password
```

*(Change these in `database/seeders/AdminUserSeeder.php` if needed.)*


---

## 🔐 Authentication Overview

FlameGuard supports two authentication methods:

| Method               | Description                                             |
| -------------------- | ------------------------------------------------------- |
| **Email & Password** | Traditional login and registration via Laravel Breeze   |
| **Google OAuth**     | Social login using Laravel Socialite for quicker access |

After authentication, users are redirected automatically:

* 🧑‍💼 **Admin:** `/admin` → Filament Admin Dashboard
* 👤 **User:** `/user` → Filament User Dashboard

---

## 🧩 Next Steps

* ✅ Finalize Google OAuth error handling
* 🎨 Improve dashboard UI with Filament components
* ⚙️ Integrate IoT sensor data streams
* 🤖 Train and deploy ML model for fire detection
* 📱 Add notification system for real-time alerts

---

## 🧠 Project Structure

```
flameguard/
├── app/
│   ├── Http/Controllers/
│   │   ├── Auth/AuthenticatedSessionController.php
│   │   ├── SocialAuthController.php
│   ├── Models/User.php
│   ├── Filament/
│   │   ├── Admin/
│   │   └── User/
├── database/
│   ├── migrations/
│   ├── seeders/AdminUserSeeder.php
├── resources/views/auth/
├── routes/web.php
└── .env
```

---


## 📜 License

This project is licensed under the **MIT License**.

---

> Developed with ❤️ by **Michelle Atuti**

```


