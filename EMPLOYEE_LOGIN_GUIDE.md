# 🚀 Employee Login - Quick Start Guide

## 📋 Setup Instructions

### **Step 1: Generate Login Credentials**

Run this URL in your browser to create employee login accounts:
```
http://localhost/ehealth/ehealth/employee/setup_credentials.php
```

This will:
- ✅ Create the `users` table (if it doesn't exist)
- ✅ Generate passwords for all employees
- ✅ Display a table with all credentials
- ✅ Hash passwords securely

---

## 🔑 Password Generation Formula

**Pattern:** `First 3 letters of first name (lowercase) + Last 3 digits of phone number`

### Examples:

| Employee Name | Phone Number | Generated Password |
|---------------|--------------|-------------------|
| Abebe Bekele | +251 912 345 678 | **abe678** |
| Tigist Haile | +251 911 222 333 | **tig333** |
| Yohannes Tesfaye | +251 923 456 789 | **yoh789** |
| Marta Girma | +251 934 567 890 | **mar890** |

---

## 🌐 Access URLs

### 1. **Setup Credentials** (Run First)
```
http://localhost/ehealth/ehealth/employee/setup_credentials.php
```
Creates/updates all employee login accounts

### 2. **View Demo Credentials**
```
http://localhost/ehealth/ehealth/employee/demo_credentials.php
```
Beautiful page showing all employee logins with copy buttons

### 3. **Employee Login Page**
```
http://localhost/ehealth/ehealth/employee/login.php
```
The actual login page for employees

---

## 🎯 Testing Workflow

### Quick Test (3 Steps):

1. **Setup Credentials**
   - Visit: `setup_credentials.php`
   - Wait for "Successfully created X employee login credentials!"
   - Note down any employee ID and password

2. **View All Credentials** (Optional)
   - Visit: `demo_credentials.php`
   - Beautiful table with all accounts
   - Click "Copy" button to copy password
   - Click "Login" to go to login page

3. **Login**
   - Visit: `login.php`
   - Enter Employee ID (e.g., `EMP-001`)
   - Enter Password (e.g., `abe678`)
   - Click "Login"

---

## 📱 Login Page Features

### What's New:
- ✅ Modern gradient background
- ✅ Glass-morphism design
- ✅ Helpful error messages
- ✅ **"View Demo Login Credentials" link** for easy access
- ✅ Link to Kebele HR login
- ✅ Last login tracking
- ✅ Secure password verification

### Error Messages:
- **"Invalid password. Check demo credentials for help."** - Wrong password
- **"Employee ID not found. Please check your credentials."** - ID doesn't exist

---

## 🔐 Security Features

✅ **Password Hashing** - Uses PHP `password_hash()` with bcrypt
✅ **Prepared Statements** - SQL injection protection
✅ **Session Management** - Secure session handling
✅ **Role Verification** - Only 'employee' role can login
✅ **Last Login Tracking** - Records login timestamp

---

## 🗃️ Database Tables

### `users` Table Structure:
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,           -- Hashed password
    role VARCHAR(50) DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX(employee_id)
);
```

### How Login Works:
```sql
SELECT u.employee_id, u.password, e.first_name, e.last_name, e.working_kebele 
FROM users u 
JOIN employees e ON u.employee_id = e.employee_id 
WHERE u.employee_id = ? AND u.role = 'employee'
```

---

## 🎨 Demo Credentials Page Features

Beautiful, professional design with:
- 💜 **Purple gradient background**
- 📋 **Clean data table**
- 📋 **Password formula explanation**
- 📋 **Example with visual breakdown**
- 🔘 **Copy button** for each password
- 🔗 **Direct login links**
- 📱 **Mobile responsive**
- 🎯 **Color-coded badges**

---

## 🔍 Troubleshooting

### Problem: "Employee ID not found"
**Solution:** 
1. Run `setup_credentials.php` first
2. Check the employee exists in `employees` table
3. Verify employee_id spelling

### Problem: "Invalid password"
**Solution:**
1. Visit `demo_credentials.php` to see correct password
2. Check password formula: first 3 letters + last 3 phone digits
3. Password is lowercase
4. Example: "Abebe" with phone "912345678" = "abe678"

### Problem: Users table doesn't exist
**Solution:**
1. Run `setup_credentials.php`
2. It will automatically create the table

### Problem: Employees have no phone numbers
**Solution:**
- Default password will use "000" as last 3 digits
- Example: "Abebe" with no phone = "abe000"

---

## 📊 Sample Credentials Output

After running `setup_credentials.php`, you'll see:

```
✓ Successfully created/updated 20 employee login credentials!

┌──────────────┬─────────────────────┬──────────────────┬──────────┐
│ Employee ID  │ Name                │ Phone Number     │ Password │
├──────────────┼─────────────────────┼──────────────────┼──────────┤
│ EMP-001      │ Abebe Bekele        │ +251912345678    │ abe678   │
│ EMP-002      │ Tigist Haile        │ +251911222333    │ tig333   │
│ EMP-003      │ Yohannes Tesfaye    │ +251923456789    │ yoh789   │
└──────────────┴─────────────────────┴──────────────────┴──────────┘
```

---

## 🎉 Success Checklist

After setup, verify:
- [ ] `users` table exists in database
- [ ] Employee count in users = Employee count in employees
- [ ] Can view all credentials at `demo_credentials.php`
- [ ] Can login with any credential
- [ ] Redirects to dashboard after login
- [ ] Session persists across pages
- [ ] "View Demo Credentials" link works on login page

---

## 🔗 Navigation Flow

```
setup_credentials.php (Setup)
        ↓
demo_credentials.php (View All)
        ↓
login.php (Login)
        ↓
dashboard.php (Portal)
```

---

## 💡 Pro Tips

1. **Bookmark** `demo_credentials.php` for quick access during testing
2. **Use Copy Button** on demo page instead of typing passwords
3. **Phone Number Format** doesn't matter - only digits are used
4. **Case Sensitive** - Employee IDs are case-sensitive
5. **Clear Cache** if login page doesn't update

---

## 📞 Files Created

```
employee/
├── setup_credentials.php    ⭐ Setup script (run once)
├── demo_credentials.php     ⭐ Demo page (bookmark this)
├── login.php               ✏️ Updated login (main entry)
├── dashboard.php           ✅ Landing after login
├── profile.php             ✅ Employee profile
├── leave_request.php       ✅ Request leave
├── leave_history.php       ✅ Leave history
├── payslips.php            ✅ Salary info
├── attendance.php          ✅ Attendance records
└── documents.php           ✅ Document management
```

---

## 🎯 Quick Commands

### Setup Everything:
```bash
# 1. Create credentials
http://localhost/ehealth/ehealth/employee/setup_credentials.php

# 2. View credentials
http://localhost/ehealth/ehealth/employee/demo_credentials.php

# 3. Login
http://localhost/ehealth/ehealth/employee/login.php
```

### Test Login Flow:
1. Copy any Employee ID from demo page
2. Copy corresponding password
3. Paste into login form
4. Click Login
5. Should redirect to dashboard

---

**You're all set! 🚀**

*The system auto-generates secure, memorable passwords for all employees!*

---

**Last Updated:** January 6, 2026  
**Version:** 1.0
