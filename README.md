# # 🍱 Online Food Waste Management System

## 📌 Project Domain
Web Application

---

## 📖 Introduction
The **Online Food Waste Management System** is a web-based platform designed to reduce food wastage by connecting **food donors** (restaurants, grocery stores, individuals) with **NGOs and food banks**.

This system helps redistribute surplus food to those in need through an efficient and technology-driven process. It includes features like real-time tracking, expiration alerts, and location-based food discovery.

---

## 🎯 Objectives

- Reduce food wastage
- Help needy people by redistributing excess food
- Promote sustainability
- Provide a seamless donation and collection system

---

## 🚀 Key Features

### 👨‍💼 Admin Panel

- Secure Login / Logout
- Manage Donors & NGO Accounts
- Approve / Reject NGO Registrations
- Manage Food Listings
- Monitor Food Safety Guidelines
- Generate Reports:
  - Donations
  - Requests
  - Deliveries

---

### 🍽️ Food Donor (Restaurant / Store / Individual)

- User Registration & Login
- Add Food Donations:
  - Quantity
  - Category
  - Expiry Date
- Update / Delete Listings
- View NGO Requests
- Accept / Reject Pickup Requests
- Track Donation History
- View Impact Reports

---

### 🤝 NGO / Food Bank

- Registration & Login
- Browse Available Food:
  - Filter by category, location, expiry
- Send Pickup Requests
- Track Orders & Donation History
- Provide Feedback
- View Nearby Donors (Map Integration)

---

### 🚚 Food Collection & Delivery

- Assign Drivers / Volunteers
- Live Pickup Tracking
- Expiry Alerts for Urgent Food
- Generate Pickup Receipts
- Delivery Status Updates

---

### 📊 Dashboard & Reports

#### Donor Dashboard
- Past Donations
- Impact Statistics
- Pending Requests

#### NGO Dashboard
- Request Status
- Completed Pickups
- Pending Deliveries

#### Admin Dashboard
- System Analytics
- Donation Reports
- NGO Activity Tracking

---

## 🛠️ Technologies Used

- 💻 Frontend: HTML, CSS, Bootstrap
- 🧠 Backend: PHP (Procedural)
- 🗄️ Database: MySQL
- ⚙️ Server: XAMPP
- 🗺️ API: Google Maps API

---

## 📂 System Modules

1. Authentication Module
   - Login / Registration (Admin, Donor, NGO)

2. Donation Management Module
   - Add / Update / Delete food listings

3. Request Management Module
   - NGO requests & donor approvals

4. Delivery Management Module
   - Pickup assignment & tracking

5. Notification System
   - Expiry alerts
   - Request updates

6. Reporting Module
   - Donation analytics
   - System performance

---

## 🔄 System Workflow

1. Donor registers & logs in  
2. Donor adds surplus food  
3. NGO browses available food  
4. NGO sends pickup request  
5. Donor accepts/rejects request  
6. Delivery is assigned  
7. NGO collects food  
8. System updates reports & history  

---

## 📦 Installation Guide

```bash
1. Install XAMPP
2. Start Apache & MySQL
3. Copy project folder into:
   htdocs/
4. Import database via phpMyAdmin
5. Run project:
   http://localhost/project-folder
