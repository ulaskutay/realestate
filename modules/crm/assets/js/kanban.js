/**
 * Kanban Drag & Drop Functionality
 */

(function() {
    'use strict';
    
    let draggedElement = null;
    let draggedElementParent = null;
    
    // Initialize kanban
    function initKanban() {
        const columns = document.querySelectorAll('[data-status]');
        
        columns.forEach(column => {
            // Column event listeners
            column.addEventListener('dragover', handleDragOver);
            column.addEventListener('dragleave', handleDragLeave);
            column.addEventListener('drop', handleDrop);
            
            // Card event listeners
            const cards = column.querySelectorAll('[draggable="true"]');
            cards.forEach(card => {
                card.addEventListener('dragstart', handleDragStart);
                card.addEventListener('dragend', handleDragEnd);
            });
        });
    }
    
    function handleDragStart(e) {
        draggedElement = this;
        draggedElementParent = this.parentElement;
        this.style.opacity = '0.5';
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', this.innerHTML);
    }
    
    function handleDragEnd(e) {
        this.style.opacity = '1';
        
        // Remove drag-over class from all columns
        document.querySelectorAll('[data-status]').forEach(col => {
            col.classList.remove('drag-over');
        });
    }
    
    function handleDragOver(e) {
        if (e.preventDefault) {
            e.preventDefault();
        }
        e.dataTransfer.dropEffect = 'move';
        this.classList.add('drag-over');
        return false;
    }
    
    function handleDragLeave(e) {
        this.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        if (e.stopPropagation) {
            e.stopPropagation();
        }
        
        this.classList.remove('drag-over');
        
        if (draggedElement && draggedElement !== this) {
            const newStatus = this.getAttribute('data-status');
            const leadId = draggedElement.getAttribute('data-lead-id');
            
            // Update status via AJAX
            updateLeadStatus(leadId, newStatus)
                .then(data => {
                    if (data.success) {
                        // Move element to new column
                        this.appendChild(draggedElement);
                        
                        // Update counts if needed
                        updateColumnCounts();
                        
                        // Show success message
                        showNotification('Lead durumu güncellendi', 'success');
                    } else {
                        // Revert on error
                        if (draggedElementParent) {
                            draggedElementParent.appendChild(draggedElement);
                        }
                        showNotification('Hata: ' + (data.message || 'Durum güncellenemedi'), 'error');
                    }
                })
                .catch(error => {
                    // Revert on error
                    if (draggedElementParent) {
                        draggedElementParent.appendChild(draggedElement);
                    }
                    showNotification('Hata: ' + error.message, 'error');
                });
        }
        
        return false;
    }
    
    function updateLeadStatus(leadId, status) {
        const formData = new FormData();
        formData.append('id', leadId);
        formData.append('status', status);
        
        return fetch(adminUrl('module/crm/lead_update_status'), {
            method: 'POST',
            body: formData
        })
        .then(response => response.json());
    }
    
    function updateColumnCounts() {
        document.querySelectorAll('[data-status]').forEach(column => {
            const status = column.getAttribute('data-status');
            const count = column.querySelectorAll('[draggable="true"]').length;
            const header = column.previousElementSibling;
            if (header) {
                const countSpan = header.querySelector('span');
                if (countSpan) {
                    countSpan.textContent = `(${count})`;
                }
            }
        });
    }
    
    function showNotification(message, type) {
        // Simple notification - can be enhanced with a toast library
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Admin URL helper
    function adminUrl(path) {
        if (typeof window.adminUrl === 'function') {
            return window.adminUrl(path);
        }
        return '/admin/' + path;
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKanban);
    } else {
        initKanban();
    }
    
    // Export for global access
    window.KanbanManager = {
        init: initKanban,
        updateColumnCounts: updateColumnCounts
    };
})();
