<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$order = $id ? sales_order_find($id) : null;
if ($id && !$order) {
    flash('danger', 'ບໍ່ພົບ Sales Order ນີ້');
    redirect('/orders/');
}

$customers = customer_all();
$banks = bank_all();
$products = product_all();
$warehouses = warehouse_all();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST;
    $items = $data['items'] ?? [];
    $deliveries = $data['deliveries'] ?? [];

    if (trim($data['document_no'] ?? '') === '') {
        $errors[] = 'ກະລຸນາປ້ອນ Document No.';
    }
    if (empty($data['customer_id'])) {
        $errors[] = 'ກະລຸນາເລືອກລູກຄ້າ';
    }
    $hasItem = false;
    foreach ($items as $item) {
        if ((float) ($item['quantity'] ?? 0) > 0) {
            $hasItem = true;
            break;
        }
    }
    if (!$hasItem) {
        $errors[] = 'ກະລຸນາປ້ອນລາຍການສິນຄ້າຢ່າງໜ້ອຍ 1 ລາຍການ';
    }

    if (!$errors) {
        try {
            $newId = sales_order_save($data, $items, $deliveries, $id);
            flash('success', 'ບັນທຶກ Sales Order ສຳເລັດ');
            redirect('/orders/view.php?id=' . $newId);
        } catch (PDOException $e) {
            $errors[] = str_contains($e->getMessage(), 'unique')
                ? 'Document No. ນີ້ຖືກໃຊ້ແລ້ວ'
                : 'ເກີດຂໍ້ຜິດພາດ: ' . $e->getMessage();
        }
    }

    $order = $data;
    $order['items'] = $items;
    $order['deliveries'] = $deliveries;
}

$pageTitle = $id ? 'ແກ້ໄຂ Sales Order' : 'ອອກບິນໃໝ່';
$items = $order['items'] ?? [['quantity' => '', 'unit_price' => '', 'disc_amount' => '']];
$deliveries = $order['deliveries'] ?? [[]];

require __DIR__ . '/../partials/header.php';
?>

<?php if ($errors): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" id="order-form">
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Document No.</label>
                <input type="text" name="document_no" class="form-control" value="<?= old($order ?? [], 'document_no', $id ? '' : next_document_no(db())) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Document Date</label>
                <input type="date" name="document_date" class="form-control" value="<?= old($order ?? [], 'document_date', date('Y-m-d')) ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Delivery Date</label>
                <input type="date" name="delivery_date" class="form-control" value="<?= old($order ?? [], 'delivery_date') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Reference No. (PO)</label>
                <input type="text" name="reference_no" class="form-control" value="<?= old($order ?? [], 'reference_no') ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Currency</label>
                <select name="currency" id="currency" class="form-select">
                    <?php foreach (CURRENCIES as $currency): ?>
                        <option value="<?= $currency ?>" <?= ($order['currency'] ?? 'LAK') === $currency ? 'selected' : '' ?>><?= $currency ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Warehouse</label>
                <select name="warehouse_code" class="form-select">
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option value="<?= e($warehouse['code']) ?>" <?= ($order['warehouse_code'] ?? '') === $warehouse['code'] ? 'selected' : '' ?>><?= e($warehouse['code']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Term</label>
                <input type="text" name="payment_term" id="payment_term" class="form-control" value="<?= old($order ?? [], 'payment_term') ?>">
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <label class="form-label fw-bold">ລູກຄ້າ (Customer)</label>
                <select name="customer_id" id="customer_id" class="form-select mb-2" required>
                    <option value="">-- ເລືອກລູກຄ້າ --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>"
                            data-company="<?= e($customer['company_name']) ?>"
                            data-village="<?= e($customer['village']) ?>"
                            data-district="<?= e($customer['district']) ?>"
                            data-province="<?= e($customer['province']) ?>"
                            data-contact="<?= e($customer['contact_person']) ?>"
                            data-phone="<?= e($customer['phone']) ?>"
                            data-taxid="<?= e($customer['tax_id']) ?>"
                            data-payment-term="<?= e($customer['payment_term']) ?>"
                            data-ship-to="<?= e($customer['ship_to_address']) ?>"
                            <?= (string) ($order['customer_id'] ?? '') === (string) $customer['id'] ? 'selected' : '' ?>>
                            <?= e($customer['code']) ?> - <?= e($customer['company_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="customer-summary" class="small text-muted mb-2"></div>
                <label class="form-label">ShipTo Address</label>
                <textarea name="ship_to_address" id="ship_to_address" class="form-control" rows="2"><?= old($order ?? [], 'ship_to_address') ?></textarea>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <label class="form-label fw-bold">ທະນາຄານ (ສຳລັບ Remark)</label>
                <select name="bank_id" id="bank_id" class="form-select mb-2">
                    <option value="">-- ເລືອກທະນາຄານ --</option>
                    <?php foreach ($banks as $bank): ?>
                        <option value="<?= $bank['id'] ?>"
                            data-bank-name="<?= e($bank['bank_name']) ?>"
                            data-account-name="<?= e($bank['account_name']) ?>"
                            data-account-lak="<?= e($bank['account_lak']) ?>"
                            data-account-thb="<?= e($bank['account_thb']) ?>"
                            data-account-usd="<?= e($bank['account_usd']) ?>"
                            data-swift="<?= e($bank['swift_code']) ?>"
                            <?= (string) ($order['bank_id'] ?? '') === (string) $bank['id'] ? 'selected' : '' ?>>
                            <?= e($bank['bank_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label">Remark</label>
                <textarea name="remark" id="remark" class="form-control" rows="4"><?= old($order ?? [], 'remark') ?></textarea>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">ລາຍການສິນຄ້າ</label>
            <button type="button" id="add-item-row" class="btn btn-sm btn-outline-primary">+ ເພີ່ມແຖວ</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm align-middle line-items-table">
                <thead>
                <tr>
                    <th style="width: 3%">No.</th>
                    <th style="width: 20%">Product Code</th>
                    <th style="width: 12%">Quantity</th>
                    <th style="width: 8%">UoM</th>
                    <th style="width: 15%">Unit Price</th>
                    <th style="width: 15%">Disc/Liter</th>
                    <th style="width: 17%" class="text-end">Amount</th>
                    <th style="width: 3%"></th>
                </tr>
                </thead>
                <tbody id="items-body">
                <?php foreach ($items as $i => $item): ?>
                    <tr data-amount="<?= ((float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0)) - (float) ($item['disc_amount'] ?? 0) ?>">
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td>
                            <select name="items[<?= $i ?>][product_id]" class="form-select form-select-sm item-product">
                                <option value="">-- ເລືອກສິນຄ້າ --</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>"
                                        data-code="<?= e($product['code']) ?>"
                                        data-name="<?= e($product['name']) ?>"
                                        data-uom="<?= e($product['uom']) ?>"
                                        data-price="<?= e($product['default_unit_price']) ?>"
                                        <?= (string) ($item['product_id'] ?? '') === (string) $product['id'] ? 'selected' : '' ?>>
                                        <?= e($product['code']) ?> - <?= e($product['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="items[<?= $i ?>][product_code]" class="item-product-code" value="<?= old($item, 'product_code') ?>">
                            <input type="hidden" name="items[<?= $i ?>][product_name]" class="item-product-name" value="<?= old($item, 'product_name') ?>">
                        </td>
                        <td><input type="number" step="0.001" name="items[<?= $i ?>][quantity]" class="form-control form-control-sm item-qty" value="<?= old($item, 'quantity') ?>"></td>
                        <td><input type="text" name="items[<?= $i ?>][uom]" class="form-control form-control-sm item-uom" value="<?= old($item, 'uom', 'Liter') ?>"></td>
                        <td><input type="number" step="0.01" name="items[<?= $i ?>][unit_price]" class="form-control form-control-sm item-price" value="<?= old($item, 'unit_price') ?>"></td>
                        <td><input type="number" step="0.01" name="items[<?= $i ?>][disc_amount]" class="form-control form-control-sm item-disc" value="<?= old($item, 'disc_amount', '0') ?>"></td>
                        <td class="text-end item-amount fw-semibold">0.00</td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <template id="item-row-template">
            <tr data-amount="0">
                <td class="text-center">-</td>
                <td>
                    <select name="items[__INDEX__][product_id]" class="form-select form-select-sm item-product">
                        <option value="">-- ເລືອກສິນຄ້າ --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"
                                data-code="<?= e($product['code']) ?>"
                                data-name="<?= e($product['name']) ?>"
                                data-uom="<?= e($product['uom']) ?>"
                                data-price="<?= e($product['default_unit_price']) ?>">
                                <?= e($product['code']) ?> - <?= e($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="items[__INDEX__][product_code]" class="item-product-code" value="">
                    <input type="hidden" name="items[__INDEX__][product_name]" class="item-product-name" value="">
                </td>
                <td><input type="number" step="0.001" name="items[__INDEX__][quantity]" class="form-control form-control-sm item-qty" value=""></td>
                <td><input type="text" name="items[__INDEX__][uom]" class="form-control form-control-sm item-uom" value="Liter"></td>
                <td><input type="number" step="0.01" name="items[__INDEX__][unit_price]" class="form-control form-control-sm item-price" value=""></td>
                <td><input type="number" step="0.01" name="items[__INDEX__][disc_amount]" class="form-control form-control-sm item-disc" value="0"></td>
                <td class="text-end item-amount fw-semibold">0.00</td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-item">&times;</button></td>
            </tr>
        </template>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <label class="form-label fw-bold mb-0">ລາຍລະອຽດການສົ່ງ (Delivery Detail)</label>
            <button type="button" id="add-delivery-row" class="btn btn-sm btn-outline-primary">+ ເພີ່ມແຖວ</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle deliveries-table">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Sales Order No</th>
                    <th>Liters</th>
                    <th>Delivery Note No</th>
                    <th>AR Invoice No</th>
                    <th>TAX Invoice No</th>
                    <th style="width: 3%"></th>
                </tr>
                </thead>
                <tbody id="deliveries-body">
                <?php foreach ($deliveries as $i => $row): ?>
                    <tr>
                        <td><input type="date" name="deliveries[<?= $i ?>][delivery_date]" class="form-control form-control-sm" value="<?= old($row, 'delivery_date') ?>"></td>
                        <td><input type="text" name="deliveries[<?= $i ?>][sales_order_no]" class="form-control form-control-sm" value="<?= old($row, 'sales_order_no') ?>"></td>
                        <td><input type="number" step="0.001" name="deliveries[<?= $i ?>][liters]" class="form-control form-control-sm delivery-liters" value="<?= old($row, 'liters') ?>"></td>
                        <td><input type="text" name="deliveries[<?= $i ?>][delivery_note_no]" class="form-control form-control-sm" value="<?= old($row, 'delivery_note_no') ?>"></td>
                        <td><input type="text" name="deliveries[<?= $i ?>][ar_invoice_no]" class="form-control form-control-sm" value="<?= old($row, 'ar_invoice_no') ?>"></td>
                        <td><input type="text" name="deliveries[<?= $i ?>][tax_no]" class="form-control form-control-sm" value="<?= old($row, 'tax_no') ?>"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-delivery">&times;</button></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="2" class="text-end fw-bold">ຍອດລວມ (Total Liters)</td>
                    <td class="fw-bold" id="total_liters_display">0.000</td>
                    <td colspan="4"></td>
                </tr>
                </tfoot>
            </table>
        </div>
        <template id="delivery-row-template">
            <tr>
                <td><input type="date" name="deliveries[__INDEX__][delivery_date]" class="form-control form-control-sm" value=""></td>
                <td><input type="text" name="deliveries[__INDEX__][sales_order_no]" class="form-control form-control-sm" value=""></td>
                <td><input type="number" step="0.001" name="deliveries[__INDEX__][liters]" class="form-control form-control-sm delivery-liters" value=""></td>
                <td><input type="text" name="deliveries[__INDEX__][delivery_note_no]" class="form-control form-control-sm" value=""></td>
                <td><input type="text" name="deliveries[__INDEX__][ar_invoice_no]" class="form-control form-control-sm" value=""></td>
                <td><input type="text" name="deliveries[__INDEX__][tax_no]" class="form-control form-control-sm" value=""></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-delivery">&times;</button></td>
            </tr>
        </template>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <label class="form-label fw-bold">ຜູ້ກ່ຽວຂ້ອງ (Signatures)</label>
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label small">Issued By</label>
                        <input type="text" readonly name="prepared_by" class="form-control form-control-sm" value="<?= old($order ?? [], 'prepared_by') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Verified By</label>
                        <input type="text" readonly name="verified_by" class="form-control form-control-sm" value="<?= old($order ?? [], 'verified_by') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Approved By</label>
                        <input type="text" readonly name="approved_by" class="form-control form-control-sm" value="<?= old($order ?? [], 'approved_by') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>Sub Total</td>
                        <td class="text-end" id="sub_total_display">0.00</td>
                    </tr>
                    <tr>
                        <td>Discount</td>
                        <td class="text-end">
                            <div class="input-group input-group-sm justify-content-end">
                                <input type="number" step="0.01" name="discount_value" id="discount_value" class="form-control text-end" style="max-width: 140px" value="<?= old($order ?? [], 'discount_value', '0') ?>">
                                <select name="discount_type" id="discount_type" class="form-select" style="max-width: 100px">
                                    <option value="amount" <?= ($order['discount_type'] ?? 'amount') === 'amount' ? 'selected' : '' ?>>ຈຳນວນເງິນ</option>
                                    <option value="percent" <?= ($order['discount_type'] ?? '') === 'percent' ? 'selected' : '' ?>>%</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>Total</td>
                        <td class="text-end" id="total_display">0.00</td>
                    </tr>
                    <tr>
                        <td>VAT</td>
                        <td class="text-end">
                            <div class="input-group input-group-sm justify-content-end mb-1">
                                <select name="vat_mode" id="vat_mode" class="form-select" style="max-width: 160px">
                                    <option value="exclusive" <?= ($order['vat_mode'] ?? 'exclusive') === 'exclusive' ? 'selected' : '' ?>>Exclusive</option>
                                    <option value="inclusive" <?= ($order['vat_mode'] ?? '') === 'inclusive' ? 'selected' : '' ?>>Inclusive</option>
                                </select>
                            </div>
                            <div class="input-group input-group-sm justify-content-end">
                                <input type="number" step="0.01" name="vat_percent" id="vat_percent" class="form-control text-end" style="max-width: 100px" value="<?= old($order ?? [], 'vat_percent', '10') ?>">
                                <span class="input-group-text">%</span>
                            </div>
                            <div class="text-end small text-muted" id="vat_amount_display">0.00</div>
                        </td>
                    </tr>
                    <tr class="table-light">
                        <td class="fw-bold">Net Total</td>
                        <td class="text-end fw-bold fs-5" id="net_total_display">0.00</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mb-5">
    <button type="submit" class="btn btn-primary btn-lg">ບັນທຶກ Sales Order</button>
    <a href="<?= url('/orders/') ?>" class="btn btn-outline-secondary btn-lg">ຍົກເລີກ</a>
</div>
</form>

<script src="<?= url('/assets/js/order-form.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/order-form.js') ?>"></script>

<?php require __DIR__ . '/../partials/footer.php'; ?>
