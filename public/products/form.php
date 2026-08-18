<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = $id ? product_find($id) : null;
if ($id && !$product) {
    flash('danger', 'ບໍ່ພົບຂໍ້ມູນສິນຄ້ານີ້');
    redirect('/products/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (trim($data['code'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນລະຫັດສິນຄ້າ';
    }
    if (trim($data['name'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ສິນຄ້າ';
    }

    if (!$errors) {
        try {
            product_save($data, $id);
            flash('success', 'ບັນທຶກຂໍ້ມູນສິນຄ້າສຳເລັດ');
            redirect('/products/');
        } catch (PDOException $e) {
            $errors[] = str_contains($e->getMessage(), 'unique')
                ? 'ລະຫັດສິນຄ້ານີ້ຖືກໃຊ້ແລ້ວ'
                : 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
        }
    }
    $product = $data + ($product ?? []);
}

$pageTitle = $id ? 'ແກ້ໄຂຂໍ້ມູນສິນຄ້າ' : 'ເພີ່ມສິນຄ້າ';
require __DIR__ . '/../partials/header.php';
?>

<h5 class="mb-3"><?= e($pageTitle) ?></h5>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Product Code</label>
                    <input type="text" name="code" class="form-control" value="<?= old($product ?? [], 'code') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">ຊື່ສິນຄ້າ</label>
                    <input type="text" name="name" class="form-control" value="<?= old($product ?? [], 'name') ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">UoM</label>
                    <input type="text" name="uom" class="form-control" value="<?= old($product ?? [], 'uom', 'Liter') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ລາຄາຕໍ່ໜ່ວຍ (ຄ່າຕັ້ງຕົ້ນ)</label>
                    <input type="number" step="0.01" name="default_unit_price" class="form-control" value="<?= old($product ?? [], 'default_unit_price', '0') ?>">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                <a href="/products/" class="btn btn-outline-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
