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

### 🔧 In Progress
- Building custom login and register pages (with Google OAuth planned)
- Creating role-based dashboard redirects:
  - Admin → Filament Admin Dashboard  
  - User → User Dashboard

---

## 🧰 Tech Stack

| Layer | Technology |
|-------|-------------|
| Backend Framework | Laravel 11 |
| Admin Dashboard | FilamentPHP v4 |
| Database | MySQL |
| Frontend | Blade Templates |
| Authentication | Laravel Auth (custom login/register + Google OAuth planned) |

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

## 🧩 Next Steps

* Implement Google OAuth using **Laravel Socialite**
* Design custom login/register pages
* Add Filament dashboards for Admin and User roles
* Integrate IoT + ML modules for real-time fire detection alerts

---

## 📜 License

This project is licensed under the **MIT License**.

---

> Developed with ❤️ by **Michelle Atuti**

```


