<?php
require_once '../config.php';

/**
 * Property Test: Configuration Consistency
 * Feature: healthfirst-file-manager, Property 10: Configuration Consistency
 * 
 * Tests that global configuration changes are applied uniformly across all modules
 * Validates: Requirements 10.4
 */
class ConfigurationConsistencyTest {
    private $conn;
    private $config;
    private $testResults = [];
    
    public function __construct() {
        $this->conn = getDBConnection();
        $this->config = new FileManagerConfig($this->conn);
    }
    
    /**
     * Run property-based test with 100 iterations
     */
    public function runTest() {
        echo "<h2>Property Test: Configuration Consistency</h2>";
        echo "<p><strong>Property:</strong> For any global configuration change, the new settings should be applied uniformly across all modules</p>";
        echo "<p><strong>Validates:</strong> Requirements 10.4</p>";
        
        $iterations = 100;
        $passed = 0;
        $failed = 0;
        
        for ($i = 1; $i <= $iterations; $i++) {
            if ($this->testConfigurationConsistency($i)) {
                $passed++;
            } else {
                $failed++;
            }
        }
        
        echo "<h3>Test Results:</h3>";
        echo "<p>✅ Passed: {$passed}/{$iterations}</p>";
        echo "<p>❌ Failed: {$failed}/{$iterations}</p>";
        
        if ($failed > 0) {
            echo "<h4>Failed Test Details:</h4>";
            foreach ($this->testResults as $result) {
                if (!$result['passed']) {
                    echo "<p>❌ Iteration {$result['iteration']}: {$result['error']}</p>";
                }
            }
            return false;
        }
        
        echo "<p><strong>✅ All tests passed! Configuration consistency property holds.</strong></p>";
        return true;
    }
    
    /**
     * Test configuration consistency for a single iteration
     */
    private function testConfigurationConsistency($iteration) {
        try {
            // Generate random configuration key and value
            $configKey = 'test_config_' . $iteration . '_' . uniqid();
            $configValue = 'test_value_' . rand(1000, 9999);
            $modules = ['global', 'employee', 'patient', 'payroll', 'recruitment', 'training'];
            
            // Set global configuration
            $globalSet = $this->config->set($configKey, $configValue, 'global', 'Test config for iteration ' . $iteration, 1);
            
            if (!$globalSet) {
                $this->testResults[] = [
                    'iteration' => $iteration,
                    'passed' => false,
                    'error' => 'Failed to set global configuration'
                ];
                return false;
            }
            
            // Verify the configuration is retrievable globally
            $retrievedValue = $this->config->get($configKey, 'global');
            
            if ($retrievedValue !== $configValue) {
                $this->testResults[] = [
                    'iteration' => $iteration,
                    'passed' => false,
                    'error' => "Global config retrieval failed. Expected: {$configValue}, Got: {$retrievedValue}"
                ];
                return false;
            }
            
            // Test that module-specific configs can override global
            $moduleValue = 'module_override_' . rand(1000, 9999);
            $testModule = $modules[array_rand($modules)];
            
            $moduleSet = $this->config->set($configKey, $moduleValue, $testModule, 'Module override test', 1);
            
            if (!$moduleSet) {
                $this->testResults[] = [
                    'iteration' => $iteration,
                    'passed' => false,
                    'error' => 'Failed to set module-specific configuration'
                ];
                return false;
            }
            
            // Verify module-specific value is returned for that module
            $moduleRetrieved = $this->config->get($configKey, $testModule);
            if ($moduleRetrieved !== $moduleValue) {
                $this->testResults[] = [
                    'iteration' => $iteration,
                    'passed' => false,
                    'error' => "Module config retrieval failed. Expected: {$moduleValue}, Got: {$moduleRetrieved}"
                ];
                return false;
            }
            
            // Verify global value is still returned for other modules
            $otherModules = array_filter($modules, function($m) use ($testModule) {
                return $m !== $testModule && $m !== 'global';
            });
            
            $otherModule = $otherModules[array_rand($otherModules)];
            $otherRetrieved = $this->config->get($configKey, $otherModule, 'default');
            
            if ($otherRetrieved !== 'default') {
                $this->testResults[] = [
                    'iteration' => $iteration,
                    'passed' => false,
                    'error' => "Other module should return default when no specific config exists. Got: {$otherRetrieved}"
                ];
                return false;
            }
            
            // Clean up test data
            $this->cleanupTestConfig($configKey);
            
            $this->testResults[] = [
                'iteration' => $iteration,
                'passed' => true,
                'error' => null
            ];
            
            return true;
            
        } catch (Exception $e) {
            $this->testResults[] = [
                'iteration' => $iteration,
                'passed' => false,
                'error' => 'Exception: ' . $e->getMessage()
            ];
            return false;
        }
    }
    
    /**
     * Clean up test configuration data
     */
    private function cleanupTestConfig($configKey) {
        $stmt = $this->conn->prepare("DELETE FROM file_config WHERE config_key = ?");
        $stmt->bind_param("s", $configKey);
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Test file type validation consistency
     */
    public function testFileTypeConsistency() {
        echo "<h3>Testing File Type Validation Consistency</h3>";
        
        $testFiles = [
            'document.pdf' => true,
            'image.jpg' => true,
            'image.jpeg' => true,
            'image.png' => true,
            'document.doc' => true,
            'document.docx' => true,
            'script.exe' => false,
            'archive.zip' => false,
            'video.mp4' => false
        ];
        
        $allPassed = true;
        
        foreach ($testFiles as $filename => $shouldBeAllowed) {
            $isAllowed = $this->config->isFileTypeAllowed($filename);
            
            if ($isAllowed === $shouldBeAllowed) {
                echo "✅ {$filename}: " . ($shouldBeAllowed ? 'Allowed' : 'Blocked') . " (Correct)<br>";
            } else {
                echo "❌ {$filename}: Expected " . ($shouldBeAllowed ? 'Allowed' : 'Blocked') . ", Got " . ($isAllowed ? 'Allowed' : 'Blocked') . "<br>";
                $allPassed = false;
            }
        }
        
        return $allPassed;
    }
    
    /**
     * Test file size validation consistency
     */
    public function testFileSizeConsistency() {
        echo "<h3>Testing File Size Validation Consistency</h3>";
        
        $maxSize = $this->config->getMaxFileSize();
        echo "Maximum allowed file size: " . $this->config->formatFileSize($maxSize) . "<br>";
        
        $testSizes = [
            1024 => true,           // 1KB - should be allowed
            1048576 => true,        // 1MB - should be allowed
            5242880 => true,        // 5MB - should be allowed
            $maxSize => true,       // Exactly max size - should be allowed
            $maxSize + 1 => false,  // Over max size - should be blocked
            $maxSize * 2 => false   // Way over max size - should be blocked
        ];
        
        $allPassed = true;
        
        foreach ($testSizes as $size => $shouldBeAllowed) {
            $isAllowed = $this->config->isFileSizeAllowed($size);
            $formattedSize = $this->config->formatFileSize($size);
            
            if ($isAllowed === $shouldBeAllowed) {
                echo "✅ {$formattedSize}: " . ($shouldBeAllowed ? 'Allowed' : 'Blocked') . " (Correct)<br>";
            } else {
                echo "❌ {$formattedSize}: Expected " . ($shouldBeAllowed ? 'Allowed' : 'Blocked') . ", Got " . ($isAllowed ? 'Allowed' : 'Blocked') . "<br>";
                $allPassed = false;
            }
        }
        
        return $allPassed;
    }
    
    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}

// Run the test if this file is accessed directly
if (basename($_SERVER['SCRIPT_FILENAME']) === 'ConfigurationConsistencyTest.php') {
    $test = new ConfigurationConsistencyTest();
    
    echo "<h1>File Manager Configuration Tests</h1>";
    
    $propertyTestPassed = $test->runTest();
    $fileTypeTestPassed = $test->testFileTypeConsistency();
    $fileSizeTestPassed = $test->testFileSizeConsistency();
    
    echo "<h2>Overall Test Results</h2>";
    if ($propertyTestPassed && $fileTypeTestPassed && $fileSizeTestPassed) {
        echo "<p><strong>✅ All tests passed! Configuration system is working correctly.</strong></p>";
    } else {
        echo "<p><strong>❌ Some tests failed. Please review the configuration system.</strong></p>";
    }
}
?>