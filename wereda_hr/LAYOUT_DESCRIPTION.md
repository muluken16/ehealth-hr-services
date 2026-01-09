# ADD EMPLOYEE PAGE - LAYOUT DESCRIPTION

## Current Page Structure

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         BROWSER WINDOW                                  │
├────────────┬────────────────────────────────────────────────────────────┤
│            │                    NAVBAR (navbar.php)                      │
│  SIDEBAR   │  "Add New Employee"                          [User Menu]   │
│            ├────────────────────────────────────────────────────────────┤
│  • Dashboard   │                                                        │
│  • Employees   │  ADD-LAYOUT CONTAINER                                  │
│  • Leave       │  ┌──────────────────┬────────────────────────────────┐ │
│  • Reports     │  │   FORM NAV       │      FORM CONTENT              │ │
│  • Settings    │  │   (nav-card)     │      (form-content)            │ │
│                │  │                  │                                │ │
│  (280px wide)  │  │  [Photo Upload]  │  Personal Information          │ │
│  Fixed left    │  │                  │  ┌──────────────────────────┐ │ │
│                │  │  New Employee    │  │ First Name: [_______]    │ │ │
│                │  │  Create record   │  │ Last Name:  [_______]    │ │ │
│                │  │                  │  │ Email:      [_______]    │ │ │
│                │  │  ☑ Personal      │  └──────────────────────────┘ │ │
│                │  │  ☐ Education     │                                │ │
│                │  │  ☐ Employment    │  Education Information         │ │
│                │  │  ☐ Contact       │  ┌──────────────────────────┐ │ │
│                │  │  ☐ Finance       │  │ Education Level: [____]  │ │ │
│                │  │  ☐ Warranty      │  │ University:      [____]  │ │ │
│                │  │  ☐ Documents     │  └──────────────────────────┘ │ │
│                │  │                  │                                │ │
│                │  │  (280px wide)    │  [Submit Button]               │ │
│                │  │  Sticky scroll   │  (Full width)                  │ │
│                │  └──────────────────┴────────────────────────────────┘ │
│                │                                                        │
└────────────┴────────────────────────────────────────────────────────────┘

```

## Layout Breakdown:

### 1. HR Container (Full Width)
```css
.hr-container {
    display: flex;
    min-height: 100vh;
}
```

### 2. Sidebar (280px Fixed Left)
- Included from `sidebar.php`
- Fixed position
- Contains: Dashboard, Employees, Leave, Reports links

### 3. Main Content (Flex 1, margin-left: 280px)
```css
.main-content {
    flex: 1;
    margin-left: 280px;
}
```
Contains:
- Navbar at top
- Content area below

### 4. Add-Layout Grid (Inside Main Content)
```css
.add-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
}
```

**Column 1 (280px): nav-card**
- Photo upload circle
- "New Employee" title
- Form section navigation (Personal, Education, etc.)
- Sticky positioned (stays visible while scrolling)

**Column 2 (1fr - takes remaining space): form-content**
- Actual form fields
- Multiple sections (Personal, Education, Employment, etc.)
- Submit button at bottom

## Total Width Calculation:

```
Sidebar:        280px (fixed, outside main-content)
Main Content:
  ├─ Nav Card:  280px (grid column 1)
  ├─ Gap:        30px (between columns)
  └─ Form:      Rest  (grid column 2, flexible)
```

## Current Issues Fixed:

✅ Sidebar displays correctly (280px fixed left)
✅ Nav-card shows form navigation (280px sticky)
✅ Form content uses remaining space
✅ No empty gaps
✅ Proper spacing with 30px gap

## How It Should Look:

**Desktop (1920px wide):**
- Sidebar: 280px
- Main content starts at 280px
- Nav-card: 280px
- Gap: 30px
- Form: ~1330px (remaining space)

**Laptop (1366px wide):**
- Sidebar: 280px
- Main content starts at 280px
- Nav-card: 280px
- Gap: 30px
- Form: ~776px (remaining space)

## To Test:

1. Visit: http://localhost/ehealth/wereda_hr/add_employee.php
2. Press Ctrl+Shift+R (hard refresh)
3. Expected layout:
   - Left sidebar with menu
   - Form navigation card in content area
   - Form fields to the right
   - Everything aligned properly

## If Still Seeing Issues:

Check browser console (F12) for:
- CSS loading errors
- JavaScript errors
- Layout calculation issues

The layout should now be:
[Sidebar 280px] [Nav Card 280px] [Gap 30px] [Form Content Flex]
