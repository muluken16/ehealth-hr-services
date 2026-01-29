<?php
require_once 'db.php';
$conn = getDBConnection();

$page_title = "Career Opportunities";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - HealthFirst</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #1a5270 0%, #2c7da0 100%);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            font-size: 1.8rem;
        }

        .header-nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 10px 20px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
        }

        .header-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h2 {
            color: #1e293b;
            font-size: 2.2rem;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #64748b;
            font-size: 1.1rem;
        }

        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }

        .job-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .job-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
        }

        .job-dept {
            font-size: 0.9rem;
            color: #64748b;
            margin-top: 5px;
        }

        .job-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-full-time {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-part-time {
            background: #fef3c7;
            color: #d97706;
        }

        .badge-contract {
            background: #e0e7ff;
            color: #4f46e5;
        }

        .badge-temporary {
            background: #f3e8ff;
            color: #9333ea;
        }

        .job-info {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            padding: 15px;
            background: #f8fafc;
            border-radius: 10px;
        }

        .job-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #475569;
        }

        .job-info-item i {
            color: #3498db;
        }

        .job-desc {
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .apply-btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
        }

        .no-jobs {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }

        .no-jobs i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .footer {
            background: #1e293b;
            color: white;
            padding: 30px;
            text-align: center;
            margin-top: 60px;
        }

        .footer a {
            color: #3498db;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <header class="header">
        <h1><i class="fas fa-heartbeat"></i> HealthFirst</h1>
        <nav class="header-nav">
            <a href="index.html"><i class="fas fa-home"></i> Home</a>
            <a href="jobs.php"><i class="fas fa-briefcase"></i> Jobs</a>
            <a href="login_ui.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        </nav>
    </header>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-briefcase"></i> Career Opportunities</h2>
            <p>Join our team and make a difference in healthcare. Browse our current openings below.</p>
        </div>

        <div class="jobs-grid" id="jobsContainer">
            <?php
            // Get all open job postings
            $result = $conn->query("SELECT * FROM job_postings WHERE status = 'open' ORDER BY posted_at DESC");

            if ($result && $result->num_rows > 0):
                while ($job = $result->fetch_assoc()):
                    $badge_class = 'badge-full-time';
                    switch ($job['employment_type']) {
                        case 'part-time':
                            $badge_class = 'badge-part-time';
                            break;
                        case 'contract':
                            $badge_class = 'badge-contract';
                            break;
                        case 'temporary':
                            $badge_class = 'badge-temporary';
                            break;
                    }
                    ?>
                    <div class="job-card">
                        <div class="job-header">
                            <div>
                                <div class="job-title"><?php echo htmlspecialchars($job['title']); ?></div>
                                <div class="job-dept"><?php echo htmlspecialchars($job['department']); ?></div>
                            </div>
                            <span class="job-badge <?php echo $badge_class; ?>">
                                <?php echo ucfirst(str_replace('-', ' ', $job['employment_type'])); ?>
                            </span>
                        </div>

                        <div class="job-info">
                            <div class="job-info-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($job['location'] ?: 'Location not specified'); ?>
                            </div>
                            <div class="job-info-item">
                                <i class="fas fa-money-bill"></i>
                                <?php echo htmlspecialchars($job['salary_range'] ?: 'Salary negotiable'); ?>
                            </div>
                            <div class="job-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                Deadline:
                                <?php echo $job['application_deadline'] ? date('M d, Y', strtotime($job['application_deadline'])) : 'Open until filled'; ?>
                            </div>
                        </div>

                        <div class="job-desc">
                            <?php
                            $desc = strip_tags($job['description']);
                            echo strlen($desc) > 150 ? substr($desc, 0, 150) . '...' : $desc;
                            ?>
                        </div>

                        <a href="login_ui.php?job_id=<?php echo $job['id']; ?>" class="apply-btn">
                            <i class="fas fa-paper-plane"></i> Apply Now
                        </a>
                    </div>
                    <?php
                endwhile;
            else:
                ?>
                <div class="no-jobs" style="grid-column: 1/-1;">
                    <i class="fas fa-briefcase"></i>
                    <h3>No Open Positions</h3>
                    <p>We currently have no job openings. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> HealthFirst Healthcare System. All rights reserved.</p>
        <p>Questions? Contact us at <a href="mailto:hr@healthfirst.com">hr@healthfirst.com</a></p>
    </footer>

    <?php $conn->close(); ?>
</body>

</html>