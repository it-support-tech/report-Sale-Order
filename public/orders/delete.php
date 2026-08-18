<?php
require_once __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    sales_order_delete((int) $_POST['id']);
    flash('success', 'ລຶບ Sales Order ສຳເລັດ');
}

redirect('/orders/');
