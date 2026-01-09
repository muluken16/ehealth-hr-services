/**
 * Enhanced File Manager JavaScript
 * Handles all client-side functionality for the file manager
 */

class FileManagerApp {
    constructor() {
        this.currentSection = 'dashboard';
        this.selectedFiles = [];
        this.uploadQueue = [];
        this.currentFilters = {
            search: '',
            category: '',
            dateFrom: '',
            dateTo: '',
            fileType: ''
        };
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadDashboard();
        this.setupDragAndDrop();
        this.setupSearch();
        this.setupBulkOperations();
    }

    bindEvents() {
        // Sidebar navigation
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchSection(e.target.closest('.nav-link').dataset.section);
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

        // Filter changes
        document.addEventListener('change', (e) => {
            if (e.target.id === 'filterCategory' || e.target.id === 'filterDateFrom' ||
                e.target.id === 'filterDateTo' || e.target.id === 'filterFileType') {
                this.applyFilters();
            }
        });

        // Search input
        document.addEventListener('input', (e) => {
            if (e.target.id === 'fileSearch' || e.target.id === 'globalSearch') {
                this.currentFilters.search = e.target.value;
                this.applyFilters();
            }
        });
    }

    switchSection(section) {
        // Update active nav link
        document.querySelectorAll('.sidebar .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-section="${section}"]`).classList.add('active');

        this.currentSection = section;
        this.selectedFiles = [];

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
                this.loadStorageInfo();
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

    loadStorageInfo() {
        // Load storage breakdown by entity type
        fetch('api/storage_stats.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.renderStorageChart(data.storage_by_type);
                }
            })
            .catch(error => console.error('Error loading storage info:', error));
    }

    renderStorageChart(storageData) {
        // Simple storage visualization
        const container = document.getElementById('storage-breakdown');
        if (!container) return;

        const colors = ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'];
        let html = '<div class="storage-chart"><h6>Storage by Category</h6>';

        let total = storageData.reduce((sum, item) => sum + item.size, 0);

        storageData.forEach((item, index) => {
            const percent = total > 0 ? ((item.size / total) * 100).toFixed(1) : 0;
            html += `
                <div class="storage-item mb-2">
                    <div class="d-flex justify-content-between small">
                        <span>${this.capitalizeFirst(item.entity_type)}</span>
                        <span>${this.formatFileSize(item.size)} (${percent}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar" style="width: ${percent}%; background: ${colors[index % colors.length]}"></div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        container.innerHTML = html;
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
                    <button class="btn btn-outline-primary" onclick="fileManager.previewFile(${file.file_id})" title="Preview">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-outline-success" onclick="fileManager.downloadFile(${file.file_id})" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-outline-info" onclick="fileManager.shareFile(${file.file_id})" title="Share">
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
            const params = new URLSearchParams({
                section: section,
                ...this.currentFilters
            });
            const response = await fetch(`api/files.php?${params}`);
            const data = await response.json();

            if (data.success) {
                this.renderFileGrid(data.files, section, data.pagination);
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

    renderFileGrid(files, section, pagination = null) {
        const container = document.getElementById('dynamic-content');

        const header = `
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h2><i class="fas ${this.getSectionIcon(section)} me-2"></i>${this.getSectionTitle(section)}</h2>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" placeholder="Search files..." id="fileSearch" value="${this.currentFilters.search}">
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                        <i class="fas fa-upload me-2"></i>Upload
                    </button>
                    <button class="btn btn-outline-secondary" onclick="fileManager.showFiltersModal()">
                        <i class="fas fa-filter me-2"></i>Filter
                    </button>
                </div>
            </div>
            <!-- Filter Bar -->
            <div class="filter-bar mb-3 p-3 bg-light rounded" id="filterBar" style="display: none;">
                <div class="row g-2">
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filterCategory">
                            <option value="">All Categories</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-sm" id="filterDateFrom" placeholder="From Date">
                    </div>
                    <div class="col-md-3">
                        <input type="date" class="form-control form-control-sm" id="filterDateTo" placeholder="To Date">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select form-select-sm" id="filterFileType">
                            <option value="">All Types</option>
                            <option value="pdf">PDF</option>
                            <option value="image">Images</option>
                            <option value="doc">Documents</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Bulk Actions Bar -->
            <div class="bulk-actions-bar mb-3 p-2 bg-primary text-white rounded" id="bulkActionsBar" style="display: none;">
                <div class="d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-check-square me-2"></i><span id="selectedCount">0</span> files selected</span>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-light" onclick="fileManager.bulkDownload()">
                            <i class="fas fa-download me-1"></i>Download
                        </button>
                        <button class="btn btn-light" onclick="fileManager.bulkShare()">
                            <i class="fas fa-share-alt me-1"></i>Share
                        </button>
                        <button class="btn btn-danger" onclick="fileManager.bulkDelete()">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                        <button class="btn btn-light" onclick="fileManager.clearSelection()">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
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

        // Pagination
        let paginationHtml = '';
        if (pagination && pagination.total > pagination.limit) {
            const totalPages = Math.ceil(pagination.total / pagination.limit);
            paginationHtml = `
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item ${pagination.offset === 0 ? 'disabled' : ''}">
                            <button class="page-link" onclick="fileManager.loadPage(${pagination.offset - pagination.limit})">Previous</button>
                        </li>
                        ${Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                const page = Math.floor(pagination.offset / pagination.limit);
                const pageNum = page + i - 2;
                if (pageNum >= 0 && pageNum < totalPages) {
                    return `<li class="page-item ${pageNum === page ? 'active' : ''}">
                                    <button class="page-link" onclick="fileManager.loadPage(${pageNum * pagination.limit})">${pageNum + 1}</button>
                                </li>`;
                }
                return '';
            }).join('')}
                        <li class="page-item ${pagination.offset + pagination.limit >= pagination.total ? 'disabled' : ''}">
                            <button class="page-link" onclick="fileManager.loadPage(${pagination.offset + pagination.limit})">Next</button>
                        </li>
                    </ul>
                </nav>
            `;
        }

        container.innerHTML = header + fileGrid + paginationHtml;

        // Bind search functionality
        document.getElementById('fileSearch').addEventListener('input', (e) => {
            this.currentFilters.search = e.target.value;
            this.debounceApplyFilters();
        });
    }

    renderFileCard(file) {
        const isSelected = this.selectedFiles.includes(file.file_id);
        return `
            <div class="file-card ${isSelected ? 'selected border-primary' : ''}" 
                 data-file-id="${file.file_id}" 
                 data-file-name="${this.escapeHtml(file.original_name)}"
                 data-category="${file.category}"
                 data-mime-type="${file.mime_type}">
                <div class="file-select">
                    <input type="checkbox" class="form-check-input" 
                           ${isSelected ? 'checked' : ''} 
                           onchange="fileManager.toggleFileSelection(${file.file_id}, this)">
                </div>
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
                    ${file.uploaded_by === this.getCurrentUserId() ? '<div><i class="fas fa-user me-1 text-success"></i>Yours</div>' : ''}
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
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-outline-secondary btn-action dropdown-toggle" data-bs-toggle="dropdown" title="More">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="fileManager.showFileDetails(${file.file_id})">
                                <i class="fas fa-info-circle me-2"></i>Details
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="fileManager.renameFile(${file.file_id}, '${this.escapeHtml(file.original_name)}')">
                                <i class="fas fa-edit me-2"></i>Rename
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="fileManager.moveFile(${file.file_id})">
                                <i class="fas fa-arrows-alt me-2"></i>Move
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="fileManager.copyFile(${file.file_id})">
                                <i class="fas fa-copy me-2"></i>Copy
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

    // File selection methods
    toggleFileSelection(fileId, checkbox) {
        if (checkbox.checked) {
            if (!this.selectedFiles.includes(fileId)) {
                this.selectedFiles.push(fileId);
            }
        } else {
            this.selectedFiles = this.selectedFiles.filter(id => id !== fileId);
        }

        this.updateBulkActionsBar();

        // Update card appearance
        const card = document.querySelector(`.file-card[data-file-id="${fileId}"]`);
        if (card) {
            card.classList.toggle('selected', checkbox.checked);
            card.classList.toggle('border-primary', checkbox.checked);
        }
    }

    updateBulkActionsBar() {
        const bar = document.getElementById('bulkActionsBar');
        const count = document.getElementById('selectedCount');
        if (bar && count) {
            bar.style.display = this.selectedFiles.length > 0 ? 'block' : 'none';
            count.textContent = this.selectedFiles.length;
        }
    }

    clearSelection() {
        this.selectedFiles = [];
        document.querySelectorAll('.file-card input[type="checkbox"]').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.file-card');
            if (card) {
                card.classList.remove('selected', 'border-primary');
            }
        });
        this.updateBulkActionsBar();
    }

    getCurrentUserId() {
        // Get from data attribute set by PHP
        return parseInt(document.body.dataset.userId || '0');
    }

    // Bulk operations
    bulkDownload() {
        if (this.selectedFiles.length === 0) return;

        this.selectedFiles.forEach(fileId => {
            this.downloadFile(fileId);
        });

        this.showNotification(`${this.selectedFiles.length} files download started`, 'info');
    }

    bulkShare() {
        if (this.selectedFiles.length === 0) return;

        // Open share modal with first file, others will be added
        this.shareFile(this.selectedFiles[0]);
        this.showNotification('Select files to share in the modal', 'info');
    }

    async bulkDelete() {
        if (this.selectedFiles.length === 0) return;

        if (!confirm(`Are you sure you want to delete ${this.selectedFiles.length} files? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch('api/bulk_delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ file_ids: this.selectedFiles })
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification(`Deleted ${data.deleted_count} files successfully`, 'success');
                this.clearSelection();
                this.refreshCurrentSection();
            } else {
                this.showNotification(data.message || 'Delete failed', 'error');
            }
        } catch (error) {
            console.error('Bulk delete error:', error);
            this.showNotification('Error deleting files', 'error');
        }
    }

    loadPage(offset) {
        this.currentFilters.offset = offset;
        this.loadSectionContent(this.currentSection);
    }

    setupBulkOperations() {
        // Enable multi-select on file cards
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Shift' || e.metaKey) {
                document.body.classList.add('multi-select-mode');
            }
        });

        document.addEventListener('keyup', (e) => {
            if (e.key === 'Shift' || e.key === 'Meta') {
                document.body.classList.remove('multi-select-mode');
            }
        });
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

                if (data.can_preview) {
                    if (data.file.mime_type.startsWith('image/')) {
                        previewContent.innerHTML = `
                            <img src="${data.preview_url}" class="file-preview" alt="${data.file.original_name}">
                        `;
                    } else if (data.file.mime_type === 'application/pdf') {
                        previewContent.innerHTML = `
                            <embed src="${data.preview_url}" type="application/pdf" width="100%" height="500px">
                        `;
                    } else if (data.file.mime_type === 'text/plain') {
                        previewContent.innerHTML = `
                            <iframe src="${data.preview_url}" style="width: 100%; height: 500px; border: none;"></iframe>
                        `;
                    } else {
                        previewContent.innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas ${this.getFileIcon(data.file.original_name)} fa-4x text-muted mb-3"></i>
                                <h5>${data.file.original_name}</h5>
                                <p class="text-muted">Preview available for download</p>
                            </div>
                        `;
                    }
                } else {
                    previewContent.innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas ${this.getFileIcon(data.file.original_name)} fa-4x text-muted mb-3"></i>
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
                        <small class="text-muted">${user.display_role} • ${user.location || 'N/A'}</small>
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

    renameFile(fileId, currentName) {
        const newName = prompt('Enter new file name:', currentName);
        if (newName && newName !== currentName) {
            fetch('api/rename.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ file_id: fileId, new_name: newName })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showNotification('File renamed successfully', 'success');
                        this.refreshCurrentSection();
                    } else {
                        this.showNotification(data.message || 'Rename failed', 'error');
                    }
                })
                .catch(error => {
                    console.error('Rename error:', error);
                    this.showNotification('Error renaming file', 'error');
                });
        }
    }

    moveFile(fileId) {
        // Show move modal or redirect to move interface
        const modal = new bootstrap.Modal(document.getElementById('moveModal'));
        document.getElementById('moveFileId').value = fileId;
        this.loadMoveOptions(fileId);
        modal.show();
    }

    async loadMoveOptions(fileId) {
        try {
            const response = await fetch(`api/file_info.php?file_id=${fileId}`);
            const data = await response.json();

            if (data.success) {
                const file = data.file;
                document.getElementById('moveFromLocation').textContent =
                    `${file.entity_type} > ${file.entity_id} > ${file.category}`;

                // Load destination options
                const destSelect = document.getElementById('moveDestination');
                destSelect.innerHTML = '<option value="">Select Destination</option>';

                // Add category options based on entity type
                const categories = this.getCategoriesForEntity(file.entity_type);
                categories.forEach(cat => {
                    if (cat !== file.category) {
                        destSelect.innerHTML += `<option value="${cat}">${this.capitalizeFirst(cat)}</option>`;
                    }
                });
            }
        } catch (error) {
            console.error('Error loading move options:', error);
        }
    }

    getCategoriesForEntity(entityType) {
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
        return categories[entityType] || ['documents'];
    }

    async copyFile(fileId) {
        if (!confirm('Create a copy of this file?')) return;

        try {
            const response = await fetch('api/copy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ file_id: fileId })
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('File copied successfully', 'success');
                this.refreshCurrentSection();
            } else {
                this.showNotification(data.message || 'Copy failed', 'error');
            }
        } catch (error) {
            console.error('Copy error:', error);
            this.showNotification('Error copying file', 'error');
        }
    }

    showFileDetails(fileId) {
        fetch(`api/file_info.php?file_id=${fileId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const file = data.file;
                    const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
                    const content = document.getElementById('detailsContent');

                    content.innerHTML = `
                        <table class="table table-sm">
                            <tr><th>File Name</th><td>${this.escapeHtml(file.original_name)}</td></tr>
                            <tr><th>Size</th><td>${this.formatFileSize(file.file_size)}</td></tr>
                            <tr><th>Type</th><td>${file.mime_type}</td></tr>
                            <tr><th>Category</th><td>${file.category}</td></tr>
                            <tr><th>Entity</th><td>${file.entity_type} > ${file.entity_id}</td></tr>
                            <tr><th>Uploaded</th><td>${this.formatDate(file.upload_date)}</td></tr>
                            <tr><th>Uploaded By</th><td>${this.escapeHtml(file.uploader_name || 'Unknown')}</td></tr>
                            <tr><th>Downloads</th><td>${file.download_count || 0}</td></tr>
                            <tr><th>Views</th><td>${file.view_count || 0}</td></tr>
                        </table>
                    `;

                    // Add share info if file is shared
                    if (data.shares && data.shares.length > 0) {
                        content.innerHTML += `
                            <h6 class="mt-3">Shared With</h6>
                            <ul class="list-group list-group-flush">
                                ${data.shares.map(share => `
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ${this.escapeHtml(share.shared_with_name)}
                                        <span class="badge bg-${share.permission_type === 'download' ? 'success' : 'primary'}">
                                            ${share.permission_type}
                                        </span>
                                    </li>
                                `).join('')}
                            </ul>
                        `;
                    }

                    modal.show();
                }
            })
            .catch(error => {
                console.error('Error loading file details:', error);
                this.showNotification('Error loading file details', 'error');
            });
    }

    showFiltersModal() {
        const filterBar = document.getElementById('filterBar');
        filterBar.style.display = filterBar.style.display === 'none' ? 'block' : 'none';
    }

    applyFilters() {
        this.currentFilters.category = document.getElementById('filterCategory')?.value || '';
        this.currentFilters.dateFrom = document.getElementById('filterDateFrom')?.value || '';
        this.currentFilters.dateTo = document.getElementById('filterDateTo')?.value || '';
        this.currentFilters.fileType = document.getElementById('filterFileType')?.value || '';
        this.refreshCurrentSection();
    }

    debounceApplyFilters() {
        clearTimeout(this.filterTimeout);
        this.filterTimeout = setTimeout(() => this.applyFilters(), 300);
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
            '7z': 'fa-file-archive',
            'txt': 'fa-file-alt',
            'csv': 'fa-file-csv',
            'xls': 'fa-file-excel',
            'xlsx': 'fa-file-excel',
            'ppt': 'fa-file-powerpoint',
            'pptx': 'fa-file-powerpoint',
            'mp3': 'fa-file-audio',
            'wav': 'fa-file-audio',
            'mp4': 'fa-file-video',
            'avi': 'fa-file-video',
            'mov': 'fa-file-video',
            'html': 'fa-file-code',
            'css': 'fa-file-code',
            'js': 'fa-file-code',
            'json': 'fa-file-code'
        };
        return iconMap[ext] || 'fa-file';
    }

    getFileIconColor(filename) {
        const ext = filename.split('.').pop().toLowerCase();
        if (['pdf'].includes(ext)) return 'pdf';
        if (['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(ext)) return 'doc';
        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'].includes(ext)) return 'image';
        if (['zip', 'rar', '7z'].includes(ext)) return 'archive';
        if (['html', 'css', 'js', 'json', 'txt', 'csv'].includes(ext)) return 'text';
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
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
