# 🏥 UniClinic — Clinic & Medical Services Sub-system
**College/University Medical Records System**

---

## 📋 FEATURES

### 5 Functional Modules:
1. **Student Medical Records** — Health history, vitals, allergies, past illnesses
2. **Consultation & Treatment Logs** — Visit tracking, diagnosis, prescriptions
3. **Medicine Inventory & Dispensing** — Stock management, restock, dispensing log
4. **Medical Clearance Issuance** — Generate, approve, and print clearances
5. **Health Incident Reporting** — Accident/injury tracking with resolution workflow

### Access Control:
- 🛡️ **Admin** — Full access + User Management
- 👨‍⚕️ **Doctor** — All modules (read/write)
- 👩‍⚕️ **Nurse** — All modules (read/write)

---

## 🚀 SETUP INSTRUCTIONS

### Prerequisites:
- XAMPP / WAMP / LAMP (PHP 7.4+ & MySQL 5.7+)

### Step 1: Place Files
Copy the `clinic_system` folder into your web root:
- XAMPP: `C:/xampp/htdocs/clinic_system/`
- WAMP:  `C:/wamp64/www/clinic_system/`

### Step 2: Create Database
1. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
2. Create a new database named `clinic_system`
3. Import the file: **`database.sql`**

### Step 3: Configure Database (if needed)
Edit `php/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_NAME', 'clinic_system');
```

### Step 4: Access the System
Open: `http://localhost/clinic_system/`

---

## 🔑 DEFAULT LOGIN ACCOUNTS

| Role  | Email                          | Password   |
|-------|-------------------------------|------------|
| Admin | admin@university.edu          | password   |
| Doctor| dr.santos@university.edu      | password   |
| Nurse | nurse.reyes@university.edu    | password   |

---

## 📁 FILE STRUCTURE

```
clinic_system/
├── index.php              ← Login page
├── dashboard.php          ← Dashboard with stats & charts
├── students.php           ← Student management
├── medical_records.php    ← Module 1: Medical Records
├── consultations.php      ← Module 2: Consultation Logs
├── medicines.php          ← Module 3: Medicine Inventory
├── clearances.php         ← Module 4: Medical Clearance
├── incidents.php          ← Module 5: Health Incidents
├── users.php              ← User Management (Admin only)
├── database.sql           ← Database schema + sample data
├── php/
│   ├── config.php         ← DB config & helper functions
│   ├── auth.php           ← Login/logout handler
│   └── api.php            ← All CRUD API endpoints
└── includes/
    ├── header.php         ← Sidebar + nav layout
    └── footer.php         ← Scripts + footer
```

---

## 🎨 TECH STACK
- **Frontend**: HTML5, Bootstrap 5.3, Bootstrap Icons, Chart.js
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Fonts**: Nunito + Playfair Display (Google Fonts)
- **Theme**: Soft & Friendly pastel tones

---

## 💡 TIPS
- Change passwords after first login (edit users in User Management)
- The clearance print function uses the browser's built-in print dialog
- Medicine stock turns yellow when at/below minimum stock level
- Incidents marked "open" can be quickly resolved with the ✓ button
