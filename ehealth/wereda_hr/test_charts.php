<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart Test - Wereda HR</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .chart-container { width: 400px; height: 300px; margin: 20px; display: inline-block; border: 1px solid #ddd; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>🧪 Chart Test Page - Wereda HR</h1>
    
    <div id="status"></div>
    
    <div class="chart-container">
        <canvas id="testChart"></canvas>
    </div>
    
    <div class="chart-container">
        <canvas id="genderChart"></canvas>
    </div>

    <script>
        const statusDiv = document.getElementById('status');
        
        function addStatus(message, type = 'success') {
            const div = document.createElement('div');
            div.className = `status ${type}`;
            div.textContent = message;
            statusDiv.appendChild(div);
        }
        
        // Test Chart.js availability
        if (typeof Chart !== 'undefined') {
            addStatus('✅ Chart.js loaded successfully (Version: ' + Chart.version + ')');
            
            // Test simple chart
            try {
                const ctx1 = document.getElementById('testChart').getContext('2d');
                new Chart(ctx1, {
                    type: 'bar',
                    data: {
                        labels: ['Test 1', 'Test 2', 'Test 3'],
                        datasets: [{
                            label: 'Sample Data',
                            data: [12, 19, 3],
                            backgroundColor: ['#ff6384', '#36a2eb', '#cc65fe']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: { display: true, text: 'Simple Test Chart' }
                        }
                    }
                });
                addStatus('✅ Simple chart created successfully');
            } catch (error) {
                addStatus('❌ Simple chart error: ' + error.message, 'error');
            }
            
            // Test gender chart with API
            fetch('get_gender_stats.php')
                .then(response => response.json())
                .then(data => {
                    addStatus('✅ Gender API response: ' + JSON.stringify(data));
                    
                    if (data.success && data.data) {
                        const ctx2 = document.getElementById('genderChart').getContext('2d');
                        new Chart(ctx2, {
                            type: 'doughnut',
                            data: {
                                labels: Object.keys(data.data),
                                datasets: [{
                                    data: Object.values(data.data),
                                    backgroundColor: ['#4cb5ae', '#ff7e5f', '#ffc107']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: { display: true, text: 'Gender Distribution (API Data)' }
                                }
                            }
                        });
                        addStatus('✅ Gender chart with API data created successfully');
                    } else {
                        addStatus('❌ Gender API returned no data', 'error');
                    }
                })
                .catch(error => {
                    addStatus('❌ Gender API error: ' + error.message, 'error');
                });
                
        } else {
            addStatus('❌ Chart.js not loaded', 'error');
        }
    </script>
    
    <br><br>
    <a href="wereda_hr_dashboard.php">← Back to Dashboard</a>
</body>
</html>