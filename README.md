# 🌍 TripTrove - Tours & Travel Management System

TripTrove is a modern, full-stack travel booking and tour management web application built with PHP, MySQL, JavaScript, HTML5, and CSS3. It allows users to explore international, national, and adventure tour packages, view detailed day-by-day itineraries, book tours with online payment integration (Razorpay), and download official PDF booking receipts. It also includes an administrative dashboard powered by AdminLTE.

---

## ✨ Features

### 👤 User Side
- **Interactive Home & Showcase**: Hero carousel, destination highlights, and modern responsive navigation.
- **Categorized Tours**:
  - ✈️ **International**: Bali, Thailand, Maldives, Singapore, Switzerland, Dubai, Europe, Greece.
  - 🇮🇳 **National**: Kashmir, Jaipur, Udaipur, Mussoorie, Goa, Andaman, Kerala, Nainital.
  - 🧗 **Adventure**: Spiti Valley, Bir Billing, Jim Corbett Safari, Kedarnath Trek, Auli Snow Skiing, Manali, Leh-Ladakh, Rishikesh.
- **Detailed Itinerary Timelines**: Day-by-day itinerary schedule breakdown with interactive cards, travel key highlights, inclusions & exclusions.
- **Online Booking Checkout**:
  - Live calculation of total fare based on number of travelers.
  - Automatic arrival/return date computation based on tour duration.
- **Razorpay Payment Gateway**: Seamless online payment modal integration with test/sandbox demo support.
- **Downloadable PDF Receipts**: Instant, printable official booking voucher and invoice with one-click PDF download (`html2pdf.js`).
- **User Authentication**: Registration, Login, and Password Reset.

### 🛡️ Admin Side (AdminLTE)
- **Analytics Dashboard**: Overview of bookings, users, packages, and itineraries.
- **Package Management**: Add, edit, and delete travel packages with image uploads.
- **Itinerary Management**: Update tour descriptions, schedules, departure cities, and budgets.
- **Bookings & Payments Tracker**: View all customer reservations, Razorpay Payment IDs, payment statuses, and instant links to view customer receipts.

---

## 🛠️ Tech Stack
- **Backend**: PHP 8.x
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (ES6+), FontAwesome Icons
- **Admin Theme**: AdminLTE 3
- **Payment Gateway**: Razorpay Standard Checkout SDK
- **PDF Generation**: `html2pdf.js` & Print CSS

---

## 🚀 Getting Started

### 1. Prerequisites
- [XAMPP](https://www.apachefriends.org/) (PHP 8.x and MySQL / MariaDB)
- Web Browser (Chrome, Edge, Firefox)

### 2. Database Setup
1. Start **MySQL** from XAMPP Control Panel.
2. Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
3. Create a new database named `triptrove`.
4. Import the SQL file located at `main/DataBase/triptrove.sql`.

### 3. Database Configuration
Verify your database credentials in `main/db_connect.php`:
```php
$host = "127.0.0.1";
$user = "root";
$password = "";
$database = "triptrove";
```

### 4. Running the Project
#### Option A: Using PHP Development Server
Run the following command from the project root:
```bash
php -S localhost:8000 -t main
```
Then open [http://localhost:8000/index.php](http://localhost:8000/index.php) in your browser.

#### Option B: Using XAMPP Apache
1. Copy or place the folder inside `C:/xampp/htdocs/triptrove`.
2. Start **Apache** from XAMPP Control Panel.
3. Open [http://localhost/triptrove/index.php](http://localhost/triptrove/index.php).

---

## 📄 License
This project is for educational and commercial tour management purposes.
