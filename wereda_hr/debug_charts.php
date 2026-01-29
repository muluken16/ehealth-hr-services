<!DOCTYPE html>
<html>
<head>
    <title>Chart Debug - Wereda HR</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        .chart-container { width: 400px; height: 300px; margin: 10px; display: inline-block; }
        .error { color: red; }
        .success { color: green; }
        .info { color: blue; }
    </style>
</head>
<body>
    <h1>🔍 Chart Debug Tool - Wereda HR Dashboard</h1>
    
    <div class="debug-section">
        <h2>1. Chart.js Library Test</h2>
        <div id="chartjs-test"></div>
    </div>
    
    <div class="debug-section">
        <h2>2. API Endpoints Test</h2>
        <div id="api-test"></div>
    </div>
    
    <div class="debug-section">
        <h2>3. Sample Chart Test</h2>
        <div class="chart-container">
            <canvas id="testChart"></canvas>
        </div>
    </div>
    
    <div class="debug-section">
        <h2>4. Gender Chart Test</h2>
        <div class="chart-container">
            <canvas id="genderChart"></canvas>
        </div>
        <div id="gender-debug"></div>
    </div>
    
    <div class="debug-section">
        <h2>5. Console Logs</h2>
        <div id="console-logs" style="background: #f5f5f5; padding: 10px; height: 200px; overflow-y: scroll;"></div>
    </div>

    <script>
        // Capture console logs
        const originalLog = console.log;
        const originalError = console.error;
        const logContainer = document.getElementById('console-logs');
        
        function addLog(message, type = 'log') {
            const div = document.createElement('div');
            div.className = type;
            div.textContent = new Date().toLocaleTimeString() + ' - ' + message;
            logContainer.appendChild(div);
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            addLog(args.join(' '), 'info');
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            addLog(args.join(' '), 'error');
        };

        // Test 1: Chart.js Library
        function testChartJS() {
            const testDiv = document.getElementById('chartjs-test');
            if (typeof Chart !== 'undefined') {
                testDiv.innerHTML = '<span class="success">✅ Chart.js loaded successfully</span><br>Version: ' + Chart.version;
                console.log('Chart.js version:', Chart.version);
            } else {
                testDiv.innerHTML = '<span class="error">❌ Chart.js not loaded</span>';
                console.error('Chart.js not available');
            }
        }

        // Test 2: API Endpoints
        async function testAPIs() {
            const testDiv = document.getElementById('api-test');
            const endpoints = [
                'get_gender_stats.php',
                'get_academic_stats.php', 
                'get_department_stats.php',
                'get_job_level_stats.php',
                'get_status_stats.php'
            ];
            
            let results = '';
            
            for (const endpoint of endpoints) {
                try {
                    console.log('Testing endpoint:', endpoint);
                    const response = await fetch(endpoint);
                    const data = await response.json();
                    
                    if (data.success) {
                        results += `<span class="success">✅ ${endpoint}</span> - Data: ${JSON.stringify(data.data)}<br>`;
                        console.log(endpoint + ' success:', data);
                    } else {
                        results += `<span class="error">❌ ${endpoint}</span> - Error: ${data.message}<br>`;
                        console.error(endpoint + ' failed:', data.message);
                    }
                } catch (error) {
                    results += `<span class="error">❌ ${endpoint}</span> - Network Error: ${error.message}<br>`;
                    console.error(endpoint + ' network error:', error);
                }
            }
            
            testDiv.innerHTML = results;
        }

        // Test 3: Simple Chart
        function testSimpleChart() {
            try {
                const ctx = document.getElementById('testChart');
                if (!ctx) {
                    console.error('Test chart canvas not found');
                    return;
                }
                
                new Chart(ctx, {
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
                            title: {
                                display: true,
                                text: 'Simple Test Chart'
                            }
                        }
                    }
                });
                console.log('Simple chart created successfully');
            } catch (error) {
                console.error('Simple chart error:', error);
            }
        }

        // Test 4: Gender Chart (like dashboard)
        function testGenderChart() {
            fetch('get_gender_stats.php')
                .then(response => {
                    console.log('Gender stats response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Gender stats data:', data);
                    
                    const debugDiv = document.getElementById('gender-debug');
                    debugDiv.innerHTML = `<strong>API Response:</strong><br><pre>${JSON.stringify(data, null, 2)}</pre>`;
                    
                    if (!data.success || !data.data) {
                        console.error('Gender stats failed:', data.message || 'No data');
                        return;
                    }
                    
                    const ctx = document.getElementById('genderChart');
                    if (!ctx) {
                        console.error('Gender chart canvas not found');
                        return;
                    }
                    
                    new Chart(ctx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(data.data),
                            datasets: [{
                                data: Object.values(data.data),
                                backgroundColor: ['#4cb5ae', '#ff7e5f', '#ffc107', '#17a2b8'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: {
                                title: { 
                                    display: true, 
                                    text: 'Gender Distribution (Debug)', 
                                    font: { size: 16, weight: 'bold' } 
                                },
                                legend: { 
                                    position: 'bottom', 
                                    labels: { usePointStyle: true, padding: 20 } 
                                }
                            }
                        }
                    });
                    console.log('Gender chart created successfully');
                })
                .catch(error => {
                    console.error('Error loading gender chart:', error);
                    document.getElementById('gender-debug').innerHTML = `<span class="error">Error: ${error.message}</span>`;
                });
        }

        // Test 5: Check CSS
        function testCSS() {
            const style = getComputedStyle(document.querySelector('.chart-container'));
            console.log('Chart container styles:', {
                width: style.width,
                height: style.height,
                display: style.display
            });
        }

        // Run all tests
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== Starting Chart Debug Tests ===');
            
            // Test 1: Chart.js
            testChartJS();
            
            // Test 2: APIs (with delay)
            setTimeout(testAPIs, 500);
            
            // Test 3: Simple chart (with delay)
            setTimeout(testSimpleChart, 1000);
            
            // Test 4: Gender chart (with delay)
            setTimeout(testGenderChart, 1500);
            
            // Test 5: CSS
            setTimeout(testCSS, 2000);
            
            console.log('=== All tests initiated ===');
        });
    </script>
</body>
</html>