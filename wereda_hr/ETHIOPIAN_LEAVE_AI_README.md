# 🇪🇹 Ethiopian Leave Management AI System

## Overview
An intelligent leave management system that automatically evaluates employee leave requests based on **Ethiopian Labour Proclamation No. 1156/2019**.

## 🎯 Features

### ✅ Automated Leave Evaluation
The AI automatically approves or rejects leave requests based on Ethiopian law, including:

1. **Annual Leave** (Article 74)
   - 16 days after 1 year of service
   - +1 day for every additional 2 years
   - Automatic balance tracking and deduction

2. **Bereavement Leave** (Article 76)
   - Maximum 3 working days per death
   - Fully paid
   - No balance deduction

3. **Sick Leave** (Article 75)
   - Maximum 6 months (180 days) per year
   - Medical proof required
   - Automatic tracking of used days

4. **Maternity Leave** (Article 88)
   - Female employees only
   - 120 days total (30 prenatal + 90 postnatal)
   - Fully paid
   - No annual leave deduction

5. **Paternity Leave** (Article 89)
   - Male employees only
   - 3 working days per birth
   - Fully paid
   - No balance deduction

## 📋 Installation

### Step 1: Run Database Migration
```sql
mysql -u root -p ehealth_db < ethiopian_leave_ai_setup.sql
```

This will:
- Add AI decision tracking columns to `leave_requests`
- Create `leave_balances` table
- Initialize leave balances for all active employees

### Step 2: Verify Files
Ensure these files exist in `wereda_hr/` directory:
- ✅ `ethiopian_leave_ai.php` - Core AI decision engine
- ✅ `approve_leave.php` - Updated approval handler
- ✅ `reject_leave.php` - Updated rejection handler
- ✅ `get_leave_requests.php` - Enhanced request fetcher

## 🚀 How It Works

### For HR Officers:

1. **View Leave Requests**
   - Navigate to Leave Management
   - See all pending requests with employee details
   - View service years, leave type, and requested days

2. **Click "Approve"**
   - AI automatically evaluates the request
   - Checks eligibility based on Ethiopian law
   - If approved: Updates balance and confirms
   - If rejected: Shows detailed reason

3. **AI Decision Response**
   ```json
   {
     "decision": "Approved",
     "reason": "Annual leave request approved based on Ethiopian Labour Proclamation",
     "days_approved": 10,
     "balance_remaining": 6,
     "law_reference": "Ethiopian Labour Proclamation No. 1156/2019 - Article 74"
   }
   ```

### AI Decision Logic:

#### Annual Leave ✅
- **Check:** Service ≥ 1 year?
- **Calculate:** 16 + floor((years - 1) / 2) days
- **Verify:** Sufficient balance?
- **Action:** Approve & deduct balance

#### Bereavement Leave 😔
- **Check:** Days ≤ 3?
- **Action:** Approve (no balance deduction)

#### Sick Leave 🏥
- **Check:** Days ≤ (180 - used_this_year)?
- **Note:** Medical certificate required
- **Action:** Approve & track usage

#### Maternity Leave 🤰
- **Check:** Gender = Female?
- **Check:** Days ≤ 120?
- **Check:** Not exhausted this year?
- **Action:** Approve (no annual leave deduction)

#### Paternity Leave 👨‍👶
- **Check:** Gender = Male?
- **Check:** Days ≤ 3?
- **Action:** Approve (no balance deduction)

## 📊 Leave Balance Management

### Automatic Balance Calculation
```php
Base Annual Leave = 16 days (after 1 year)
Bonus = +1 day per 2 years

Example:
- 1 year service: 16 days
- 3 years service: 17 days (16 + 1)
- 5 years service: 18 days (16 + 2)
- 10 years service: 20 days (16 + 4)
```

### Balance Tracking
- Annual leave: Tracked in `leave_balances.annual_balance`
- Sick leave: Tracked in `leave_balances.sick_used`
- Maternity: Tracked in `leave_balances.maternity_used`
- Bereavement & Paternity: No tracking (event-based)

## 🔄 Annual Reset (HR Admin Task)

At the beginning of each year, run:
```sql
UPDATE leave_balances 
SET 
    annual_balance = 16 + FLOOR((TIMESTAMPDIFF(YEAR, (SELECT join_date FROM employees WHERE employee_id = leave_balances.employee_id), NOW()) - 1) / 2),
    sick_used = 0,
    maternity_used = 0
WHERE year = YEAR(NOW());
```

## 📖 API Response Examples

### Successful Approval
```json
{
  "success": true,
  "ai_decision": "approved",
  "message": "✅ Annual leave request approved based on Ethiopian Labour Proclamation",
  "days_approved": 10,
  "balance_remaining": 6,
  "law_reference": "Ethiopian Labour Proclamation No. 1156/2019 - Article 74",
  "payment_status": "Standard"
}
```

### Rejection (Insufficient Balance)
```json
{
  "success": false,
  "ai_decision": "rejected",
  "message": "Insufficient annual leave balance. Requested: 20 days, Available: 16 days",
  "law_reference": "Ethiopian Labour Proclamation No. 1156/2019 - Article 74"
}
```

### Rejection (Ineligible)
```json
{
  "success": false,
  "ai_decision": "rejected",
  "message": "Maternity leave is only available for female employees",
  "law_reference": "Ethiopian Labour Proclamation No. 1156/2019 - Article 88"
}
```

## ⚠️ Important Notes

1. **Sick Leave:** Medical certificate MUST be submitted within 3 days
2. **Maternity Leave:** Can be split (30 days prenatal + 90 days postnatal)
3. **Balance Reset:** Annual leave balances reset yearly (admin responsibility)
4. **Payment:** All approved leave is paid according to Ethiopian law
5. **Weekend/Holidays:** System counts calendar days (includes weekends)

## 🛠️ Troubleshooting

### Issue: "Employee not found"
**Solution:** Ensure employee_id in leave_requests matches employees table

### Issue: "Leave balance not initialized"
**Solution:** Run the initialization query in the SQL setup file

### Issue: "Unknown leave type"
**Solution:** Use exact leave types: Annual, Bereavement, Sick, Maternity, Paternity

## 📞 Support

For questions about Ethiopian labour law interpretation, refer to:
- Ethiopian Labour Proclamation No. 1156/2019
- Articles 74 (Annual), 75 (Sick), 76 (Bereavement), 88 (Maternity), 89 (Paternity)

## 🎓 Legal References

All decisions are based on:
**Ethiopian Labour Proclamation No. 1156/2019**

- Article 74: Annual Leave
- Article 75: Sick Leave  
- Article 76: Bereavement Leave
- Article 88: Maternity Leave
- Article 89: Paternity Leave

---

**Made with ❤️ for Ethiopian Healthcare Workers**
