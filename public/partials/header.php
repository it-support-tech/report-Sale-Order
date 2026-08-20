<?php
/** @var string $pageTitle */
$currentPath = $_SERVER['SCRIPT_NAME'];
function nav_active(string $needle, string $path): string
{
    return str_contains($path, $needle) ? 'active' : '';
}
?>
<!doctype html>
<html lang="lo">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'NTP Trading Petroleum') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell d-flex">
    <aside class="sidebar no-print" id="sidebar">
        <a class="sidebar-brand" href="<?= url('/') ?>">
            <img src="<?= url('/logo.php') ?>" alt="logo">
            <span>NTP Trading<br>Petroleum</span>
        </a>
        <nav class="sidebar-nav">
            <a class="nav-link <?= nav_active('/orders', $currentPath) ?>" href="<?= url('/orders/') ?>">Sales Order</a>
            <a class="nav-link <?= nav_active('/customers', $currentPath) ?>" href="<?= url('/customers/') ?>">ຂໍ້ມູນລູກຄ້າ</a>
            <a class="nav-link <?= nav_active('/banks', $currentPath) ?>" href="<?= url('/banks/') ?>">ຂໍ້ມູນທະນາຄານ</a>
            <a class="nav-link <?= nav_active('/products', $currentPath) ?>" href="<?= url('/products/') ?>">ຂໍ້ມູນສິນຄ້າ</a>
        </nav>
    </aside>
    <div class="sidebar-backdrop no-print" id="sidebarBackdrop"></div>

    <div class="app-main flex-grow-1">
        <nav class="topbar no-print sticky-top">
            <button class="sidebar-toggle-btn d-lg-none" type="button" id="sidebarToggle" aria-label="ເປີດ/ປິດ ເມນູ">&#9776;</button>
            <span class="topbar-title"><?= e($pageTitle ?? '') ?></span>
        </nav>
        <div class="container-fluid px-4 pt-4 pb-4">
            <?php foreach (flash_messages() as $flash): ?>
                <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show no-print" role="alert">
                    <?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endforeach; ?>
