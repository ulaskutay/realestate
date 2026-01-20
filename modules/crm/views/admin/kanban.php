<?php
// Kanban view
$leads = $leads ?? [
    'new' => [],
    'contacted' => [],
    'quoted' => [],
    'closed' => [],
    'cancelled' => []
];

$statusLabels = [
    'new' => 'Yeni',
    'contacted' => 'İletişimde',
    'quoted' => 'Teklif Verildi',
    'closed' => 'Kapandı',
    'cancelled' => 'İptal'
];

$statusColors = [
    'new' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
    'contacted' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
    'quoted' => 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
    'closed' => 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800',
    'cancelled' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800'
];
?>

<!-- Header -->
<header class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-3">
            <a href="<?php echo admin_url('module/crm'); ?>" class="text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                <span class="material-symbols-outlined text-xl">arrow_back</span>
            </a>
            <h1 class="text-gray-900 dark:text-white text-3xl font-bold tracking-tight">Kanban Görünümü</h1>
        </div>
        <p class="text-gray-500 dark:text-gray-400 text-base">Lead durumlarını görsel olarak yönetin</p>
    </div>
    <a href="<?php echo admin_url('module/crm/lead_create'); ?>" class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
        <span class="material-symbols-outlined text-xl">add</span>
        <span class="text-sm font-medium">Yeni Lead</span>
    </a>
</header>

<!-- Kanban Board -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4" id="kanbanBoard">
    <?php foreach (['new', 'contacted', 'quoted', 'closed', 'cancelled'] as $status): ?>
        <div class="flex flex-col h-full">
            <div class="p-3 rounded-t-lg <?php echo $statusColors[$status]; ?> border-2">
                <h3 class="font-semibold text-gray-900 dark:text-white">
                    <?php echo $statusLabels[$status]; ?>
                    <span class="ml-2 text-sm font-normal text-gray-600 dark:text-gray-400">
                        (<?php echo count($leads[$status] ?? []); ?>)
                    </span>
                </h3>
            </div>
            <div class="flex-1 p-3 bg-gray-100 dark:bg-gray-800 rounded-b-lg border-2 border-t-0 <?php echo $statusColors[$status]; ?> min-h-[400px] max-h-[600px] overflow-y-auto" data-status="<?php echo $status; ?>">
                <?php if (empty($leads[$status])): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Lead yok</p>
                <?php else: ?>
                    <?php foreach ($leads[$status] as $lead): ?>
                        <div class="bg-white dark:bg-gray-700 rounded-lg p-3 mb-3 shadow-sm cursor-move hover:shadow-md transition-shadow" data-lead-id="<?php echo $lead['id']; ?>" draggable="true">
                            <a href="<?php echo admin_url('module/crm/lead_view/' . $lead['id']); ?>" class="block">
                                <p class="font-medium text-gray-900 dark:text-white mb-1"><?php echo esc_html($lead['name']); ?></p>
                                <?php if (!empty($lead['phone'])): ?>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                        <span class="material-symbols-outlined text-xs align-middle">phone</span>
                                        <?php echo esc_html($lead['phone']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($lead['location'])): ?>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">
                                        <span class="material-symbols-outlined text-xs align-middle">location_on</span>
                                        <?php echo esc_html($lead['location']); ?>
                                    </p>
                                <?php endif; ?>
                                <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">
                                    <?php echo turkish_date($lead['created_at'], 'short'); ?>
                                </p>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="<?php echo admin_url('assets/js/kanban.js'); ?>"></script>
<script>
// Kanban drag & drop functionality
document.addEventListener('DOMContentLoaded', function() {
    const columns = document.querySelectorAll('[data-status]');
    let draggedElement = null;
    
    columns.forEach(column => {
        column.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.opacity = '0.5';
        });
        
        column.addEventListener('dragleave', function(e) {
            this.style.opacity = '1';
        });
        
        column.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.opacity = '1';
            
            if (draggedElement) {
                const newStatus = this.getAttribute('data-status');
                const leadId = draggedElement.getAttribute('data-lead-id');
                
                // Update status via AJAX
                const formData = new FormData();
                formData.append('id', leadId);
                formData.append('status', newStatus);
                
                fetch('<?php echo admin_url('module/crm/lead_update_status'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.appendChild(draggedElement);
                        draggedElement = null;
                        // Optionally reload page to update counts
                        setTimeout(() => location.reload(), 500);
                    } else {
                        alert('Hata: ' + data.message);
                        location.reload();
                    }
                });
            }
        });
    });
    
    document.querySelectorAll('[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedElement = this;
            this.style.opacity = '0.5';
        });
        
        item.addEventListener('dragend', function(e) {
            this.style.opacity = '1';
        });
    });
});
</script>
