<?php
    $status_title = 'Payment update';
    $status_description = 'The payment moved to status: ' . strtoupper($status ?? 'updated');
    $status_class = 'neutral';
    include __DIR__ . '/status-template.php';
?>
