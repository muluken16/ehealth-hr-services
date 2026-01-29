# 🇪🇹 Ethiopian Leave Management AI - Quick Reference

## ✅ APPROVED Leave Types

### 📅 ANNUAL LEAVE
**Eligibility:** ≥ 1 year service  
**Entitlement:** 16 days + 1 day per 2 years  
**Payment:** ✅ Paid  
**Balance:** ⚠️ Deducted from annual leave  
**Law:** Article 74

**Examples:**
- 1 year → 16 days
- 5 years → 18 days  
- 10 years → 20 days

---

### 😔 BEREAVEMENT LEAVE
**Eligibility:** All employees  
**Entitlement:** Max 3 working days  
**Payment:** ✅ Paid  
**Balance:** ✅ No deduction  
**Law:** Article 76

---

### 🏥 SICK LEAVE
**Eligibility:** All employees  
**Entitlement:** Max 6 months/year (180 days)  
**Payment:** ✅ Paid  
**Balance:** ⚠️ Tracked separately  
**Required:** 📋 Medical certificate  
**Law:** Article 75

---

### 🤰 MATERNITY LEAVE
**Eligibility:** Female employees only  
**Entitlement:** 120 days (30 prenatal + 90 postnatal)  
**Payment:** ✅ Fully paid  
**Balance:** ✅ No deduction  
**Law:** Article 88

---

### 👨‍👶 PATERNITY LEAVE
**Eligibility:** Male employees only  
**Entitlement:** 3 working days per birth  
**Payment:** ✅ Fully paid  
**Balance:** ✅ No deduction  
**Law:** Article 89

---

## ❌ REJECTION Reasons

### Common Rejections:

| Reason | Solution |
|--------|----------|
| Service < 1 year (Annual) | Wait until 1 year completed |
| Insufficient balance | Request fewer days or wait for reset |
| Wrong gender (Maternity/Paternity) | Use appropriate leave type |
| Exceeds maximum (Sick/Maternity) | Check used days this year |
| Days > limit (Bereavement/Paternity) | Reduce request to max allowed |

---

## 🔄 AI Decision Flow

```
LEAVE REQUEST SUBMITTED
         ↓
    AI ANALYZES:
    ✓ Employee details
    ✓ Service years  
    ✓ Gender
    ✓ Leave type
    ✓ Current balance
    ✓ Days requested
         ↓
   CHECKS ETHIOPIAN LAW
   (Proclamation 1156/2019)
         ↓
    ┌─────────────┐
    │  ELIGIBLE?  │
    └─────────────┘
        ↙     ↘
      YES      NO
       ↓        ↓
   APPROVE   REJECT
       ↓        ↓
   Update     Show
   Balance   Reason
       ↓        ↓
   Notify    Notify
     HR        HR
```

---

## 📊 Balance Calculation Formula

```
ANNUAL LEAVE ENTITLEMENT:

Base = 16 days (after 1 year)
Bonus = FLOOR((service_years - 1) / 2)

Total = Base + Bonus

Example:
- Employee joined: 2020-01-01
- Current date: 2026-01-29
- Service: 6 years
- Calculation: 16 + FLOOR((6-1)/2) = 16 + 2 = 18 days
```

---

## 🎯 Quick Decision Table

| Leave Type | Max Days | Gender | Service Req | Pay | Deduct Balance |
|-----------|----------|--------|-------------|-----|----------------|
| Annual | 16+ | Any | ≥1 year | ✅ | ✅ |
| Bereavement | 3 | Any | None | ✅ | ❌ |
| Sick | 180/year | Any | None | ✅ | 🟨 Tracked |
| Maternity | 120 | Female | None | ✅ | ❌ |
| Paternity | 3 | Male | None | ✅ | ❌ |

---

## 💡 HR Tips

1. **Check Balance First** - View employee's current annual leave balance before approving
2. **Medical Proof** - Always require medical certificate for sick leave
3. **Annual Reset** - Reset balances at year start (automated with script)
4. **Gender Verification** - System auto-checks for maternity/paternity
5. **Service Check** - AI automatically calculates service years

---

## 📞 Emergency Override

If AI rejects but you need to approve:
1. Note the AI decision
2. Document override reason
3. Use manual approval (if implemented)
4. Inform payroll of exception

---

**🇪🇹 Based on Ethiopian Labour Proclamation No. 1156/2019**  
**All decisions are legally compliant** ✅
