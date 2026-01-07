# Design Document: HealthFirst File Manager

## Overview

The HealthFirst File Manager is a web-based document management system built as a PHP module integrated into the existing HealthFirst eHealth system. It provides secure, role-based file management with geographic access controls, comprehensive audit logging, and collaborative features for healthcare and HR document management across Zone, Wereda, and Kebele administrative levels.

## Architecture

### System Architecture

```mermaid
graph TB
    A[Web Browser] --> B[File Manager UI]
    B --> C[Authentication Layer]
    C --> D[Permission Controller]
    D --> E[File Operations Controller]
    E --> F[Database Layer]
    E --> G[File Storage Layer]
    F --> H[(MySQL Database)]
    G --> I[/files/ Directory Structure]
    
    J[Audit Logger] --> F
    K[Notification System] --> F
    L[Report Generator] --> F
    
    E --> J
    E --> K
    D --> J
```

### Database Schema

The system extends the existing HealthFirst database with new tables:

```sql
-- File metadata and management
files (
    file_id, entity_type, entity_id, category, 
    original_name, system_name, file_path, file_size, 
    mime_type, uploaded_by, upload_date, status,
    geographic_scope, zone_id, wereda_id, kebele_id
)

-- File sharing and access control
file_shares (
    share_id, file_id, shared_by, shared_with, 
    permission_type, expiry_date, created_date, status
)

-- Comprehensive audit logging
file_audit_log (
    log_id, file_id, user_id, action_type, 
    timestamp, ip_address, user_agent, details
)

-- System configuration
file_config (
    config_key, config_value, module, updated_by, updated_date
)
```

## Components and Interfaces

### 1. File Upload Component
- **Purpose**: Handle secure file uploads with validation
- **Interface**: `FileUploader::upload($entityType, $entityId, $category, $file)`
- **Validation**: File type, size, permissions, naming conventions
- **Storage**: Structured directory creation and metadata recording

### 2. Access Control Manager
- **Purpose**: Enforce role-based and geographic permissions
- **Interface**: `AccessController::checkPermission($userId, $fileId, $action)`
- **Logic**: Role hierarchy, geographic scope validation, special permissions
- **Integration**: Session management, user roles from existing system

### 3. File Operations Handler
- **Purpose**: Core CRUD operations for files
- **Interfaces**:
  - `FileManager::viewFile($fileId, $userId)`
  - `FileManager::downloadFile($fileId, $userId)`
  - `FileManager::renameFile($fileId, $newName, $userId)`
  - `FileManager::deleteFile($fileId, $userId)`

### 4. Sharing and Collaboration System
- **Purpose**: Controlled file sharing between users
- **Interfaces**:
  - `ShareManager::shareFile($fileId, $recipients, $permissions, $expiry)`
  - `ShareManager::sendFile($fileId, $recipients, $message, $permissions)`
  - `ShareManager::revokeAccess($shareId, $userId)`

### 5. Search and Filter Engine
- **Purpose**: Efficient file discovery within user permissions
- **Interface**: `SearchEngine::search($query, $filters, $userId)`
- **Features**: Full-text search, metadata filtering, permission-aware results

### 6. Audit and Logging System
- **Purpose**: Comprehensive activity tracking for compliance
- **Interface**: `AuditLogger::log($action, $fileId, $userId, $details)`
- **Coverage**: All file operations, access attempts, configuration changes

### 7. Reporting Module
- **Purpose**: Analytics and compliance reporting
- **Interfaces**:
  - `ReportGenerator::generateUsageReport($dateRange, $scope)`
  - `ReportGenerator::generateAuditReport($criteria)`
  - `ReportGenerator::generateStorageReport($location)`

## Data Models

### File Entity Model
```php
class FileEntity {
    public $fileId;
    public $entityType;     // 'employee', 'patient', 'payroll', etc.
    public $entityId;       // Reference to specific record
    public $category;       // 'personal', 'medical', 'banking', etc.
    public $originalName;
    public $systemName;     // Generated unique name
    public $filePath;
    public $fileSize;
    public $mimeType;
    public $uploadedBy;
    public $uploadDate;
    public $status;         // 'active', 'deleted', 'archived'
    public $geographicScope; // Zone/Wereda/Kebele access level
}
```

### User Permission Model
```php
class UserPermission {
    public $userId;
    public $role;           // 'admin', 'zone_officer', 'wereda_officer', etc.
    public $geographicScope;
    public $zoneId;
    public $weredaId;
    public $kebeleId;
    public $permissions;    // Array of allowed actions
}
```

### File Share Model
```php
class FileShare {
    public $shareId;
    public $fileId;
    public $sharedBy;
    public $sharedWith;     // User ID or role
    public $permissionType; // 'view', 'download'
    public $expiryDate;
    public $status;         // 'active', 'expired', 'revoked'
}
```

## Directory Structure

### Physical File Organization
```
/files/
├── employees/
│   ├── HF-2024-0001/
│   │   ├── personal/
│   │   ├── banking/
│   │   ├── education/
│   │   ├── criminal/
│   │   ├── warranty/
│   │   └── leave/
├── patients/
│   ├── PT-000123/
│   │   ├── medical/
│   │   ├── insurance/
│   │   └── emergency/
├── payroll/
├── recruitment/
├── training/
├── emergency/
├── quality/
└── system/
```

### File Naming Convention
- **System Name Format**: `{ENTITY_TYPE}-{ENTITY_ID}_{CATEGORY}_{INDEX}_{TIMESTAMP}.{EXT}`
- **Example**: `EMP-HF-2024-0001_personal_0_1704067200.pdf`
- **Benefits**: Unique identification, collision prevention, audit trail

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system—essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: File Upload Validation
*For any* file upload attempt, the system should accept the file if and only if it meets all validation criteria (allowed file type, size limit, user permissions, and valid entity/category combination)
**Validates: Requirements 1.2, 1.3**

### Property 2: Folder Structure Consistency
*For any* entity and module combination, the system should create and maintain consistent folder structures with appropriate category subfolders
**Validates: Requirements 6.1, 6.2, 6.5**

### Property 3: Access Control Enforcement
*For any* user and file combination, access should be granted if and only if the user has appropriate role permissions and geographic scope authorization
**Validates: Requirements 2.1, 2.2, 2.5**

### Property 4: Admin Override Access
*For any* System Admin user, access should be granted to all files regardless of geographic scope restrictions
**Validates: Requirements 2.3**

### Property 5: Audit Log Completeness
*For any* file operation (upload, download, view, rename, delete, share, send), a complete audit log entry should be created with all required metadata
**Validates: Requirements 1.5, 3.4, 4.4, 8.1, 8.2**

### Property 6: Soft Delete Preservation
*For any* file deletion operation, the file should be marked as inactive rather than physically removed, preserving audit trail and recovery capability
**Validates: Requirements 4.3**

### Property 7: Permission Inheritance
*For any* subfolder created within an entity folder, it should inherit the same access permissions as its parent folder
**Validates: Requirements 6.3**

### Property 8: Search Scope Restriction
*For any* search operation, results should only include files that the requesting user has permission to access within their geographic scope
**Validates: Requirements 7.1, 7.5**

### Property 9: Share Access Control
*For any* file sharing operation, recipients should only be selectable from users within appropriate role and geographic permission boundaries
**Validates: Requirements 5.1**

### Property 10: Configuration Consistency
*For any* global configuration change, the new settings should be applied uniformly across all modules and entity types
**Validates: Requirements 10.4**

### Property 11: File Naming Uniqueness
*For any* folder, no two files should have identical display names, preventing naming conflicts and ensuring clear identification
**Validates: Requirements 4.5**

### Property 12: Unauthorized Access Logging
*For any* unauthorized access attempt, the system should both deny access and create a detailed audit log entry
**Validates: Requirements 2.4, 8.4**

## Error Handling

### File Upload Errors
- **Invalid File Type**: Return specific error message listing allowed formats
- **File Size Exceeded**: Display current file size and maximum allowed size
- **Permission Denied**: Clear message about insufficient access rights
- **Storage Quota Exceeded**: Information about current usage and limits
- **Duplicate File Name**: Suggest alternative names or auto-increment

### Access Control Errors
- **Geographic Scope Violation**: Explain geographic access restrictions
- **Role Permission Insufficient**: Specify required role level
- **File Not Found**: Generic message to prevent information disclosure
- **Session Expired**: Redirect to login with return URL preservation

### System Errors
- **Database Connection**: Graceful degradation with retry mechanism
- **File System Errors**: Detailed logging with user-friendly messages
- **Configuration Errors**: Admin notification with fallback to defaults
- **Network Timeouts**: Retry logic with exponential backoff

### Error Logging Strategy
- All errors logged with severity levels (INFO, WARN, ERROR, CRITICAL)
- User-facing messages sanitized to prevent information disclosure
- Technical details logged separately for debugging
- Critical errors trigger immediate admin notifications

## Testing Strategy

### Unit Testing Approach
- **Component Isolation**: Test each component independently with mocked dependencies
- **Edge Case Coverage**: Focus on boundary conditions, invalid inputs, and error scenarios
- **Permission Matrix Testing**: Verify all role/action combinations work correctly
- **Database Integration**: Test data persistence and retrieval operations

### Property-Based Testing Configuration
- **Testing Framework**: PHPUnit with Faker library for data generation
- **Test Iterations**: Minimum 100 iterations per property test
- **Data Generation**: Smart generators that create realistic file structures, user roles, and geographic hierarchies
- **Property Test Tags**: Each test tagged with format: **Feature: healthfirst-file-manager, Property {number}: {property_text}**

### Integration Testing
- **End-to-End Workflows**: Complete user journeys from login to file operations
- **Cross-Module Integration**: Verify integration with existing HealthFirst modules
- **Performance Testing**: File upload/download performance under load
- **Security Testing**: Penetration testing for access control bypasses

### Test Data Management
- **Synthetic Data**: Generated test files and user accounts for safe testing
- **Geographic Hierarchies**: Test data covering Zone/Wereda/Kebele structures
- **Role Combinations**: Comprehensive coverage of all user role types
- **File Type Variety**: Test files in all supported formats and sizes

### Continuous Testing
- **Automated Test Suite**: Run on every code change
- **Performance Benchmarks**: Monitor file operation response times
- **Security Scans**: Regular vulnerability assessments
- **Compliance Validation**: Automated checks for audit log completeness