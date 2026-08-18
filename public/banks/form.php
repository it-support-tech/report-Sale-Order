<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$bank = $id ? bank_find($id) : null;
if ($id && !$bank) {
    flash('danger', 'ບໍ່ພົບຂໍ້ມູນທະນາຄານນີ້');
    redirect('/banks/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (trim($data['bank_name'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ທະນາຄານ';
    }

    if (!$errors) {
        bank_save($data, $id);
        flash('success', 'ບັນທຶກຂໍ້ມູນທະນາຄານສຳເລັດ');
        redirect('/banks/');
    }
    $bank = $data + ($bank ?? []);
}

$pageTitle = $id ? 'ແກ້ໄຂຂໍ້ມູນທະນາຄານ' : 'ເພີ່ມທະນາຄານ';
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
                <div class="col-12">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="<?= old($bank ?? [], 'bank_name') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number - LAK</label>
                    <input type="text" name="account_lak" class="form-control" value="<?= old($bank ?? [], 'account_lak') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number - THB</label>
                    <input type="text" name="account_thb" class="form-control" value="<?= old($bank ?? [], 'account_thb') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Number - USD</label>
                    <input type="text" name="account_usd" class="form-control" value="<?= old($bank ?? [], 'account_usd') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Swift Code</label>
                    <input type="text" name="swift_code" class="form-control" value="<?= old($bank ?? [], 'swift_code') ?>">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" <?= (($bank['is_active'] ?? true) ? 'checked' : '') ?>>
                        <label for="is_active" class="form-check-label">ໃຊ້ງານຢູ່ (Active)</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                <a href="<?= url('/banks/') ?>" class="btn btn-outline-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
