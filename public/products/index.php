<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$pageTitle = 'ຂໍ້ມູນສິນຄ້າ';
$products = product_all();

require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">ຂໍ້ມູນສິນຄ້າ</h5>
    <a href="<?= url('/products/form.php') ?>" class="btn btn-primary">+ ເພີ່ມສິນຄ້າ</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>Product Code</th>
                <th>ຊື່ສິນຄ້າ</th>
                <th>UoM</th>
                <th class="text-end">ລາຄາຕໍ່ໜ່ວຍ</th>
                <th class="table-actions"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= e($product['code']) ?></td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e($product['uom']) ?></td>
                    <td class="text-end"><?= money($product['default_unit_price']) ?></td>
                    <td class="table-actions">
                        <a href="<?= url('/products/form.php') ?>?id=<?= $product['id'] ?>" class="btn btn-sm btn-outline-primary">ແກ້ໄຂ</a>
                        <form action="<?= url('/products/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('ລຶບຂໍ້ມູນສິນຄ້ານີ້ບໍ່?');">
                            <input type="hidden" name="id" value="<?= $product['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">ລຶບ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$products): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ຍັງບໍ່ມີຂໍ້ມູນສິນຄ້າ</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
