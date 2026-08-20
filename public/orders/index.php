<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$pageTitle = 'Sales Order';
$orders = sales_order_all();

require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= url('/orders/form.php') ?>" class="btn btn-primary">+ ອອກບິນໃໝ່</a>
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
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= e($order['document_no']) ?></td>
                    <td><?= e($order['document_date']) ?></td>
                    <td><?= e($order['company_name']) ?></td>
                    <td><?= e($order['warehouse_code']) ?></td>
                    <td><?= e($order['currency']) ?></td>
                    <td class="text-end"><?= money($order['net_total']) ?></td>
                    <td class="table-actions">
                        <a href="<?= url('/orders/view.php') ?>?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-secondary">ເບິ່ງ/Print</a>
                        <a href="<?= url('/orders/form.php') ?>?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">ແກ້ໄຂ</a>
                        <form action="<?= url('/orders/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('ລຶບ Sales Order ນີ້ບໍ່?');">
                            <input type="hidden" name="id" value="<?= $order['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">ລຶບ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">ຍັງບໍ່ມີ Sales Order</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
