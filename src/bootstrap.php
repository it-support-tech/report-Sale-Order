<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect the URL prefix the app is served under, so links still work when
// public/ isn't the web server's document root (e.g. deployed in a subfolder).
$publicDir = realpath(__DIR__ . '/../public');
$scriptFile = realpath($_SERVER['SCRIPT_FILENAME']);
$relative = str_replace('\\', '/', ltrim(substr($scriptFile, strlen($publicDir)), '/'));
$scriptName = $_SERVER['SCRIPT_NAME'];
$suffix = '/' . $relative;
define('BASE_PATH', str_ends_with($scriptName, $suffix) ? substr($scriptName, 0, -strlen($suffix)) : '');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Customer.php';
require_once __DIR__ . '/Bank.php';
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/Warehouse.php';
require_once __DIR__ . '/SalesOrder.php';

const CURRENCIES = ['LAK', 'THB', 'USD', 'CNY'];
