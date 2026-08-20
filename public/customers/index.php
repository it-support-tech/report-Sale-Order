<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$pageTitle = 'ຂໍ້ມູນລູກຄ້າ';
$customers = customer_all();

require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-end mb-3">
    <a href="<?= url('/customers/form.php') ?>" class="btn btn-primary">+ ເພີ່ມລູກຄ້າ</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>ລະຫັດ</th>
                <th>ບໍລິສັດ</th>
                <th>Contact Person</th>
                <th>Tel</th>
                <th>Tax ID</th>
                <th>Payment Term</th>
                <th class="table-actions"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><?= e($customer['code']) ?></td>
                    <td><?= e($customer['company_name']) ?></td>
                    <td><?= e($customer['contact_person']) ?></td>
                    <td><?= e($customer['phone']) ?></td>
                    <td><?= e($customer['tax_id']) ?></td>
                    <td><?= e($customer['payment_term']) ?></td>
                    <td class="table-actions">
                        <a href="<?= url('/customers/form.php') ?>?id=<?= $customer['id'] ?>" class="btn btn-sm btn-outline-primary">ແກ້ໄຂ</a>
                        <form action="<?= url('/customers/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('ລຶບຂໍ້ມູນລູກຄ້ານີ້ບໍ່?');">
                            <input type="hidden" name="id" value="<?= $customer['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">ລຶບ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$customers): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">ຍັງບໍ່ມີຂໍ້ມູນລູກຄ້າ</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
