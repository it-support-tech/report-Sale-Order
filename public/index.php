<?php
require_once __DIR__ . '/../src/bootstrap.php';

$pageTitle = 'Dashboard';
$recentOrders = array_slice(sales_order_all(), 0, 10);
$customerCount = count(customer_all());
$productCount = count(product_all());
$orderCount = count(sales_order_all());

require __DIR__ . '/partials/header.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted small">Sales Order ທັງໝົດ</div>
                <div class="fs-2 fw-bold"><?= $orderCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted small">ລູກຄ້າ</div>
                <div class="fs-2 fw-bold"><?= $customerCount ?></div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="text-muted small">ສິນຄ້າ</div>
                <div class="fs-2 fw-bold"><?= $productCount ?></div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Sales Order ຫຼ້າສຸດ</h5>
    <a href="/orders/form.php" class="btn btn-primary">+ ອອກບິນໃໝ່</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>Document No.</th>
                <th>Document Date</th>
                <th>ລູກຄ້າ</th>
                <th>ສາງ</th>
                <th>Currency</th>
                <th class="text-end">Net Total</th>
                <th class="table-actions"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($recentOrders as $order): ?>
                <tr>
                    <td><?= e($order['document_no']) ?></td>
                    <td><?= e($order['document_date']) ?></td>
                    <td><?= e($order['company_name']) ?></td>
                    <td><?= e($order['warehouse_code']) ?></td>
                    <td><?= e($order['currency']) ?></td>
                    <td class="text-end"><?= money($order['net_total']) ?></td>
                    <td class="table-actions">
                        <a href="/orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">ເບິ່ງ</a>
                        <a href="/orders/form.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">ແກ້ໄຂ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recentOrders): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">ຍັງບໍ່ມີ Sales Order</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
