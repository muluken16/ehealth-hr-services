-- Ethiopian Leave Management AI - Database Schema Updates
-- Run this script to add AI decision tracking and leave balance management

-- Add AI decision columns to leave_requests table
ALTER TABLE `leave_requests` 
ADD COLUMN IF NOT EXISTS `ai_decision` TEXT DEFAULT NULL COMMENT 'JSON of AI decision details',
ADD COLUMN IF NOT EXISTS `ai_reason` VARCHAR(500) DEFAULT NULL COMMENT 'AI decision reason',
ADD COLUMN IF NOT EXISTS `rejection_reason` VARCHAR(500) DEFAULT NULL COMMENT 'Manual rejection reason';

-- Create leave_balances table for tracking employee leave balances
CREATE TABLE IF NOT EXISTS `leave_balances` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `employee_id` VARCHAR(50) NOT NULL,
    `year` INT NOT NULL,
    `annual_balance` INT DEFAULT 0 COMMENT 'Remaining annual leave days',
    `sick_used` INT DEFAULT 0 COMMENT 'Sick leave days used this year',
    `maternity_used` INT DEFAULT 0 COMMENT 'Maternity leave days used this year',
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_employee_year` (`employee_id`, `year`),
    FOREIGN KEY (`employee_id`) REFERENCES `employees`(`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Tracks employee leave balances per year';

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS `idx_employee_year` ON `leave_balances` (`employee_id`, `year`);
CREATE INDEX IF NOT EXISTS `idx_leave_status` ON `leave_requests` (`status`, `created_at`);

-- Insert initial balances for existing employees (one-time migration)
INSERT IGNORE INTO `leave_balances` (`employee_id`, `year`, `annual_balance`)
SELECT 
    e.employee_id,
    YEAR(NOW()) as year,
    CASE 
        WHEN TIMESTAMPDIFF(YEAR, e.join_date, NOW()) >= 1 THEN 
            16 + FLOOR((TIMESTAMPDIFF(YEAR, e.join_date, NOW()) - 1) / 2)
        ELSE 0
    END as annual_balance
FROM employees e
WHERE e.status = 'active';

-- Update statement for HR to manually reset leave balances annually (run at year start)
-- UPDATE leave_balances SET annual_balance = 16 + FLOOR((TIMESTAMPDIFF(YEAR, (SELECT join_date FROM employees WHERE employee_id = leave_balances.employee_id), NOW()) - 1) / 2), sick_used = 0, maternity_used = 0 WHERE year = YEAR(NOW());

SELECT 'Ethiopian Leave Management AI database schema updated successfully!' as message;
