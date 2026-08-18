<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$customer = $id ? customer_find($id) : null;
if ($id && !$customer) {
    flash('danger', 'ບໍ່ພົບຂໍ້ມູນລູກຄ້ານີ້');
    redirect('/customers/');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    if (trim($data['code'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນລະຫັດລູກຄ້າ';
    }
    if (trim($data['company_name'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ບໍລິສັດ';
    }

    if (!$errors) {
        try {
            customer_save($data, $id);
            flash('success', 'ບັນທຶກຂໍ້ມູນລູກຄ້າສຳເລັດ');
            redirect('/customers/');
        } catch (PDOException $e) {
            $errors[] = str_contains($e->getMessage(), 'unique')
                ? 'ລະຫັດລູກຄ້ານີ້ຖືກໃຊ້ແລ້ວ'
                : 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
        }
    }
    $customer = $data + ($customer ?? []);
}

$pageTitle = $id ? 'ແກ້ໄຂຂໍ້ມູນລູກຄ້າ' : 'ເພີ່ມລູກຄ້າ';
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
                    <label class="form-label">ລະຫັດລູກຄ້າ (Code)</label>
                    <input type="text" name="code" class="form-control" value="<?= old($customer ?? [], 'code') ?>" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">ຊື່ບໍລິສັດ (Company Name)</label>
                    <input type="text" name="company_name" class="form-control" value="<?= old($customer ?? [], 'company_name') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">ບ້ານ (Village)</label>
                    <input type="text" name="village" class="form-control" value="<?= old($customer ?? [], 'village') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ເມືອງ (District)</label>
                    <input type="text" name="district" class="form-control" value="<?= old($customer ?? [], 'district') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">ແຂວງ (Province)</label>
                    <input type="text" name="province" class="form-control" value="<?= old($customer ?? [], 'province') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control" value="<?= old($customer ?? [], 'contact_person') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tel</label>
                    <input type="text" name="phone" class="form-control" value="<?= old($customer ?? [], 'phone') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fax</label>
                    <input type="text" name="fax" class="form-control" value="<?= old($customer ?? [], 'fax') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Tax ID</label>
                    <input type="text" name="tax_id" class="form-control" value="<?= old($customer ?? [], 'tax_id') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Term</label>
                    <input type="text" name="payment_term" class="form-control" placeholder="e.g. 60 Days" value="<?= old($customer ?? [], 'payment_term') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label">ShipTo Address</label>
                    <textarea name="ship_to_address" class="form-control" rows="2"><?= old($customer ?? [], 'ship_to_address') ?></textarea>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">ບັນທຶກ</button>
                <a href="<?= url('/customers/') ?>" class="btn btn-outline-secondary">ຍົກເລີກ</a>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
