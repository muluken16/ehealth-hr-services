/**
 * Enhanced File Manager JavaScript
 * Handles all client-side functionality for the file manager
 */

class FileManagerApp {
    constructor() {
        this.currentSection = 'dashboard';
        this.selectedFiles = [];
        this.uploadQueue = [];
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadDashboard();
        this.setupDragAndDrop();
        this.setupSearch();
    }
    
    bindEvents() {
        // Sidebar navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchSection(e.target.dataset.section);
            });
        });
        
        // Upload form
        document.getElementById('uploadBtn').addEventListener('click', () => {
            this.handleUpload();
        });
        
        // Entity type change
        document.querySelector('select[name="entity_type"]').addEventListener('change', (e) => {
            this.loadCategories(e.target.value);
        });
        
        // File input change
        document.getElementById('fileInput').addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files);
        });
        
        // Upload zone click
        document.getElementById('uploadZone').addEventListener('click', () => {
            document.getElementById('fileInput').click();
        });
        
        // Share button
        document.getElementById('shareBtn').addEventListener('click', () => {
            this.handleShare();
        });
        
        // User search for sharing
        document.getElementById('shareUserSearch').addEventListener('input', (e) => {
            this.searchUsers(e.target.value);
        });
    }
    
    switchSection(section) {
        // Update active nav link
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${section}"]`).classList.add('active');
        
        this.currentSection = section;
        
        if (section === 'dashboard') {
            document.getElementById('dashboard-section').style.display = 'block';
            document.getElementById('dynamic-content').style.display = 'none';
            this.loadDashboard();
        } else {
            document.getElementById('dashboard-section').style.display = 'none';
            document.getElementById('dynamic-content').style.display = 'block';
            this.loadSectionContent(section);
        }
    }
    
    async loadDashboard() {
        try {
            const response = await fetch('api/dashboard.php');
            const data = await response.json();
            
            if (data.success) {
                this.updateDashboardStats(data.stats);
                this.loadRecentFiles(data.recent_files);
            }
        } catch (error) {
            console.error('Error loading dashboard:', error);
            this.showNotification('Error loading dashboard data', 'error');
        }
    }
    
    updateDashboardStats(stats) {
        document.getElementById('total-files').textContent = stats.total_files || 0;
        document.getElementById('storage-used').textContent = this.formatFileSize(stats.storage_used || 0);
        document.getElementById('shared-files').textContent = stats.shared_files || 0;
        document.getElementById('recent-uploads').textContent = stats.recent_uploads || 0;
    }
    
    loadRecentFiles(files) {
        const container = document.getElementById('recent-files-list');
        
        if (!files || files.length === 0) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No files uploaded yet</p>
                </div>
            `;
            return;
        }
        
        const html = files.map(file => `
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                <div class="d-flex align-items-center">
                    <i class="fas ${this.getFileIcon(file.original_name)} fa-lg me-3 ${this.getFileIconColor(file.original_name)}"></i>
                    <div>
                        <h6 class="mb-0">${this.escapeHtml(file.original_name)}</h6>
                        <small class="text-muted">${file.entity_type} • ${this.formatFileSize(file.file_size)} • ${this.formatDate(file.upload_date)}</small>
                    </div>
                </div>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="fileManager.previewFile(${file.file_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-success" onclick="fileManager.downloadFile(${file.file_id})">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="fileManager.shareFile(${file.file_id})">
                        <i class="fas fa-share-alt"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }
    
    async loadSectionContent(section) {
        const container = document.getElementById('dynamic-content');
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                <p class="mt-3">Loading ${section} files...</p>
            </div>
        `;
        
        try {
            const response = await fetch(`api/files.php?section=${section}`);
            const data = await response.json();
            
            if (data.success) {
                this.renderFileGrid(data.files, section);
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        ${data.message || 'Error loading files'}
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading section content:', error);
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Error loading files. Please try again.
                </div>
            `;
        }
    }
    
    renderFileGrid(files, section) {
        const container = document.getElementById('dynamic-content');
        
        const header = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas ${this.getSectionIcon(section)} me-2"></i>${this.getSectionTitle(section)}</h2>
                <div class="d-flex gap-2">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search files..." id="fileSearch">
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                </div>
            </div>
        `;
        
        if (!files || files.length === 0) {
            container.innerHTML = header + `
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">No files found</h4>
                    <p class="text-muted">Upload your first file to get started</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload me-2"></i>Upload File
                    </button>
                </div>
            `;
            return;
        }
        
        const fileGrid = `
            <div class="file-grid" id="fileGrid">
                ${files.map(file => this.renderFileCard(file)).join('')}
            </div>
        `;
        
        container.innerHTML = header + fileGrid;
        
        // Bind search functionality
        document.getElementById('fileSearch').addEventListener('input', (e) => {
            this.filterFiles(e.target.value);
        });
    }
    
    renderFileCard(file) {
        return `
            <div class="file-card" data-file-id="${file.file_id}" data-file-name="${this.escapeHtml(file.original_name)}">
                <div class="file-icon ${this.getFileIconColor(file.original_name)}">
                    <i class="fas ${this.getFileIcon(file.original_name)}"></i>
                </div>
                <h6 class="mb-2" title="${this.escapeHtml(file.original_name)}">
                    ${this.truncateText(file.original_name, 25)}
                </h6>
                <div class="text-muted small mb-3">
                    <div><i class="fas fa-folder me-1"></i>${file.category}</div>
                    <div><i class="fas fa-hdd me-1"></i>${this.formatFileSize(file.file_size)}</div>
                    <div><i class="fas fa-calendar me-1"></i>${this.formatDate(file.upload_date)}</div>
                </div>
                <div class="file-actions">
                    <button class="btn btn-outline-primary btn-action" onclick="fileManager.previewFile(${file.file_id})" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-success btn-action" onclick="fileManager.downloadFile(${file.file_id})" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-outline-info btn-action" onclick="fileManager.shareFile(${file.file_id})" title="Share">
                        <i class="fas fa-share-alt"></i>
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-action dropdown-toggle" data-bs-toggle="dropdown" title="More">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="fileManager.renameFile(${file.file_id})">
                                <i class="fas fa-edit me-2"></i>Rename
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="fileManager.moveFile(${file.file_id})">
                                <i class="fas fa-arrows-alt me-2"></i>Move
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="fileManager.deleteFile(${file.file_id})">
                                <i class="fas fa-trash me-2"></i>Delete
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        `;
    }
    
    setupDragAndDrop() {
        const uploadZone = document.getElementById('uploadZone');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, this.preventDefaults, false);
        });
        
        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('dragover');
            }, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('dragover');
            }, false);
        });
        
        uploadZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            this.handleFileSelect(files);
        }, false);
    }
    
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    handleFileSelect(files) {
        if (files.length > 0) {
            const file = files[0];
            document.getElementById('fileInput').files = files;
            
            // Update upload zone to show selected file
            const uploadZone = document.getElementById('uploadZone');
            uploadZone.innerHTML = `
                <i class="fas fa-file fa-3x text-success mb-3"></i>
                <h5>${file.name}</h5>
                <p class="text-muted">${this.formatFileSize(file.size)}</p>
                <small class="text-success">Ready to upload</small>
            `;
        }
    }
    
    async loadCategories(entityType) {
        const categorySelect = document.querySelector('select[name="category"]');
        
        const categories = {
            'employee': ['personal', 'banking', 'education', 'criminal', 'warranty', 'leave', 'documents'],
            'patient': ['medical', 'insurance', 'emergency', 'documents'],
            'payroll': ['payslips', 'tax', 'benefits', 'documents'],
            'recruitment': ['applications', 'interviews', 'contracts', 'documents'],
            'training': ['materials', 'certificates', 'evaluations', 'documents'],
            'emergency': ['reports', 'responses', 'media', 'documents'],
            'quality': ['assessments', 'reports', 'certifications', 'documents'],
            'system': ['backups', 'logs', 'configurations', 'documents']
        };
        
        const entityCategories = categories[entityType] || ['documents'];
        
        categorySelect.innerHTML = '<option value="">Select Category</option>' +
            entityCategories.map(cat => `<option value="${cat}">${this.capitalizeFirst(cat)}</option>`).join('');
    }
    
    async handleUpload() {
        const form = document.getElementById('uploadForm');
        const formData = new FormData(form);
        const uploadBtn = document.getElementById('uploadBtn');
        const progressContainer = document.querySelector('.progress-container');
        const progressBar = document.querySelector('.progress-bar');
        
        // Validate form
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Show progress
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';
        progressContainer.style.display = 'block';
        
        try {
            const xhr = new XMLHttpRequest();
            
            // Track upload progress
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                    progressBar.textContent = Math.round(percentComplete) + '%';
                }
            });
            
            xhr.onload = () => {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        this.showNotification('File uploaded successfully!', 'success');
                        bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                        this.resetUploadForm();
                        this.refreshCurrentSection();
                    } else {
                        this.showNotification(response.message || 'Upload failed', 'error');
                    }
                } else {
                    this.showNotification('Upload failed. Please try again.', 'error');
                }
                
                // Reset button and progress
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload File';
                progressContainer.style.display = 'none';
                progressBar.style.width = '0%';
            };
            
            xhr.onerror = () => {
                this.showNotification('Upload failed. Please check your connection.', 'error');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload File';
                progressContainer.style.display = 'none';
            };
            
            xhr.open('POST', 'api/upload.php');
            xhr.send(formData);
            
        } catch (error) {
            console.error('Upload error:', error);
            this.showNotification('Upload failed. Please try again.', 'error');
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload me-2"></i>Upload File';
            progressContainer.style.display = 'none';
        }
    }
    
    resetUploadForm() {
        document.getElementById('uploadForm').reset();
        document.getElementById('uploadZone').innerHTML = `
            <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
            <h5>Drag & Drop Files Here</h5>
            <p class="text-muted">or click to browse</p>
        `;
    }
    
    async previewFile(fileId) {
        try {
            const response = await fetch(`api/preview.php?file_id=${fileId}`);
            const data = await response.json();
            
            if (data.success) {
                const modal = new bootstrap.Modal(document.getElementById('previewModal'));
                const previewContent = document.getElementById('previewContent');
                const downloadBtn = document.getElementById('downloadFromPreview');
                
                if (data.file.mime_type.startsWith('image/')) {
                    previewContent.innerHTML = `
                        <img src="${data.preview_url}" class="file-preview" alt="${data.file.original_name}">
                    `;
                } else if (data.file.mime_type === 'application/pdf') {
                    previewContent.innerHTML = `
                        <embed src="${data.preview_url}" type="application/pdf" width="100%" height="500px">
                    `;
                } else {
                    previewContent.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-file fa-4x text-muted mb-3"></i>
                            <h5>${data.file.original_name}</h5>
                            <p class="text-muted">Preview not available for this file type</p>
                        </div>
                    `;
                }
                
                downloadBtn.onclick = () => this.downloadFile(fileId);
                modal.show();
            } else {
                this.showNotification(data.message || 'Cannot preview file', 'error');
            }
        } catch (error) {
            console.error('Preview error:', error);
            this.showNotification('Error loading file preview', 'error');
        }
    }
    
    async downloadFile(fileId) {
        try {
            const response = await fetch(`api/download.php?file_id=${fileId}`);
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = response.headers.get('Content-Disposition')?.split('filename=')[1]?.replace(/"/g, '') || 'download';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showNotification('File downloaded successfully', 'success');
            } else {
                const data = await response.json();
                this.showNotification(data.message || 'Download failed', 'error');
            }
        } catch (error) {
            console.error('Download error:', error);
            this.showNotification('Error downloading file', 'error');
        }
    }
    
    shareFile(fileId) {
        document.getElementById('shareFileId').value = fileId;
        const modal = new bootstrap.Modal(document.getElementById('shareModal'));
        modal.show();
        this.loadShareUsers();
    }
    
    async loadShareUsers() {
        try {
            const response = await fetch('api/users.php');
            const data = await response.json();
            
            if (data.success) {
                this.renderShareUsers(data.users);
            }
        } catch (error) {
            console.error('Error loading users:', error);
        }
    }
    
    renderShareUsers(users) {
        const container = document.getElementById('shareUserList');
        container.innerHTML = users.map(user => `
            <div class="user-item" data-user-id="${user.id}">
                <div class="d-flex align-items-center">
                    <input type="checkbox" class="form-check-input me-2" value="${user.id}">
                    <div>
                        <div class="fw-bold">${this.escapeHtml(user.name)}</div>
                        <small class="text-muted">${user.role} • ${user.zone || 'N/A'}</small>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    searchUsers(query) {
        const userItems = document.querySelectorAll('.user-item');
        userItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(query.toLowerCase()) ? 'block' : 'none';
        });
    }
    
    async handleShare() {
        const form = document.getElementById('shareForm');
        const formData = new FormData(form);
        const selectedUsers = Array.from(document.querySelectorAll('.user-item input:checked')).map(cb => cb.value);
        
        if (selectedUsers.length === 0) {
            this.showNotification('Please select at least one user to share with', 'warning');
            return;
        }
        
        formData.append('shared_with', JSON.stringify(selectedUsers));
        
        try {
            const response = await fetch('api/share.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('File shared successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('shareModal')).hide();
            } else {
                this.showNotification(data.message || 'Sharing failed', 'error');
            }
        } catch (error) {
            console.error('Share error:', error);
            this.showNotification('Error sharing file', 'error');
        }
    }
    
    async deleteFile(fileId) {
        if (!confirm('Are you sure you want to delete this file? This action cannot be undone.')) {
            return;
        }
        
        try {
            const response = await fetch('api/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ file_id: fileId })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('File deleted successfully', 'success');
                this.refreshCurrentSection();
            } else {
                this.showNotification(data.message || 'Delete failed', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification('Error deleting file', 'error');
        }
    }
    
    filterFiles(query) {
        const fileCards = document.querySelectorAll('.file-card');
        fileCards.forEach(card => {
            const fileName = card.dataset.fileName.toLowerCase();
            card.style.display = fileName.includes(query.toLowerCase()) ? 'block' : 'none';
        });
    }
    
    refreshCurrentSection() {
        if (this.currentSection === 'dashboard') {
            this.loadDashboard();
        } else {
            this.loadSectionContent(this.currentSection);
        }
    }
    
    setupSearch() {
        // Global search functionality can be added here
    }
    
    // Utility functions
    getFileIcon(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        const iconMap = {
            'pdf': 'fa-file-pdf',
            'doc': 'fa-file-word',
            'docx': 'fa-file-word',
            'jpg': 'fa-file-image',
            'jpeg': 'fa-file-image',
            'png': 'fa-file-image',
            'gif': 'fa-file-image',
            'zip': 'fa-file-archive',
            'rar': 'fa-file-archive',
            'txt': 'fa-file-alt',
            'csv': 'fa-file-csv',
            'xls': 'fa-file-excel',
            'xlsx': 'fa-file-excel'
        };
        return iconMap[ext] || 'fa-file';
    }
    
    getFileIconColor(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        if (['pdf'].includes(ext)) return 'pdf';
        if (['doc', 'docx'].includes(ext)) return 'doc';
        if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return 'image';
        return 'default';
    }
    
    getSectionIcon(section) {
        const iconMap = {
            'employees': 'fa-users',
            'patients': 'fa-user-injured',
            'payroll': 'fa-money-bill-wave',
            'recruitment': 'fa-user-plus',
            'training': 'fa-graduation-cap',
            'emergency': 'fa-exclamation-triangle',
            'quality': 'fa-award',
            'shared': 'fa-share-alt',
            'reports': 'fa-chart-bar',
            'settings': 'fa-cog'
        };
        return iconMap[section] || 'fa-folder';
    }
    
    getSectionTitle(section) {
        const titleMap = {
            'employees': 'Employee Files',
            'patients': 'Patient Files',
            'payroll': 'Payroll Files',
            'recruitment': 'Recruitment Files',
            'training': 'Training Files',
            'emergency': 'Emergency Files',
            'quality': 'Quality Assurance Files',
            'shared': 'Shared Files',
            'reports': 'Reports',
            'settings': 'Settings'
        };
        return titleMap[section] || 'Files';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
    
    truncateText(text, maxLength) {
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
    
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
}

// Initialize the file manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.fileManager = new FileManagerApp();
});