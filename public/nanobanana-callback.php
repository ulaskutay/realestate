<?php
/**
 * Nano Banana API callback - task tamamlandığında bu URL'ye POST atar.
 * Sadece 200 döndürüyoruz; sonuç için frontend record-info ile poll ediyor.
 */
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true]);
exit;
