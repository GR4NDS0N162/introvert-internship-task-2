<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once('Solution.php');

use Task\Solution;

if (isset($_GET['date_field_id']) && isset($_GET['status_lead_id'])) {
    $dateFieldId = $_GET['date_field_id'];
    $statusLeadIds = $_GET['status_lead_id'];

    $sol = new Solution('https://api.s1.yadrocrm.ru/tmp', '23bc075b710da43f0ffb50ff9e889aed');
    $data = $sol->calculateCounts($dateFieldId, $statusLeadIds);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => 'Введите ID доп. поля с датой и ID статуса сделки (несколько).']);
}
