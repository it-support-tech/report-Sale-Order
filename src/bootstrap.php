<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Bank.php';
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Warehouse.php';
require_once __DIR__ . '/SalesOrder.php';

const CURRENCIES = ['LAK', 'THB', 'USD', 'CNY'];
