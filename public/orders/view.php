<?php
require_once __DIR__ . '/../../src/bootstrap.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$order = $id ? sales_order_find($id) : null;
if (!$order) {
    flash('danger', 'ບໍ່ພົບ Sales Order ນີ້');
    redirect('/orders/');
}

$customer = customer_find($order['customer_id']);
$bank = $order['bank_id'] ? bank_find($order['bank_id']) : null;

$totalLiters = array_sum(array_column($order['deliveries'], 'liters'));

$pageTitle = 'Sales Order ' . $order['document_no'];
require __DIR__ . '/../partials/header.php';
?>
<link href="<?= url('/assets/css/print.css') ?>?v=<?= filemtime(__DIR__ . '/../assets/css/print.css') ?>" rel="stylesheet">

<div class="d-flex justify-content-end gap-2 mb-3 no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">🖨️ Print / Save as PDF</button>
    <a href="<?= url('/orders/form.php') ?>?id=<?= $order['id'] ?>" class="btn btn-outline-secondary">ແກ້ໄຂ</a>
    <a href="<?= url('/orders/') ?>" class="btn btn-outline-secondary">ກັບຄືນ</a>
</div>
<div class="invoice-sheet card">
    <div class="invoice-header">
        <div class="company-block">
            <img src="<?= url('/logo.php') ?>" alt="logo">
            <div>
                <h6 style="font-weight: 700;" class="mb-1 ">NTP TRADING PETROLEUM CO., LTD.</h6>
                <div>Donglouang Village, Naxay Thong District, Vientiane Capital Laos P.D.R</div>
                <div>Tel. : 030-5888885</div>
                <div>TAX ID : 200510584900
                    <span class="doc-title-label">Sales Order</span>

                </div>
                <div>ntp@gmail.com
                    <span class="doc-title-original">Original</span>
                </div>
            </div>
        </div>
    </div>
    <div class="meta-grid">
        <div class="box">
            <h6>Customer</h6>
            <div><?= e($customer['code']) ?> <?= e($customer['company_name']) ?></div>
            <div><?= e(trim(implode(' ', array_filter([$customer['village'], $customer['district'], $customer['province']])))) ?></div><br>
            <div class="customer-line mt-1">Contact Person: <?= e($customer['contact_person']) ?></div>
            <div class="customer-line">Tel: <?= e($customer['phone']) ?> &nbsp; Fax: <?= e($customer['fax']) ?></div>
            <div class="customer-line">TAX ID: <?= e($customer['tax_id']) ?></div>
        </div>
        <div class="box">
            <h6>ShipTo Address</h6>
            <div><?= nl2br(e($order['ship_to_address'])) ?></div>
        </div>
        <div class="box">
            <dl>
                <div>
                    <dt>Document No.</dt>
                    <dd>: <?= e($order['document_no']) ?></dd>
                </div>
                <div>
                    <dt>Document Date</dt>
                    <dd>: <?= format_date($order['document_date']) ?></dd>
                </div>
                <div>
                    <dt>Payment Term</dt>
                    <dd>: <?= e($order['payment_term']) ?></dd>
                </div>
                <div>
                    <dt>Delivery Date</dt>
                    <dd>: <?= format_date($order['delivery_date']) ?></dd>
                </div>
                <div>
                    <dt>Reference No.</dt>
                    <dd>: <?= e($order['reference_no']) ?></dd>
                </div>
                <div>
                    <dt>Currency</dt>
                    <dd>: <?= e($order['currency']) ?></dd>
                </div>
                <div>
                    <dt>Warehouse</dt>
                    <dd>: <?= e($order['warehouse_code']) ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Product Code</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>UoM</th>
                <th>Unit Price</th>
                <th>Disc Amt.</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['items'] as $i => $item): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= e($item['product_code']) ?></td>
                    <td><?= e($item['product_name']) ?></td>
                    <td class="num"><?= money($item['quantity'], 2) ?></td>
                    <td class="text-center"><?= e($item['uom']) ?></td>
                    <td class="num"><?= money($item['unit_price']) ?></td>
                    <td class="num"><?= money($item['disc_amount']) ?></td>
                    <td class="num"><?= money($item['amount']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table class="deliveries-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Sales Order No</th>
                <th>Liters</th>
                <th>Delivery Note No</th>
                <th>AR Invoice No</th>
                <th>TAX Invoice No</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order['deliveries'] as $row): ?>
                <tr>
                    <td class="text-center"><?= format_date($row['delivery_date']) ?></td>
                    <td class="text-center"><?= e($row['sales_order_no']) ?></td>
                    <td class="num"><?= money($row['liters'], 2) ?></td>
                    <td class="text-center"><?= e($row['delivery_note_no']) ?></td>
                    <td class="text-center"><?= e($row['ar_invoice_no']) ?></td>
                    <td class="text-center"><?= e($row['tax_no']) ?></td>
                </tr>
            <?php endforeach; ?>

        </tbody>
    </table>

    <?php $remarkLines = array_values(array_filter(preg_split('/\r\n|\r|\n/', (string) $order['remark']))); ?>
    <div class="bottom-grid">
        <div class="remark-col">
            <div class="remark-box">
                <?php if ($remarkLines): ?>
                    <?php foreach ($remarkLines as $i => $line): ?>
                        <div class="remark-line"><?php if ($i === 0): ?><strong>Remark:</strong> <br> <?php endif; ?><?= e($line) ?></div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="remark-line"><strong>Remark:</strong></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="totals-col">
            <table class="totals-table">
                <tr>
                    <td>Sub Total</td>
                    <td class="text-end"><?= money($order['sub_total']) ?></td>
                </tr>
                <tr>
                    <td>Discount <?= $order['discount_type'] === 'percent' ? '(' . money($order['discount_value'], 0) . '%)' : '' ?></td>
                    <td class="text-end"><?= money($order['discount_amount']) ?></td>
                </tr>
                <tr>
                    <td>Total</td>
                    <td class="text-end"><?= money($order['total']) ?></td>
                </tr>
                <tr>
                    <td>
                        <div class="vat-label-flex">
                            <span>VAT</span>
                            <span><?= money($order['vat_percent'], 0) ?>%</span>
                        </div>
                    </td>
                    <td class="text-end"><?= money($order['vat_amount']) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="amount-in-words">
        <div class="words"><?= e(amount_in_words((float) $order['net_total'], $order['currency'])) ?></div>
        <div class="net-total-label">Net Total</div>
        <div class="net-total-value"><?= money($order['net_total']) ?></div>
    </div>

    <div class="signatures">
        <div>
            <div class="sig-title">Issued By</div>
            <div class="sig-space"><?= e($order['prepared_by']) ?></div>
            <div class="sig-date-line">Date___/___/___</div>
        </div>
        <div>
            <div class="sig-title">Verified By</div>
            <div class="sig-space"><?= e($order['verified_by']) ?></div>
            <div class="sig-date-line">Date___/___/___</div>
        </div>
        <div>
            <div class="sig-title">Approved By</div>
            <div class="sig-space"><?= e($order['approved_by']) ?></div>
            <div class="sig-date-line">Date___/___/___</div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>