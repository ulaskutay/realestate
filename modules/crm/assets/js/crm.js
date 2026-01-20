/**
 * CRM Module JavaScript
 */

// WhatsApp link oluşturma
function createWhatsAppLink(phone, message) {
    if (!phone) return '#';
    
    // Telefon numarasını temizle (sadece rakamlar)
    const cleanPhone = phone.replace(/[^0-9]/g, '');
    
    // Mesajı encode et
    const encodedMessage = encodeURIComponent(message || 'Merhaba, size nasıl yardımcı olabilirim?');
    
    return `https://wa.me/${cleanPhone}?text=${encodedMessage}`;
}

// Telefon numarası formatla
function formatPhone(phone) {
    if (!phone) return '';
    
    // Sadece rakamları al
    const cleaned = phone.replace(/[^0-9]/g, '');
    
    // Türkiye formatına çevir (0XXX XXX XX XX)
    if (cleaned.length === 10) {
        return cleaned.replace(/(\d{3})(\d{3})(\d{2})(\d{2})/, '0$1 $2 $3 $4');
    }
    
    return phone;
}

// Lead durum güncelleme
function updateLeadStatus(leadId, status) {
    const formData = new FormData();
    formData.append('id', leadId);
    formData.append('status', status);
    
    return fetch(adminUrl('module/crm/lead_update_status'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return Promise.resolve(data);
        } else {
            return Promise.reject(new Error(data.message || 'Durum güncellenemedi'));
        }
    });
}

// Not ekleme
function addNote(leadId, note) {
    const formData = new FormData();
    formData.append('lead_id', leadId);
    formData.append('note', note);
    
    return fetch(adminUrl('module/crm/note_add'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

// Görev ekleme
function addTask(leadId, taskData) {
    const formData = new FormData();
    formData.append('lead_id', leadId);
    formData.append('title', taskData.title);
    formData.append('description', taskData.description || '');
    if (taskData.due_date) {
        formData.append('due_date', taskData.due_date);
    }
    
    return fetch(adminUrl('module/crm/task_add'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

// E-posta gönderme
function sendEmail(leadId, subject, body) {
    const formData = new FormData();
    formData.append('lead_id', leadId);
    formData.append('subject', subject);
    formData.append('body', body);
    
    return fetch(adminUrl('module/crm/send_email'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

// Dönüşüm kaydetme
function addConversion(leadId, conversionData) {
    const formData = new FormData();
    formData.append('lead_id', leadId);
    formData.append('type', conversionData.type);
    formData.append('value', conversionData.value);
    formData.append('notes', conversionData.notes || '');
    
    return fetch(adminUrl('module/crm/conversion_add'), {
        method: 'POST',
        body: formData
    })
    .then(response => response.json());
}

// Admin URL helper (eğer tanımlı değilse)
if (typeof adminUrl === 'undefined') {
    function adminUrl(path) {
        return '/admin/' + path;
    }
}

// Export functions
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        createWhatsAppLink,
        formatPhone,
        updateLeadStatus,
        addNote,
        addTask,
        sendEmail,
        addConversion
    };
}
