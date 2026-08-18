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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 no-print">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= url('/') ?>">NTP Trading Petroleum</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= nav_active('/orders', $currentPath) ?>" href="<?= url('/orders/') ?>">Sales Order</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('/customers', $currentPath) ?>" href="<?= url('/customers/') ?>">ຂໍ້ມູນລູກຄ້າ</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('/banks', $currentPath) ?>" href="<?= url('/banks/') ?>">ຂໍ້ມູນທະນາຄານ</a></li>
                <li class="nav-item"><a class="nav-link <?= nav_active('/products', $currentPath) ?>" href="<?= url('/products/') ?>">ຂໍ້ມູນສິນຄ້າ</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container-fluid px-4">
    <?php foreach (flash_messages() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show no-print" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
</div>
