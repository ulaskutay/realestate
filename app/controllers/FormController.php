<?php
/**
 * Form Controller
 * Form yönetimi için controller
 */

class FormController extends Controller {
    private $formModel;
    private $fieldModel;
    private $submissionModel;
    
    public function __construct() {
        $this->formModel = new Form();
        $this->fieldModel = new FormField();
        $this->submissionModel = new FormSubmission();
    }
    
    /**
     * Giriş kontrolü
     */
    private function checkAuth() {
        if (!is_user_logged_in()) {
            $this->redirect(admin_url('login'));
        }
    }
    
    /**
     * Form listesi
     */
    public function index() {
        $this->checkAuth();
        
        // Yetki kontrolü
        if (!current_user_can('forms.view')) {
            $_SESSION['error_message'] = 'Bu modülde yetkiniz yoktur!';
            $this->redirect(admin_url('dashboard'));
        }
        
        $forms = $this->formModel->getAll();
        
        // Her form için alan ve gönderim sayısını ekle
        foreach ($forms as &$form) {
            $fields = $this->fieldModel->getAllByFormId($form['id']);
            $form['field_count'] = count($fields);
            $form['new_submissions'] = $this->submissionModel->countByFormId($form['id'], 'new');
            // Toplam gönderim sayısı - veritabanından al veya hesapla
            $form['total_submissions'] = $form['submission_count'] ?? $this->submissionModel->countByFormId($form['id']);
        }
        
        $data = [
            'title' => 'Form Yönetimi',
            'user' => get_logged_in_user(),
            'forms' => $forms,
            'message' => $_SESSION['form_message'] ?? null,
            'messageType' => $_SESSION['form_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['form_message']);
        unset($_SESSION['form_message_type']);
        
        $this->view('admin/forms/index', $data);
    }
    
    /**
     * Yeni form oluşturma formu
     */
    public function create() {
        $this->checkAuth();
        
        $data = [
            'title' => 'Yeni Form Oluştur',
            'user' => get_logged_in_user(),
            'form' => null,
            'fieldTypes' => FormField::getFieldTypes()
        ];
        
        $this->view('admin/forms/create', $data);
    }
    
    /**
     * Form kaydetme (POST)
     */
    public function store() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('forms'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'success_message' => $_POST['success_message'] ?? 'Formunuz başarıyla gönderildi!',
            'redirect_url' => $_POST['redirect_url'] ?? '',
            'email_notification' => isset($_POST['email_notification']) ? 1 : 0,
            'notification_email' => $_POST['notification_email'] ?? '',
            'email_subject' => $_POST['email_subject'] ?? '',
            'submit_button_text' => $_POST['submit_button_text'] ?? 'Gönder',
            'submit_button_color' => $_POST['submit_button_color'] ?? '#137fec',
            'form_style' => $_POST['form_style'] ?? 'default',
            'layout' => $_POST['layout'] ?? 'vertical',
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['form_message'] = 'Form adı zorunludur!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms/create'));
        }
        
        $id = $this->formModel->createForm($data);
        
        $_SESSION['form_message'] = 'Form başarıyla oluşturuldu! Şimdi form alanlarını ekleyebilirsiniz.';
        $_SESSION['form_message_type'] = 'success';
        
        $this->redirect(admin_url('forms/edit/' . $id));
    }
    
    /**
     * CRM Lead Formu şablonundan form oluştur
     */
    public function createFromCrmTemplate() {
        $this->checkAuth();
        
        // Form oluştur
        $formData = [
            'name' => 'CRM Lead Formu',
            'description' => 'Emlak sektörü için CRM entegrasyonlu lead formu. Bu formdan gelen gönderimler otomatik olarak CRM\'de lead olarak oluşturulur.',
            'success_message' => 'Formunuz başarıyla gönderildi! En kısa sürede size dönüş yapacağız.',
            'email_notification' => 1,
            'email_subject' => 'Yeni Lead Formu Gönderimi',
            'submit_button_text' => 'Gönder',
            'submit_button_color' => '#137fec',
            'form_style' => 'modern',
            'layout' => 'vertical',
            'status' => 'active'
        ];
        
        $formId = $this->formModel->createForm($formData);
        
        if (!$formId) {
            $_SESSION['form_message'] = 'Form oluşturulurken hata oluştu!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms'));
            return;
        }
        
        // Field'ları oluştur (CRM mapping'ine uygun isimlerle)
        $fields = [
            [
                'form_id' => $formId,
                'field_type' => 'text',
                'field_name' => 'name',
                'field_label' => 'Ad Soyad',
                'placeholder' => 'Adınız ve soyadınız',
                'is_required' => 1,
                'order' => 1
            ],
            [
                'form_id' => $formId,
                'field_type' => 'tel',
                'field_name' => 'phone',
                'field_label' => 'Telefon',
                'placeholder' => '05XX XXX XX XX',
                'is_required' => 1,
                'order' => 2
            ],
            [
                'form_id' => $formId,
                'field_type' => 'email',
                'field_name' => 'email',
                'field_label' => 'E-posta',
                'placeholder' => 'ornek@email.com',
                'is_required' => 0,
                'order' => 3
            ],
            [
                'form_id' => $formId,
                'field_type' => 'select',
                'field_name' => 'property_type',
                'field_label' => 'Emlak Tipi',
                'placeholder' => 'Seçiniz',
                'is_required' => 0,
                'options' => [
                    'Satılık Daire',
                    'Kiralık Daire',
                    'Satılık Villa',
                    'Kiralık Villa',
                    'Satılık Arsa',
                    'Kiralık Arsa',
                    'Satılık İşyeri',
                    'Kiralık İşyeri'
                ],
                'order' => 4
            ],
            [
                'form_id' => $formId,
                'field_type' => 'text',
                'field_name' => 'location',
                'field_label' => 'Lokasyon',
                'placeholder' => 'İlçe, Mahalle',
                'is_required' => 0,
                'order' => 5
            ],
            [
                'form_id' => $formId,
                'field_type' => 'text',
                'field_name' => 'budget',
                'field_label' => 'Bütçe',
                'placeholder' => 'Örn: 500.000 - 1.000.000 TL',
                'is_required' => 0,
                'order' => 6
            ],
            [
                'form_id' => $formId,
                'field_type' => 'select',
                'field_name' => 'room_count',
                'field_label' => 'Oda Sayısı',
                'placeholder' => 'Seçiniz',
                'is_required' => 0,
                'options' => [
                    '1+0',
                    '1+1',
                    '2+1',
                    '3+1',
                    '4+1',
                    '5+1',
                    '6+1',
                    'Daha fazla'
                ],
                'order' => 7
            ],
            [
                'form_id' => $formId,
                'field_type' => 'textarea',
                'field_name' => 'message',
                'field_label' => 'Mesajınız',
                'placeholder' => 'Eklemek istediğiniz bilgiler...',
                'is_required' => 0,
                'order' => 8
            ]
        ];
        
        // Field'ları ekle
        foreach ($fields as $fieldData) {
            try {
                $this->fieldModel->createField($fieldData);
            } catch (Exception $e) {
                error_log("CRM Template field creation error: " . $e->getMessage());
            }
        }
        
        $_SESSION['form_message'] = 'CRM Lead Formu şablonu başarıyla oluşturuldu! Form alanlarını düzenleyebilirsiniz.';
        $_SESSION['form_message_type'] = 'success';
        
        $this->redirect(admin_url('forms/edit/' . $formId));
    }
    
    /**
     * Form düzenleme formu
     */
    public function edit($id) {
        $this->checkAuth();
        
        $form = $this->formModel->findWithFields($id);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms'));
        }
        
        $data = [
            'title' => 'Form Düzenle',
            'user' => get_logged_in_user(),
            'form' => $form,
            'fieldTypes' => FormField::getFieldTypes(),
            'message' => $_SESSION['form_message'] ?? null,
            'messageType' => $_SESSION['form_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['form_message']);
        unset($_SESSION['form_message_type']);
        
        $this->view('admin/forms/edit', $data);
    }
    
    /**
     * Form güncelleme (POST)
     */
    public function update($id) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(admin_url('forms'));
        }
        
        $form = $this->formModel->find($id);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms'));
        }
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'success_message' => $_POST['success_message'] ?? 'Formunuz başarıyla gönderildi!',
            'redirect_url' => $_POST['redirect_url'] ?? '',
            'email_notification' => isset($_POST['email_notification']) ? 1 : 0,
            'notification_email' => $_POST['notification_email'] ?? '',
            'email_subject' => $_POST['email_subject'] ?? '',
            'submit_button_text' => $_POST['submit_button_text'] ?? 'Gönder',
            'submit_button_color' => $_POST['submit_button_color'] ?? '#137fec',
            'form_style' => $_POST['form_style'] ?? 'default',
            'layout' => $_POST['layout'] ?? 'vertical',
            'status' => isset($_POST['status']) && $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];
        
        // Validasyon
        if (empty($data['name'])) {
            $_SESSION['form_message'] = 'Form adı zorunludur!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms/edit/' . $id));
        }
        
        $this->formModel->updateForm($id, $data);
        
        $_SESSION['form_message'] = 'Form başarıyla güncellendi!';
        $_SESSION['form_message_type'] = 'success';
        
        $this->redirect(admin_url('forms/edit/' . $id));
    }
    
    /**
     * Form silme
     */
    public function delete($id) {
        $this->checkAuth();
        
        $form = $this->formModel->find($id);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
        } else {
            $this->formModel->deleteForm($id);
            $_SESSION['form_message'] = 'Form başarıyla silindi!';
            $_SESSION['form_message_type'] = 'success';
        }
        
        $this->redirect(admin_url('forms'));
    }
    
    /**
     * Form durumunu değiştir
     */
    public function toggleStatus($id) {
        $this->checkAuth();
        
        $form = $this->formModel->find($id);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
        } else {
            if ($form['status'] === 'active') {
                $this->formModel->setInactive($id);
                $_SESSION['form_message'] = 'Form pasifleştirildi!';
            } else {
                $this->formModel->setActive($id);
                $_SESSION['form_message'] = 'Form aktifleştirildi!';
            }
            $_SESSION['form_message_type'] = 'success';
        }
        
        $this->redirect(admin_url('forms'));
    }
    
    // ==================== ALAN İŞLEMLERİ ====================
    
    /**
     * Alan ekleme (AJAX)
     */
    public function addField() {
        try {
            $this->checkAuth();
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
                return;
            }
            
            $formId = (int)($_POST['form_id'] ?? 0);
            
            if (!$formId || !$this->formModel->find($formId)) {
                $this->json(['success' => false, 'message' => 'Form bulunamadı'], 404);
                return;
            }
            
            $data = [
                'form_id' => $formId,
                'type' => $_POST['type'] ?? 'text',
                'label' => $_POST['label'] ?? 'Yeni Alan',
                'name' => $_POST['name'] ?? '',
                'placeholder' => $_POST['placeholder'] ?? '',
                'default_value' => $_POST['default_value'] ?? '',
                'required' => isset($_POST['required']) ? 1 : 0,
                'css_class' => $_POST['css_class'] ?? '',
                'width' => $_POST['width'] ?? 'full',
                'help_text' => $_POST['help_text'] ?? '',
                'status' => 'active'
            ];
            
            // Seçenekleri işle (select, checkbox, radio için)
            if (isset($_POST['options']) && is_array($_POST['options'])) {
                $data['options'] = $_POST['options'];
            }
            
            // Validasyon kurallarını işle
            if (isset($_POST['validation']) && is_array($_POST['validation'])) {
                $data['validation'] = $_POST['validation'];
            }
            
            $fieldId = $this->fieldModel->createField($data);
            
            $field = $this->fieldModel->findDecoded($fieldId);
            
            $this->json([
                'success' => true,
                'message' => 'Alan başarıyla eklendi',
                'field' => $field
            ]);
        } catch (Exception $e) {
            error_log("FormController::addField() Error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Alan eklenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        } catch (PDOException $e) {
            error_log("FormController::addField() SQL Error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Veritabanı hatası: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Alan güncelleme (AJAX)
     */
    public function updateField($fieldId) {
        try {
            $this->checkAuth();
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
                return;
            }
            
            $field = $this->fieldModel->find($fieldId);
            
            if (!$field) {
                $this->json(['success' => false, 'message' => 'Alan bulunamadı'], 404);
                return;
            }
            
            $data = [];
            
            // Sadece gönderilen alanları güncelle
            $allowedFields = [
                'type', 'label', 'name', 'placeholder', 'default_value',
                'required', 'css_class', 'width', 'help_text', 'status', 'order'
            ];
            
            foreach ($allowedFields as $fieldName) {
                if (isset($_POST[$fieldName])) {
                    if (in_array($fieldName, ['required', 'order'])) {
                        $data[$fieldName] = (int)$_POST[$fieldName];
                    } else {
                        $data[$fieldName] = $_POST[$fieldName];
                    }
                }
            }
            
            // Seçenekleri işle
            if (isset($_POST['options']) && is_array($_POST['options'])) {
                $data['options'] = $_POST['options'];
            }
            
            // Validasyon kurallarını işle
            if (isset($_POST['validation']) && is_array($_POST['validation'])) {
                $data['validation'] = $_POST['validation'];
            }
            
            $this->fieldModel->updateField($fieldId, $data);
            
            $updatedField = $this->fieldModel->findDecoded($fieldId);
            
            $this->json([
                'success' => true,
                'message' => 'Alan başarıyla güncellendi',
                'field' => $updatedField
            ]);
        } catch (Exception $e) {
            error_log("FormController::updateField() Error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Alan güncellenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        } catch (PDOException $e) {
            error_log("FormController::updateField() SQL Error: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Veritabanı hatası: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Alan silme (AJAX)
     */
    public function deleteField($fieldId) {
        $this->checkAuth();
        
        $field = $this->fieldModel->find($fieldId);
        
        if (!$field) {
            $this->json(['success' => false, 'message' => 'Alan bulunamadı'], 404);
        }
        
        $this->fieldModel->delete($fieldId);
        
        $this->json([
            'success' => true,
            'message' => 'Alan başarıyla silindi'
        ]);
    }
    
    /**
     * Alan sıralaması güncelleme (AJAX)
     */
    public function updateFieldOrder() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $fields = json_decode($_POST['fields'] ?? '[]', true);
        
        if (empty($fields) || !is_array($fields)) {
            $this->json(['success' => false, 'message' => 'Geçersiz alan listesi'], 400);
        }
        
        $this->fieldModel->updateOrder($fields);
        
        $this->json([
            'success' => true,
            'message' => 'Sıralama başarıyla güncellendi'
        ]);
    }
    
    /**
     * Alan verisini getir (AJAX)
     */
    public function getField($fieldId) {
        $this->checkAuth();
        
        $field = $this->fieldModel->findDecoded($fieldId);
        
        if (!$field) {
            $this->json(['success' => false, 'message' => 'Alan bulunamadı'], 404);
        }
        
        $this->json([
            'success' => true,
            'field' => $field
        ]);
    }
    
    // ==================== GÖNDERİM İŞLEMLERİ ====================
    
    /**
     * Gönderimler listesi
     */
    public function submissions($formId) {
        $this->checkAuth();
        
        $form = $this->formModel->findWithFields($formId);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms'));
        }
        
        $status = $_GET['status'] ?? null;
        $page = max(1, (int)($_GET['p'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $submissions = $this->submissionModel->getAllByFormId($formId, $status, $perPage, $offset);
        $totalCount = $this->submissionModel->countByFormId($formId, $status);
        $totalPages = ceil($totalCount / $perPage);
        
        $stats = $this->formModel->getStats($formId);
        
        $data = [
            'title' => 'Form Gönderimleri - ' . $form['name'],
            'user' => get_logged_in_user(),
            'form' => $form,
            'submissions' => $submissions,
            'stats' => $stats,
            'currentStatus' => $status,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'message' => $_SESSION['form_message'] ?? null,
            'messageType' => $_SESSION['form_message_type'] ?? null
        ];
        
        // Session mesajlarını temizle
        unset($_SESSION['form_message']);
        unset($_SESSION['form_message_type']);
        
        $this->view('admin/forms/submissions', $data);
    }
    
    /**
     * Gönderim detayı görüntüle (AJAX)
     */
    public function viewSubmission($submissionId) {
        $this->checkAuth();
        
        $submission = $this->submissionModel->findDecoded($submissionId);
        
        if (!$submission) {
            $this->json(['success' => false, 'message' => 'Gönderim bulunamadı'], 404);
        }
        
        // Form bilgilerini de getir
        $form = $this->formModel->findWithFields($submission['form_id']);
        
        // Eğer yeni ise okundu olarak işaretle
        if ($submission['status'] === 'new') {
            $this->submissionModel->markAsRead($submissionId);
            $submission['status'] = 'read';
        }
        
        $this->json([
            'success' => true,
            'submission' => $submission,
            'form' => $form
        ]);
    }
    
    /**
     * Gönderim durumunu güncelle (AJAX)
     */
    public function updateSubmissionStatus($submissionId) {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $submission = $this->submissionModel->find($submissionId);
        
        if (!$submission) {
            $this->json(['success' => false, 'message' => 'Gönderim bulunamadı'], 404);
        }
        
        $status = $_POST['status'] ?? '';
        $validStatuses = ['new', 'read', 'spam', 'archived'];
        
        if (!in_array($status, $validStatuses)) {
            $this->json(['success' => false, 'message' => 'Geçersiz durum'], 400);
        }
        
        $this->submissionModel->updateStatus($submissionId, $status);
        
        $this->json([
            'success' => true,
            'message' => 'Durum başarıyla güncellendi'
        ]);
    }
    
    /**
     * Gönderim silme (AJAX)
     */
    public function deleteSubmission($submissionId) {
        $this->checkAuth();
        
        $submission = $this->submissionModel->find($submissionId);
        
        if (!$submission) {
            $this->json(['success' => false, 'message' => 'Gönderim bulunamadı'], 404);
        }
        
        $this->submissionModel->delete($submissionId);
        
        $this->json([
            'success' => true,
            'message' => 'Gönderim başarıyla silindi'
        ]);
    }
    
    /**
     * Toplu gönderim işlemi (AJAX)
     */
    public function bulkSubmissionAction() {
        $this->checkAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
        }
        
        $ids = json_decode($_POST['ids'] ?? '[]', true);
        $action = $_POST['action'] ?? '';
        
        if (empty($ids) || !is_array($ids)) {
            $this->json(['success' => false, 'message' => 'Gönderim seçilmedi'], 400);
        }
        
        switch ($action) {
            case 'delete':
                $this->submissionModel->deleteMultiple($ids);
                $message = count($ids) . ' gönderim silindi';
                break;
            case 'mark_read':
                $this->submissionModel->updateMultipleStatus($ids, 'read');
                $message = count($ids) . ' gönderim okundu olarak işaretlendi';
                break;
            case 'mark_spam':
                $this->submissionModel->updateMultipleStatus($ids, 'spam');
                $message = count($ids) . ' gönderim spam olarak işaretlendi';
                break;
            case 'archive':
                $this->submissionModel->updateMultipleStatus($ids, 'archived');
                $message = count($ids) . ' gönderim arşivlendi';
                break;
            default:
                $this->json(['success' => false, 'message' => 'Geçersiz işlem'], 400);
        }
        
        $this->json([
            'success' => true,
            'message' => $message
        ]);
    }
    
    /**
     * Gönderimleri CSV olarak dışa aktar
     */
    public function exportSubmissions($formId) {
        $this->checkAuth();
        
        $form = $this->formModel->find($formId);
        
        if (!$form) {
            $_SESSION['form_message'] = 'Form bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms'));
        }
        
        $exportData = $this->submissionModel->getExportData($formId);
        
        if (empty($exportData)) {
            $_SESSION['form_message'] = 'Dışa aktarılacak veri bulunamadı!';
            $_SESSION['form_message_type'] = 'error';
            $this->redirect(admin_url('forms/submissions/' . $formId));
        }
        
        // CSV başlıkları
        $headers = array_keys($exportData[0]);
        
        // CSV dosyası oluştur
        $filename = 'form_submissions_' . $form['slug'] . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // BOM for UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Başlıkları yaz
        fputcsv($output, $headers);
        
        // Verileri yaz
        foreach ($exportData as $row) {
            fputcsv($output, array_values($row));
        }
        
        fclose($output);
        exit;
    }
    
    // ==================== ÖNİZLEME ====================
    
    /**
     * Form önizleme (Tam sayfa)
     */
    public function preview($formId) {
        $this->checkAuth();
        
        $form = $this->formModel->findWithFields($formId);
        
        if (!$form) {
            die('Form bulunamadı');
        }
        
        $data = [
            'form' => $form
        ];
        
        $this->view('admin/forms/preview', $data);
    }
    
    /**
     * Form önizleme HTML'i getir (AJAX - editor içi önizleme için)
     */
    public function getPreviewHtml($formId) {
        $this->checkAuth();
        
        $form = $this->formModel->findWithFields($formId);
        
        if (!$form) {
            $this->json(['success' => false, 'message' => 'Form bulunamadı'], 404);
        }
        
        // Önizleme HTML'ini oluştur
        ob_start();
        $this->renderFormPreview($form);
        $html = ob_get_clean();
        
        $this->json([
            'success' => true,
            'html' => $html
        ]);
    }
    
    /**
     * Form önizleme render
     */
    private function renderFormPreview($form) {
        $styleClass = 'form-style-' . ($form['form_style'] ?? 'default');
        $layoutClass = 'form-layout-' . ($form['layout'] ?? 'vertical');
        ?>
        <form class="cms-form <?php echo esc_attr($styleClass); ?> <?php echo esc_attr($layoutClass); ?>" onsubmit="return false;">
            <?php if (!empty($form['description'])): ?>
                <div class="form-description"><?php echo esc_html($form['description']); ?></div>
            <?php endif; ?>
            
            <div class="form-fields">
                <?php if (!empty($form['fields'])): ?>
                    <?php foreach ($form['fields'] as $field): ?>
                        <?php if ($field['status'] !== 'active') continue; ?>
                        <?php $this->renderField($field); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-fields-message">
                        <p>Henüz form alanı eklenmemiş.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($form['fields'])): ?>
                <div class="form-submit">
                    <button type="submit" class="submit-button" style="background-color: <?php echo esc_attr($form['submit_button_color'] ?? '#137fec'); ?>">
                        <?php echo esc_html($form['submit_button_text'] ?? 'Gönder'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </form>
        <?php
    }
    
    /**
     * Tek bir form alanını render eder
     */
    private function renderField($field) {
        // Field type'ı kontrol et - hem 'type' hem 'field_type' olabilir, trim et
        $fieldType = trim($field['type'] ?? $field['field_type'] ?? 'text');
        
        $widthClass = 'field-width-' . ($field['width'] ?? 'full');
        $requiredClass = $field['required'] ? 'field-required' : '';
        $customClass = $field['css_class'] ?? '';
        
        // Layout elemanları
        if (in_array($fieldType, ['heading', 'paragraph', 'divider'])) {
            $this->renderLayoutElement($field);
            return;
        }
        
        ?>
        <div class="form-field <?php echo esc_attr($widthClass); ?> <?php echo esc_attr($requiredClass); ?> <?php echo esc_attr($customClass); ?>" data-field-type="<?php echo esc_attr($fieldType); ?>">
            <?php if ($fieldType !== 'hidden'): ?>
                <label class="field-label">
                    <?php echo esc_html($field['label']); ?>
                    <?php if ($field['required']): ?>
                        <span class="required-mark">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <div class="field-input">
                <?php
                switch ($fieldType) {
                    case 'text':
                    case 'email':
                    case 'phone':
                    case 'tel':
                    case 'number':
                    case 'date':
                    case 'time':
                    case 'datetime':
                        $inputType = $fieldType;
                        if ($fieldType === 'phone' || $fieldType === 'tel') $inputType = 'tel';
                        if ($fieldType === 'datetime') $inputType = 'datetime-local';
                        ?>
                        <input type="<?php echo esc_attr($inputType); ?>" 
                               name="<?php echo esc_attr($field['name']); ?>" 
                               placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                               value="<?php echo esc_attr($field['default_value'] ?? ''); ?>"
                               <?php echo $field['required'] ? 'required' : ''; ?>>
                        <?php
                        break;
                        
                    case 'textarea':
                        ?>
                        <textarea name="<?php echo esc_attr($field['name']); ?>" 
                                  placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>"
                                  rows="4"
                                  <?php echo $field['required'] ? 'required' : ''; ?>><?php echo esc_html($field['default_value'] ?? ''); ?></textarea>
                        <?php
                        break;
                        
                    case 'select':
                        ?>
                        <select name="<?php echo esc_attr($field['name']); ?>" <?php echo $field['required'] ? 'required' : ''; ?>>
                            <option value=""><?php echo esc_html($field['placeholder'] ?? 'Seçiniz...'); ?></option>
                            <?php if (!empty($field['options'])): ?>
                                <?php foreach ($field['options'] as $option): ?>
                                    <option value="<?php echo esc_attr($option['value'] ?? $option); ?>" 
                                            <?php echo ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'selected' : ''; ?>>
                                        <?php echo esc_html($option['label'] ?? $option); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <?php
                        break;
                        
                    case 'checkbox':
                        ?>
                        <div class="checkbox-group">
                            <?php if (!empty($field['options'])): ?>
                                <?php foreach ($field['options'] as $i => $option): ?>
                                    <label class="checkbox-label">
                                        <input type="checkbox" 
                                               name="<?php echo esc_attr($field['name']); ?>[]" 
                                               value="<?php echo esc_attr($option['value'] ?? $option); ?>">
                                        <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php
                        break;
                        
                    case 'radio':
                        ?>
                        <div class="radio-group">
                            <?php if (!empty($field['options'])): ?>
                                <?php foreach ($field['options'] as $i => $option): ?>
                                    <label class="radio-label">
                                        <input type="radio" 
                                               name="<?php echo esc_attr($field['name']); ?>" 
                                               value="<?php echo esc_attr($option['value'] ?? $option); ?>"
                                               <?php echo ($field['default_value'] ?? '') === ($option['value'] ?? $option) ? 'checked' : ''; ?>>
                                        <span><?php echo esc_html($option['label'] ?? $option); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <?php
                        break;
                        
                    case 'file':
                        ?>
                        <input type="file" 
                               name="<?php echo esc_attr($field['name']); ?>" 
                               <?php echo $field['required'] ? 'required' : ''; ?>>
                        <?php
                        break;
                        
                    case 'hidden':
                        ?>
                        <input type="hidden" 
                               name="<?php echo esc_attr($field['name']); ?>" 
                               value="<?php echo esc_attr($field['default_value'] ?? ''); ?>">
                        <?php
                        break;
                }
                ?>
            </div>
            
            <?php if (!empty($field['help_text'])): ?>
                <div class="field-help"><?php echo esc_html($field['help_text']); ?></div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Layout elemanlarını render eder
     */
    private function renderLayoutElement($field) {
        switch ($field['type']) {
            case 'heading':
                ?>
                <div class="form-heading">
                    <h3><?php echo esc_html($field['label']); ?></h3>
                </div>
                <?php
                break;
                
            case 'paragraph':
                ?>
                <div class="form-paragraph">
                    <p><?php echo nl2br(esc_html($field['default_value'] ?? $field['label'])); ?></p>
                </div>
                <?php
                break;
                
            case 'divider':
                ?>
                <div class="form-divider">
                    <hr>
                </div>
                <?php
                break;
        }
    }
    
    // ==================== FRONTEND FORM GÖNDERİMİ ====================
    
    /**
     * Frontend form gönderimi (AJAX)
     */
    public function submit() {
        try {
            // Output buffering başlat (PHP hatalarını yakalamak için)
            ob_start();
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Geçersiz istek'], 400);
                return;
            }
            
            $formId = (int)($_POST['_form_id'] ?? 0);
            
            if (!$formId) {
                $this->json(['success' => false, 'message' => 'Form ID gerekli'], 400);
                return;
            }
            
            $form = $this->formModel->findWithFields($formId);
            
            if (!$form || $form['status'] !== 'active') {
                $this->json(['success' => false, 'message' => 'Form bulunamadı veya aktif değil'], 404);
                return;
            }
            
            // Honeypot spam koruması
            $honeypotEnabled = get_option('honeypot_enabled', 1);
            if ($honeypotEnabled) {
                // Honeypot alanı doldurulmuşsa spam olarak işaretle
                $honeypotValue = trim($_POST['website_url'] ?? '');
                if (!empty($honeypotValue)) {
                    // Spam tespit edildi - sessizce reddet (botlara ipucu vermemek için)
                    error_log('Spam form gönderimi tespit edildi (Honeypot): Form ID ' . $formId . ', IP: ' . ($_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor'));
                    $this->json([
                        'success' => false,
                        'message' => 'Form gönderimi başarısız. Lütfen tekrar deneyin.'
                    ], 400);
                    return;
                }
            }
            
            // Form verilerini topla
            $formData = [];
            $errors = [];
            
            foreach ($form['fields'] as $field) {
                // Status kontrolü - hem status hem is_active kontrol et
                $isActive = false;
                if (isset($field['status'])) {
                    $isActive = ($field['status'] === 'active');
                } elseif (isset($field['is_active'])) {
                    $isActive = ($field['is_active'] == 1 || $field['is_active'] === true);
                } else {
                    // Varsayılan olarak aktif kabul et
                    $isActive = true;
                }
                if (!$isActive) continue;
                
                // Field type'ı kontrol et - hem 'type' hem 'field_type' olabilir, trim et
                $fieldType = trim($field['type'] ?? $field['field_type'] ?? 'text');
                
                // Field type boş ise text olarak kabul et
                if (empty($fieldType)) {
                    $fieldType = 'text';
                }
                
                if (in_array($fieldType, ['heading', 'paragraph', 'divider'])) continue;
                
                $value = $_POST[$field['name']] ?? null;
                
                // String değerleri trim et
                if (is_string($value)) {
                    $value = trim($value);
                }
                
                // Zorunlu alan kontrolü
                $isRequired = (int)($field['required'] ?? 0) === 1;
                if ($isRequired && (empty($value) || (is_string($value) && trim($value) === ''))) {
                    $errors[$field['name']] = $field['label'] . ' alanı zorunludur.';
                    continue;
                }
                
                // Tip bazlı validasyon - sadece değer varsa ve boş değilse
                // ÖNEMLİ: Field name kontrolü önce gelsin, field type ikinci sırada
                if (!empty($value) && is_string($value) && trim($value) !== '') {
                    $fieldName = strtolower(trim($field['name'] ?? ''));
                    $fieldTypeLower = strtolower(trim($fieldType));
                    
                    // E-posta validasyonu - field name "email" ise veya field type "email" ise
                    if ($fieldName === 'email' || $fieldName === 'e-posta' || $fieldTypeLower === 'email') {
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field['name']] = 'Geçerli bir e-posta adresi giriniz.';
                        }
                    }
                    // Telefon validasyonu - field name "phone" veya "telefon" ise veya field type "phone"/"tel" ise
                    elseif ($fieldName === 'phone' || $fieldName === 'telefon' || $fieldTypeLower === 'phone' || $fieldTypeLower === 'tel') {
                        // Telefon numarası validasyonu - esnek format kontrolü
                        // Sadece rakamları say (boşluk, tire, parantez, + gibi karakterleri çıkar)
                        $digitOnly = preg_replace('/[^0-9]/', '', $value);
                        $digitCount = strlen($digitOnly);
                        
                        // Minimum 10 rakam olmalı (Türk telefon numaraları için)
                        if ($digitCount < 10) {
                            $errors[$field['name']] = 'Geçerli bir telefon numarası giriniz. (En az 10 rakam)';
                        }
                    }
                    // Sayı validasyonu - sadece field type kontrol et
                    elseif ($fieldTypeLower === 'number') {
                        if (!is_numeric($value)) {
                            $errors[$field['name']] = 'Geçerli bir sayı giriniz.';
                        }
                    }
                }
                
                // Checkbox array'ini birleştir
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                
                $formData[$field['name']] = $value;
            }
            
            // Hata varsa geri dön
            if (!empty($errors)) {
                $this->json([
                    'success' => false,
                    'message' => 'Lütfen hataları düzeltiniz.',
                    'errors' => $errors
                ], 400);
                return;
            }
            
            // Gönderimi kaydet
            try {
                $submissionId = $this->submissionModel->createSubmission($formId, $formData);
            } catch (Exception $e) {
                error_log('Form submission create error: ' . $e->getMessage());
                throw $e;
            }
            
            // Gönderim sayısını artır
            try {
                $this->formModel->incrementSubmissionCount($formId);
            } catch (Exception $e) {
                // Sayaç hatası kritik değil, devam et
                error_log('Form increment count error: ' . $e->getMessage());
            }
            
            // Form submission hook'unu tetikle (CRM modülü için)
            if (function_exists('do_action')) {
                $submission = $this->submissionModel->findDecoded($submissionId);
                if ($submission) {
                    do_action('form_submitted', $formId, $submission);
                }
            }
            
            // E-posta bildirimi gönder
            if ($form['email_notification'] && !empty($form['notification_email'])) {
                $this->sendNotificationEmail($form, $formData);
            }
            
            // Başarılı yanıt
            $response = [
                'success' => true,
                'message' => $form['success_message'] ?? 'Formunuz başarıyla gönderildi!'
            ];
        
            // Yönlendirme URL'i varsa ekle
            if (!empty($form['redirect_url'])) {
                $response['redirect'] = $form['redirect_url'];
            }
            
            // Output buffer'ı temizle (PHP hataları varsa)
            ob_end_clean();
            
            $this->json($response);
        } catch (Exception $e) {
            // Output buffer'ı temizle
            ob_end_clean();
            
            error_log('Form submit error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->json([
                'success' => false,
                'message' => 'Form gönderilirken bir hata oluştu. Lütfen tekrar deneyin.'
            ], 500);
        } catch (Error $e) {
            // Output buffer'ı temizle
            ob_end_clean();
            
            error_log('Form submit fatal error: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            
            $this->json([
                'success' => false,
                'message' => 'Form gönderilirken bir hata oluştu. Lütfen tekrar deneyin.'
            ], 500);
        }
    }
    
    /**
     * E-posta bildirimi gönderir
     */
    private function sendNotificationEmail($form, $formData) {
        $to = $form['notification_email'];
        $subject = $form['email_subject'] ?? 'Yeni Form Gönderimi: ' . $form['name'];
        
        // Mailer sınıfını yükle
        require_once __DIR__ . '/../../core/Mailer.php';
        
        // Mailer sınıfını kullan
        $mailer = new Mailer();
        
        // SMTP yapılandırılmamışsa, sessizce çık (hata gösterme)
        if (!$mailer->isConfigured()) {
            error_log('SMTP ayarları yapılandırılmamış. Form bildirimi gönderilemedi.');
            return;
        }
        
        // HTML formatında e-posta içeriği oluştur
        $htmlContent = $this->buildEmailHtml($form, $formData, $subject);
        
        // Plain text versiyonu da oluştur (fallback için)
        $textContent = "Yeni bir form gönderimi alındı.\n\n";
        $textContent .= "Form: " . $form['name'] . "\n";
        $textContent .= "Tarih: " . date('d.m.Y H:i:s') . "\n\n";
        $textContent .= "Gönderilen Veriler:\n";
        $textContent .= "-------------------\n";
        
        foreach ($form['fields'] as $field) {
            if (in_array($field['type'], ['heading', 'paragraph', 'divider'])) continue;
            
            $value = $formData[$field['name']] ?? '-';
            $textContent .= $field['label'] . ": " . $value . "\n";
        }
        
        $textContent .= "\n-------------------\n";
        $textContent .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor') . "\n";
        
        // E-postayı gönder
        $success = $mailer->send($to, $subject, $htmlContent, [
            'isHtml' => true
        ]);
        
        // Hata durumunda log'a kaydet
        if (!$success) {
            error_log('Form bildirimi gönderilemedi: ' . $mailer->getLastError());
        }
    }
    
    /**
     * E-posta için HTML içeriği oluşturur
     */
    private function buildEmailHtml($form, $formData, $subject) {
        $siteName = get_option('seo_title', 'CMS');
        $formName = htmlspecialchars($form['name'], ENT_QUOTES, 'UTF-8');
        $subjectEscaped = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
        $date = date('d.m.Y H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor';
        
        $fieldsHtml = '';
        foreach ($form['fields'] as $field) {
            if (in_array($field['type'], ['heading', 'paragraph', 'divider'])) continue;
            
            $label = htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8');
            $value = $formData[$field['name']] ?? '-';
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            
            $fieldsHtml .= "<tr>";
            $fieldsHtml .= "<td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: 600; color: #333;'>" . $label . "</td>";
            $fieldsHtml .= "<td style='padding: 8px; border-bottom: 1px solid #eee; color: #666;'>" . nl2br($value) . "</td>";
            $fieldsHtml .= "</tr>";
        }
        
        return <<<HTML
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subjectEscaped}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 32px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .email-header {
            text-align: center;
            padding-bottom: 24px;
            border-bottom: 1px solid #eee;
            margin-bottom: 24px;
        }
        .email-header h1 {
            color: #137fec;
            font-size: 24px;
            margin: 0;
        }
        .email-content {
            padding: 16px 0;
        }
        .form-info {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: 6px;
            margin-bottom: 24px;
        }
        .form-info p {
            margin: 4px 0;
            color: #666;
        }
        .form-data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        .form-data-table th {
            background-color: #137fec;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        .form-data-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .form-data-table tr:last-child td {
            border-bottom: none;
        }
        .email-footer {
            text-align: center;
            padding-top: 24px;
            border-top: 1px solid #eee;
            margin-top: 24px;
            color: #888;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>{$siteName}</h1>
        </div>
        <div class="email-content">
            <h2 style="color: #137fec; margin-top: 0;">Yeni Form Gönderimi</h2>
            
            <div class="form-info">
                <p><strong>Form:</strong> {$formName}</p>
                <p><strong>Tarih:</strong> {$date}</p>
                <p><strong>IP Adresi:</strong> {$ip}</p>
            </div>
            
            <h3 style="color: #333; margin-top: 24px; margin-bottom: 12px;">Gönderilen Veriler:</h3>
            <table class="form-data-table">
                <thead>
                    <tr>
                        <th>Alan</th>
                        <th>Değer</th>
                    </tr>
                </thead>
                <tbody>
                    {$fieldsHtml}
                </tbody>
            </table>
        </div>
        <div class="email-footer">
            <p>Bu e-posta {$siteName} tarafından gönderilmiştir.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}

