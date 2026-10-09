# 🏙️ Event Community Hub (CSC264)

A web-based event management and community engagement platform built using **PHP** and **MySQL** for coursework **CSC264 (Web Application Development)**.

---

## 📌 Project Overview
This platform streamlines event organization and resident participation within a local community. It provides distinct interfaces for administrators, organizers, and residents to manage schedules, publish events, collect user feedback, and foster community interaction.

---

## 🛠️ Tech Stack
* **Frontend:** HTML5, CSS3, JavaScript
* **Backend:** PHP
* **Database:** MySQL
* **Environment:** XAMPP / Apache Server

---

## ✨ Key Features
* 👤 **Multi-Role Authentication:** Dedicated portals for Admins, Event Organizers, and Community Residents.
* 📅 **Event Management:** Create, publish, finalize, and view details for community activities.
* 💬 **Feedback & Inquiries:** Integrated messaging and feedback collection system (`admin_feedback.php`, `submit_feedback.php`).
* 📊 **Admin Dashboard:** Centralized management for event approvals and system navigation.

---

## 🚀 Installation & Local Setup

### 1. Prerequisites
Ensure **XAMPP** (or any local Apache + MySQL stack) is installed on your computer.

### 2. Download / Clone
Place the repository files into your local server root directory (e.g., `C:\xampp\htdocs\event-hub`).

### 3. Database Setup
1. Open `http://localhost/phpmyadmin/`.
2. Create a new database named `smartville_db`.
3. Import the `smartville_db.sql` file included in this repository.

### 4. Configuration
Ensure your `db_connect.php` file matches your local database settings:
```php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "smartville_db";
