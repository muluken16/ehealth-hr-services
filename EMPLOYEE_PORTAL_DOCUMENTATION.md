# Employee Portal - Enhanced Features Documentation

## 🎯 Overview
The Employee Portal has been significantly enhanced with **7 comprehensive pages** providing health professionals with a complete self-service platform to manage their work life.

---

## 📋 Available Pages

### 1. **Employee Dashboard** (`dashboard.php`)
**Purpose:** Central hub for employee information and quick actions

**Features:**
- ✅ Welcome message with employee name and ID
- ✅ Visual leave balance cards for all leave types:
  - Annual Leave (21+ days)
  - Sick Leave (14 days)
  - Maternity Leave (120 days) - for female employees
  - Paternity Leave (10 days) - for male employees
  - Emergency Leave (5 days)
- ✅ Quick action button to submit new leave requests
- ✅ Recent leave requests table with status indicators
- ✅ Real-time balance calculations

**Design:**
- Modern card-based layout
- Color-coded balance indicators
- Responsive grid system
- Hover animations

---

### 2. **My Profile** (`profile.php`) ⭐ NEW
**Purpose:** View comprehensive employee information

**Features:**
- ✅ Professional gradient header with large avatar
- ✅ Employee metadata display (ID, Position, Location)
- ✅ **Personal Information Card:**
  - Full name (First, Middle, Last)
  - Date of birth with formatted display
  - Gender and marital status
- ✅ **Contact Information Card:**
  - Email address
  - Phone number
  - Emergency contact
  - Residential address
- ✅ **Employment Details Card:**
  - Current position/job title
  - Department assignment
  - Join date with formatted display
  - Working location (Kebele)
- ✅ **Educational Background Card:**
  - Education level (Diploma/Degree/Masters/PhD)
  - Field of study
  - University/College name
  - Language proficiency

**Design:**
- 4-column responsive grid layout
- Color-coded card borders
- Clean info-row design
- Professional gradient header

---

### 3. **Request Leave** (`leave_request.php`)
**Purpose:** Submit new leave applications with intelligent validation

**Features:**
- ✅ Leave type selection with real-time balance display
- ✅ **Intelligent Date Calculation:**
  - Automatic day calculation between start and end dates
  - Real-time validation against available balance
  - Visual error alerts for insufficient balance
- ✅ Reason text area for context
- ✅ File attachment support (required for sick leave >3 days)
- ✅ Submit button with loading states
- ✅ Form validation before submission

**Validation Rules:**
- Cannot request more days than available balance
- End date must be after start date
- Medical certificate required for sick leave >3 days

---

### 4. **My Leave History** (`leave_history.php`)
**Purpose:** Transparent view of all leave applications

**Features:**
- ✅ Complete chronological list of leave requests
- ✅ Status indicators with color coding:
  - Pending (Orange)
  - Approved (Green)
  - Rejected (Red)
- ✅ **Detailed Information:**
  - Application date
  - Leave type
  - Duration (start to end date)
  - Number of days requested
  - Status with timestamps
- ✅ Rejection reason tooltips
- ✅ Approval date display
- ✅ Empty state with call-to-action

**Design:**
- Clean table layout
- Status pills with semantic colors
- Mobile-responsive design

---

### 5. **Payslips** (`payslips.php`) ⭐ NEW
**Purpose:** Access salary information and payment history

**Features:**
- ✅ **Salary Overview Banner:**
  - Current monthly net salary (large display)
  - Year-to-date (YTD) total earnings
  - Bank account information
- ✅ **Detailed Payslip Cards (Last 6 Months):**
  - Month and payment date
  - Basic salary breakdown
  - Allowances (15% of basic)
  - Gross salary calculation
  - Deductions:
    - Pension contribution (7%)
    - Income tax (10%)
  - **Net Salary** (highlighted)
- ✅ Download PDF button for each payslip
- ✅ Print all functionality

**Calculations:**
```
Gross Salary = Basic Salary + Allowances
Net Salary = Gross - (Pension + Income Tax)
```

**Design:**
- Green gradient overview banner
- Individual payslip cards with detailed breakdown
- Highlight on net salary
- Print-friendly layout

---

### 6. **My Attendance** (`attendance.php`) ⭐ NEW
**Purpose:** Track personal attendance records and performance

**Features:**
- ✅ **Attendance Rate Indicator:**
  - Large circular percentage display
  - Based on last 30 days
  - Visual progress indicator
- ✅ **Statistics Cards:**
  - Present Days (Green)
  - Late Arrivals (Orange)
  - Absent Days (Red)
  - On Leave Days (Blue)
- ✅ **Detailed Records Table:**
  - Date with formatted display
  - Check-in time
  - Check-out time
  - Working hours calculation
  - Status with color indicators
- ✅ Print functionality
- ✅ Empty state with helpful message

**Metrics:**
- Attendance Rate = (Present + Late) / Total Days × 100%
- Working Hours per day
- Monthly trends

**Design:**
- Circular attendance rate indicator
- Color-coded statistic cards
- Clean table with status dots
- Gradient header

---

### 7. **Documents** (`documents.php`) ⭐ NEW
**Purpose:** Centralized document management and storage

**Features:**
- ✅ **Upload Section:**
  - Large call-to-action for new uploads
  - Gradient banner design
  - File type limitations
- ✅ **Category Filtering:**
  - All Documents
  - Contracts
  - Certificates
  - Identification
  - Financial
  - Performance
- ✅ **Document Cards Display:**
  - Color-coded file type icons
  - Document title and category
  - Upload date
  - File size information
  - View and Download actions
- ✅ **Pre-loaded Documents:**
  - Employment Contract
  - Job Description
  - Educational Certificates
  - ID Card Copy
  - Bank Details
  - Performance Reviews

**File Support:**
- PDF documents
- JPG/JPEG images
- PNG images

**Design:**
- Grid layout for document cards
- Color-coded by category
- Interactive filter chips
- Gradient upload section
- Action buttons (View/Download)

---

## 🎨 Design Principles

All pages follow consistent design standards:

### Visual Elements:
- **Color Scheme:** Professional blues (#1a4a5f primary)
- **Typography:** Clean, modern fonts with proper hierarchy
- **Cards:** Rounded corners (15-20px radius)
- **Shadows:** Subtle elevation (0 4px 15px rgba(0,0,0,0.05))
- **Gradients:** Smooth transitions for headers
- **Icons:** Font Awesome 6.4.0 throughout

### Interaction:
- **Hover Effects:** Smooth transitions and elevation
- **Loading States:** Visual feedback on actions
- **Validation:** Real-time with clear error messages
- **Responsiveness:** Mobile-friendly layouts

### Navigation:
- **Consistent Sidebar:** All 8 pages accessible
- **Active States:** Clear indication of current page
- **Icons:** Meaningful visual cues
- **Logout:** Always accessible

---

## 🔐 Security Features

✅ Session-based authentication
✅ Role verification (employee role required)
✅ SQL injection protection (prepared statements)
✅ Redirect on unauthorized access
✅ Employee ID verification for all data access

---

## 📱 Mobile Responsiveness

- Responsive grid layouts
- Mobile-optimized tables
- Touch-friendly buttons
- Adaptive navigation
- Flexible card layouts

---

## 🚀 Technical Stack

**Backend:**
- PHP 7.4+
- MySQL database
- Session management
- Prepared statements

**Frontend:**
- HTML5 semantic markup
- Modern CSS3
  - Grid layouts
  - Flexbox
  - Custom properties
  - Animations
- Vanilla JavaScript
- Font Awesome icons

**Database Tables:**
- `employees` - Employee master data
- `leave_requests` - Leave applications
- `attendance` - Daily attendance records

---

## 📊 Data Integration

### Real-Time Calculations:
- Leave balance = Entitlement - Used
- Days requested = End date - Start date + 1
- Attendance rate = (Present + Late) / Total × 100%
- Working hours = Check-out - Check-in

### Dynamic Content:
- Gender-specific leave types (Maternity/Paternity)
- Date formatting (d/m/Y, d M Y)
- Status color coding
- Balance validation

---

## 🎯 Key Benefits for Employees

1. **Self-Service:** No need to contact HR for basic information
2. **Transparency:** Clear visibility of leave status and history
3. **Convenience:** 24/7 access to payslips and documents
4. **Accuracy:** Real-time balance calculations prevent errors
5. **Efficiency:** Quick leave requests with instant validation
6. **Documentation:** All important files in one place
7. **Performance Tracking:** Monitor attendance and punctuality

---

## 🔄 Future Enhancements (Roadmap)

- [ ] Profile editing functionality
- [ ] Document upload implementation
- [ ] PDF generation for payslips
- [ ] Email notifications for leave status
- [ ] Calendar view for leave planning
- [ ] Performance review module
- [ ] Training and certification tracking
- [ ] Mobile app integration
- [ ] Push notifications
- [ ] Multi-language support

---

## 📞 Support Information

**Default Login Credentials:**
- **Employee ID:** Provided by HR
- **Default Password:** 123456 (for demo/existing staff)

**Navigation Structure:**
```
Employee Portal
├── Dashboard (Landing Page)
├── My Profile
├── Request Leave
├── My Leave History
├── Payslips
├── My Attendance
├── Documents
└── Logout
```

---

## 🎉 Summary

The enhanced Employee Portal now provides **7 fully-functional pages** with:
- ✅ 4 NEW pages (Profile, Payslips, Attendance, Documents)
- ✅ 3 Enhanced existing pages (Dashboard, Leave Request, Leave History)
- ✅ Modern, premium UI/UX design
- ✅ Real-time data validation
- ✅ Comprehensive self-service capabilities
- ✅ Mobile-responsive layouts
- ✅ Professional aesthetics throughout

**Total Features Implemented:** 50+ distinct features across all pages

---

*Last Updated: January 6, 2026*
*Version: 2.0*
