# Employee Bulk Import Feature

## Overview
This feature allows HR administrators to import multiple employees at once using CSV files, significantly reducing manual data entry time.

## Files Created

### Wereda HR Module
- `wereda_hr/import_employees.php` - Backend handler for processing imports
- `wereda_hr/download_template.php` - Generates sample CSV template
- Updated `wereda_hr/wereda_hr_employees.php` - Added import button and modal

### Kebele HR Module
- `kebele_hr/import_employees.php` - Backend handler for processing imports
- `kebele_hr/download_template.php` - Generates sample CSV template
- Updated `kebele_hr/hr-employees.php` - Added import button and modal

## How to Use

### Step 1: Download Template
1. Navigate to the employee management page
2. Click the **"Import"** button
3. In the modal, click **"Download CSV Template"**
4. Open the downloaded `employee_import_template.csv` file in Excel or any spreadsheet application

### Step 2: Fill in Employee Data
The template contains the following columns (in order):

| Column Name | Required | Description | Example |
|------------|----------|-------------|---------|
| First Name | ✅ Yes | Employee's first name | Abebe |
| Middle Name | ❌ No | Employee's middle name | Kebede |
| Last Name | ✅ Yes | Employee's last name | Tesfaye |
| Gender | ✅ Yes | male or female | male |
| Email | ✅ Yes | Unique email address | abebe.tesfaye@example.com |
| Phone Number | ❌ No | Contact number with country code | +251911234567 |
| Position | ❌ No | Job title | Senior Nurse |
| Department Assigned | ❌ No | Department name | Outpatient Department (OPD) |
| Employment Type | ❌ No | Contract type | full-time, part-time, contract |
| Salary | ❌ No | Monthly salary amount | 15000 |
| Join Date | ❌ No | Start date (YYYY-MM-DD) | 2024-01-15 |
| Status | ❌ No | Employment status | active, on-leave, inactive |
| Working Kebele/Woreda | ❌ No | Location assignment | Kebele 01 or Woreda 01 |
| Date of Birth | ❌ No | Birth date (YYYY-MM-DD) | 1990-05-20 |
| Address | ❌ No | Physical address | Addis Ababa, Bole |

**Required Fields:** First Name, Last Name, Email

### Step 3: Save as CSV
1. After filling in your data in Excel, go to **File > Save As**
2. Choose **"CSV (Comma delimited) (*.csv)"** as the file format
3. Save the file

### Step 4: Upload and Import
1. Click the **"Import"** button on the employee management page
2. Click the upload area or drag and drop your CSV file
3. Review the file information displayed
4. Click **"Upload & Import"**
5. Wait for the import to complete

## Import Results

### Success
- Green success message will appear
- Shows number of employees imported
- Page will automatically refresh after 3 seconds

### Partial Success with Errors
- Shows number of employees successfully imported
- Lists specific errors for failed rows
- Successfully imported employees will be added to the database

### Complete Failure
- Red error message will appear
- Shows the reason for failure
- No employees will be added to the database

## Common Errors and Solutions

### "Missing required fields"
**Problem:** Row is missing First Name, Last Name, or Email
**Solution:** Ensure all required fields are filled in

### "Email already exists"
**Problem:** An employee with that email is already in the database
**Solution:** Use a unique email address or update the existing employee

### "Invalid file type"
**Problem:** File is not in CSV format
**Solution:** Save your Excel file as CSV format

### "No file uploaded"
**Problem:** File upload failed
**Solution:** Check file size (should be < 5MB) and try again

## Best Practices

1. **Test with small batches first:** Import 5-10 employees initially to verify your data format is correct

2. **Keep a backup:** Save a copy of your original Excel file before converting to CSV

3. **Validate emails:** Ensure all email addresses are unique and properly formatted

4. **Use consistent formats:**
   - Dates: YYYY-MM-DD (e.g., 2024-01-15)
   - Phone: Include country code (e.g., +251911234567)
   - Gender: lowercase (male or female)
   - Status: lowercase (active, on-leave, inactive)

5. **Review sample data:** The template includes 3 sample rows - use these as examples

6. **Remove sample data:** Delete the sample rows before adding your real employee data

## Technical Details

### Supported File Formats
- **CSV** (Comma-Separated Values) - Primary format
- **Excel files must be saved as CSV** before importing

### File Size Limit
- Maximum file size: Depends on PHP `upload_max_filesize` setting (default: 2MB)
- Recommended: Keep imports under 100 employees per file

### Character Encoding
- UTF-8 encoding (automatically added for Excel compatibility)
- Supports Ethiopian names and special characters

### Auto-generated Fields
- **Employee ID:** Automatically generated (format: WRD-YYYY-XXXX or KBL-YYYY-XXXX)
- **Created At:** Timestamp of import
- **Created By:** Session user performing the import

### Session-specific Data
- **Wereda HR:** Automatically assigns employees to the logged-in user's woreda
- **Kebele HR:** Automatically assigns employees to the logged-in user's kebele

## Security Notes

- Only authenticated HR administrators can import employees
- Email addresses must be unique across the entire system
- All data is validated before insertion
- SQL injection protection via prepared statements
- File type validation to prevent malicious uploads

## Troubleshooting

### Import button not working
- Check browser console for JavaScript errors
- Ensure you're logged in with proper HR credentials
- Clear browser cache and refresh the page

### Modal not closing
- Click the X button in the top-right corner
- Click outside the modal box
- Press the Cancel button

### Server errors
- Check PHP error logs
- Verify database connection is working
- Ensure `uploads/employees/` directory exists and is writable

## Support

For additional help:
1. Check the sample template for correct format examples
2. Review error messages carefully - they indicate specific issues
3. Contact your system administrator if problems persist

## Future Enhancements

Potential improvements for future versions:
- Excel file support (requires PhpSpreadsheet library)
- Bulk update of existing employees
- Import preview before committing
- Duplicate detection and merging
- Custom field mapping
- Import history and logs
- Rollback functionality
