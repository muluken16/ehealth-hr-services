<?php
/**
 * Ethiopian Leave Management AI
 * Based on Ethiopian Labour Proclamation No. 1156/2019
 * 
 * This AI automatically evaluates leave requests according to Ethiopian law
 */

class EthiopianLeaveAI
{
    private $conn;

    public function __construct($db_connection)
    {
        $this->conn = $db_connection;
    }

    /**
     * Main decision function - evaluates leave request
     * @return array Decision with reason, approved days, balance, etc.
     */
    public function evaluateLeaveRequest($leave_id)
    {
        // Fetch leave request details
        $leave = $this->getLeaveRequestDetails($leave_id);
        if (!$leave) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Leave request not found',
                'days_approved' => 0,
                'balance_remaining' => null
            ];
        }

        // Fetch employee details
        $employee = $this->getEmployeeDetails($leave['employee_id']);
        if (!$employee) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Employee not found',
                'days_approved' => 0,
                'balance_remaining' => null
            ];
        }

        // Route to appropriate leave type handler
        switch (strtolower($leave['leave_type'])) {
            case 'annual':
            case 'annual leave':
                return $this->evaluateAnnualLeave($leave, $employee);

            case 'bereavement':
            case 'bereavement leave':
                return $this->evaluateBereavementLeave($leave, $employee);

            case 'sick':
            case 'sick leave':
                return $this->evaluateSickLeave($leave, $employee);

            case 'maternity':
            case 'maternity leave':
                return $this->evaluateMaternityLeave($leave, $employee);

            case 'paternity':
            case 'paternity leave':
                return $this->evaluatePaternityLeave($leave, $employee);

            default:
                return [
                    'decision' => 'Rejected',
                    'reason' => 'Unknown leave type. Please use: Annual, Bereavement, Sick, Maternity, or Paternity',
                    'days_approved' => 0,
                    'balance_remaining' => null
                ];
        }
    }

    /**
     * ANNUAL LEAVE
     * Rules: 16 days after 1 year, +1 day per 2 years
     */
    private function evaluateAnnualLeave($leave, $employee)
    {
        // Calculate service years
        $hire_date = new DateTime($employee['join_date']);
        $today = new DateTime();
        $service_interval = $hire_date->diff($today);
        $service_years = $service_interval->y;
        $service_months = $service_interval->m;

        // Check minimum service requirement
        if ($service_years < 1) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Annual leave requires minimum 1 year of service. Employee has only ' . $service_years . ' years and ' . $service_months . ' months',
                'days_approved' => 0,
                'balance_remaining' => 0,
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 74'
            ];
        }

        // Calculate entitlement
        $base_entitlement = 16; // 16 days after first year
        $bonus_years = max(0, $service_years - 1); // Years beyond first year
        $bonus_days = floor($bonus_years / 2); // +1 day per 2 years
        $total_entitlement = $base_entitlement + $bonus_days;

        // Get current balance
        $current_balance = $this->getAnnualLeaveBalance($employee['employee_id']);
        if ($current_balance === null) {
            // Initialize balance if not exists
            $current_balance = $total_entitlement;
            $this->initializeLeaveBalance($employee['employee_id'], 'annual', $total_entitlement);
        }

        // Calculate requested days
        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        $requested_days = $start->diff($end)->days + 1;

        // Check if enough balance
        if ($requested_days > $current_balance) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Insufficient annual leave balance. Requested: ' . $requested_days . ' days, Available: ' . $current_balance . ' days',
                'days_approved' => 0,
                'balance_remaining' => $current_balance,
                'entitlement_info' => 'Total annual entitlement: ' . $total_entitlement . ' days (16 base + ' . $bonus_days . ' bonus)',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 74'
            ];
        }

        // Approve and calculate new balance
        $new_balance = $current_balance - $requested_days;

        return [
            'decision' => 'Approved',
            'reason' => 'Annual leave request approved based on Ethiopian Labour Proclamation',
            'days_approved' => $requested_days,
            'balance_remaining' => $new_balance,
            'balance_to_deduct' => $requested_days,
            'entitlement_info' => 'Service: ' . $service_years . ' years | Entitlement: ' . $total_entitlement . ' days/year',
            'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 74'
        ];
    }

    /**
     * BEREAVEMENT LEAVE
     * Rules: Max 3 working days, paid, no balance deduction
     */
    private function evaluateBereavementLeave($leave, $employee)
    {
        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        $requested_days = $start->diff($end)->days + 1;

        if ($requested_days > 3) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Bereavement leave is limited to maximum 3 working days. Requested: ' . $requested_days . ' days',
                'days_approved' => 0,
                'balance_remaining' => 'N/A',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 76'
            ];
        }

        return [
            'decision' => 'Approved',
            'reason' => 'Bereavement leave approved. Fully paid, no balance deduction',
            'days_approved' => $requested_days,
            'balance_remaining' => 'N/A',
            'balance_to_deduct' => 0,
            'payment_status' => 'Fully Paid',
            'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 76'
        ];
    }

    /**
     * SICK LEAVE
     * Rules: Max 6 months per year, medical proof required
     */
    private function evaluateSickLeave($leave, $employee)
    {
        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        $requested_days = $start->diff($end)->days + 1;

        // Check annual limit (6 months = ~180 days)
        $max_sick_days = 180;
        $used_sick_days = $this->getUsedSickLeave($employee['employee_id']);
        $remaining_sick_days = $max_sick_days - $used_sick_days;

        if ($requested_days > $remaining_sick_days) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Sick leave limit exceeded. Maximum 6 months (180 days) per year. Used: ' . $used_sick_days . ' days, Remaining: ' . $remaining_sick_days . ' days',
                'days_approved' => 0,
                'balance_remaining' => $remaining_sick_days,
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 75'
            ];
        }

        // Note: Medical certificate verification should be done separately
        $new_remaining = $remaining_sick_days - $requested_days;

        return [
            'decision' => 'Approved',
            'reason' => 'Sick leave approved. Medical proof MUST be submitted within 3 days',
            'days_approved' => $requested_days,
            'balance_remaining' => $new_remaining,
            'balance_to_deduct' => $requested_days,
            'note' => '⚠️ IMPORTANT: Medical certificate required for sick leave',
            'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 75'
        ];
    }

    /**
     * MATERNITY LEAVE
     * Rules: Female only, 120 days (30 prenatal + 90 postnatal), fully paid
     */
    private function evaluateMaternityLeave($leave, $employee)
    {
        // Gender check
        if (strtolower($employee['gender']) !== 'female') {
            return [
                'decision' => 'Rejected',
                'reason' => 'Maternity leave is only available for female employees',
                'days_approved' => 0,
                'balance_remaining' => 'N/A',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 88'
            ];
        }

        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        $requested_days = $start->diff($end)->days + 1;

        // Maximum 120 days (can be distributed: 30 prenatal + 90 postnatal)
        if ($requested_days > 120) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Maternity leave exceeds maximum 120 days (30 prenatal + 90 postnatal). Requested: ' . $requested_days . ' days',
                'days_approved' => 0,
                'balance_remaining' => 'N/A',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 88'
            ];
        }

        // Check if already used maternity leave this year
        $used_maternity = $this->getUsedMaternityLeave($employee['employee_id']);
        $remaining = 120 - $used_maternity;

        if ($requested_days > $remaining) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Maternity leave balance exhausted. Used: ' . $used_maternity . ' days, Remaining: ' . $remaining . ' days',
                'days_approved' => 0,
                'balance_remaining' => $remaining,
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 88'
            ];
        }

        return [
            'decision' => 'Approved',
            'reason' => 'Maternity leave approved. Fully paid, no balance deduction from annual leave',
            'days_approved' => $requested_days,
            'balance_remaining' => $remaining - $requested_days,
            'balance_to_deduct' => 0, // No deduction from annual leave
            'payment_status' => 'Fully Paid (100%)',
            'entitlement_info' => 'Total: 120 days (30 prenatal + 90 postnatal)',
            'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 88'
        ];
    }

    /**
     * PATERNITY LEAVE
     * Rules: Male only, 3 working days, fully paid
     */
    private function evaluatePaternityLeave($leave, $employee)
    {
        // Gender check
        if (strtolower($employee['gender']) !== 'male') {
            return [
                'decision' => 'Rejected',
                'reason' => 'Paternity leave is only available for male employees',
                'days_approved' => 0,
                'balance_remaining' => 'N/A',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 89'
            ];
        }

        $start = new DateTime($leave['start_date']);
        $end = new DateTime($leave['end_date']);
        $requested_days = $start->diff($end)->days + 1;

        if ($requested_days > 3) {
            return [
                'decision' => 'Rejected',
                'reason' => 'Paternity leave is limited to 3 working days per birth. Requested: ' . $requested_days . ' days',
                'days_approved' => 0,
                'balance_remaining' => 'N/A',
                'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 89'
            ];
        }

        return [
            'decision' => 'Approved',
            'reason' => 'Paternity leave approved. Fully paid, no balance deduction',
            'days_approved' => $requested_days,
            'balance_remaining' => 'N/A',
            'balance_to_deduct' => 0,
            'payment_status' => 'Fully Paid (100%)',
            'law_reference' => 'Ethiopian Labour Proclamation No. 1156/2019 - Article 89'
        ];
    }

    // =================== HELPER FUNCTIONS ===================

    private function getLeaveRequestDetails($leave_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM leave_requests WHERE id = ?");
        $stmt->bind_param("i", $leave_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getEmployeeDetails($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    private function getAnnualLeaveBalance($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT annual_balance FROM leave_balances WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? $result['annual_balance'] : null;
    }

    private function initializeLeaveBalance($employee_id, $type, $balance)
    {
        $stmt = $this->conn->prepare("INSERT INTO leave_balances (employee_id, annual_balance, year) VALUES (?, ?, YEAR(NOW())) ON DUPLICATE KEY UPDATE annual_balance = ?");
        $stmt->bind_param("sii", $employee_id, $balance, $balance);
        $stmt->execute();
    }

    private function getUsedSickLeave($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT SUM(DATEDIFF(end_date, start_date) + 1) as used FROM leave_requests WHERE employee_id = ? AND leave_type LIKE '%sick%' AND status = 'approved' AND YEAR(start_date) = YEAR(NOW())");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['used'] ?? 0;
    }

    private function getUsedMaternityLeave($employee_id)
    {
        $stmt = $this->conn->prepare("SELECT SUM(DATEDIFF(end_date, start_date) + 1) as used FROM leave_requests WHERE employee_id = ? AND leave_type LIKE '%maternity%' AND status = 'approved' AND YEAR(start_date) = YEAR(NOW())");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['used'] ?? 0;
    }
}
