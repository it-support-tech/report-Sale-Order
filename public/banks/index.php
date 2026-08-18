<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$pageTitle = 'ຂໍ້ມູນທະນາຄານ';
$banks = bank_all();

require __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">ຂໍ້ມູນທະນາຄານບໍລິສັດ</h5>
    <a href="/banks/form.php" class="btn btn-primary">+ ເພີ່ມທະນາຄານ</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
            <tr>
                <th>Bank Name</th>
                <th>Number - LAK</th>
                <th>Number - THB</th>
                <th>Number - USD</th>
                <th>Swift Code</th>
                <th class="table-actions"></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($banks as $bank): ?>
                <tr>
                    <td><?= e($bank['bank_name']) ?></td>
                    <td><?= e($bank['account_lak']) ?></td>
                    <td><?= e($bank['account_thb']) ?></td>
                    <td><?= e($bank['account_usd']) ?></td>
                    <td><?= e($bank['swift_code']) ?></td>
                    <td class="table-actions">
                        <a href="/banks/form.php?id=<?= $bank['id'] ?>" class="btn btn-sm btn-outline-primary">ແກ້ໄຂ</a>
                        <form action="/banks/delete.php" method="post" class="d-inline" onsubmit="return confirm('ລຶບຂໍ້ມູນທະນາຄານນີ້ບໍ່?');">
                            <input type="hidden" name="id" value="<?= $bank['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger">ລຶບ</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$banks): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">ຍັງບໍ່ມີຂໍ້ມູນທະນາຄານ</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
