<?php

function sales_order_all(): array
{
    return db()->query(
        'SELECT so.*, c.company_name
         FROM sales_orders so
         JOIN customers c ON c.id = so.customer_id
         ORDER BY so.document_date DESC, so.id DESC'
    )->fetchAll();
}

function sales_order_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM sales_orders WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $order = $stmt->fetch();
    if (!$order) {
        return null;
    }

    $itemsStmt = db()->prepare('SELECT * FROM sales_order_items WHERE sales_order_id = :id ORDER BY line_no');
    $itemsStmt->execute(['id' => $id]);
    $order['items'] = $itemsStmt->fetchAll();

    $deliveriesStmt = db()->prepare('SELECT * FROM sales_order_deliveries WHERE sales_order_id = :id ORDER BY row_order');
    $deliveriesStmt->execute(['id' => $id]);
    $order['deliveries'] = $deliveriesStmt->fetchAll();

    return $order;
}

/**
 * Recompute all monetary totals from posted line items + discount/vat inputs.
 * Never trust client-side JS math — this is the source of truth.
 */
function sales_order_calc_totals(array $items, string $discountType, float $discountValue, float $vatPercent, string $vatMode = 'exclusive'): array
{
    $rawSubTotal = 0.0;
    foreach ($items as $item) {
        $qty = (float) ($item['quantity'] ?? 0);
        $price = (float) ($item['unit_price'] ?? 0);
        $discPerUnit = (float) ($item['disc_amount'] ?? 0);
        $rawSubTotal += ($qty * $price) - ($qty * $discPerUnit);
    }

    if ($vatMode === 'inclusive') {
        // Line amounts already include VAT (gross) — back out the pre-tax Sub Total first.
        $subTotal = $rawSubTotal / (1 + $vatPercent / 100);
    } else {
        $subTotal = $rawSubTotal;
    }

    $discountAmount = $discountType === 'percent'
        ? $subTotal * $discountValue / 100
        : $discountValue;

    $total = $subTotal - $discountAmount;
    $vatAmount = $total * $vatPercent / 100;
    $netTotal = $total + $vatAmount;

    return [
        'sub_total' => round($subTotal, 2),
        'discount_amount' => round($discountAmount, 2),
        'total' => round($total, 2),
        'vat_amount' => round($vatAmount, 2),
        'net_total' => round($netTotal, 2),
    ];
}

function sales_order_save(array $data, array $items, array $deliveries, ?int $id = null): int
{
    $vatMode = $data['vat_mode'] === 'inclusive' ? 'inclusive' : 'exclusive';
    $totals = sales_order_calc_totals(
        $items,
        $data['discount_type'] === 'percent' ? 'percent' : 'amount',
        (float) ($data['discount_value'] ?? 0),
        (float) ($data['vat_percent'] ?? 0),
        $vatMode
    );

    $header = [
        'document_no' => trim($data['document_no']),
        'document_date' => $data['document_date'],
        'delivery_date' => $data['delivery_date'] ?: null,
        'reference_no' => trim($data['reference_no'] ?? ''),
        'currency' => $data['currency'],
        'warehouse_code' => $data['warehouse_code'],
        'customer_id' => (int) $data['customer_id'],
        'ship_to_address' => trim($data['ship_to_address'] ?? ''),
        'bank_id' => $data['bank_id'] !== '' ? (int) $data['bank_id'] : null,
        'payment_term' => trim($data['payment_term'] ?? ''),
        'remark' => trim($data['remark'] ?? ''),
        'discount_type' => $data['discount_type'] === 'percent' ? 'percent' : 'amount',
        'discount_value' => (float) ($data['discount_value'] ?? 0),
        'vat_percent' => (float) ($data['vat_percent'] ?? 0),
        'vat_mode' => $vatMode,
        ...$totals,
        'prepared_by' => trim($data['prepared_by'] ?? ''),
        'verified_by' => trim($data['verified_by'] ?? ''),
        'approved_by' => trim($data['approved_by'] ?? ''),
    ];

    $pdo = db();
    $pdo->beginTransaction();
    try {
        if ($id === null) {
            $columns = implode(', ', array_keys($header));
            $placeholders = implode(', ', array_map(fn($f) => ":$f", array_keys($header)));
            $stmt = $pdo->prepare("INSERT INTO sales_orders ($columns) VALUES ($placeholders) RETURNING id");
            $stmt->execute($header);
            $id = (int) $stmt->fetchColumn();
        } else {
            $assignments = implode(', ', array_map(fn($f) => "$f = :$f", array_keys($header)));
            $header['id'] = $id;
            $stmt = $pdo->prepare("UPDATE sales_orders SET $assignments, updated_at = now() WHERE id = :id");
            $stmt->execute($header);

            $pdo->prepare('DELETE FROM sales_order_items WHERE sales_order_id = :id')->execute(['id' => $id]);
            $pdo->prepare('DELETE FROM sales_order_deliveries WHERE sales_order_id = :id')->execute(['id' => $id]);
        }

        $itemStmt = $pdo->prepare(
            'INSERT INTO sales_order_items
                (sales_order_id, line_no, product_id, product_code, product_name, quantity, uom, unit_price, disc_amount, amount)
             VALUES
                (:sales_order_id, :line_no, :product_id, :product_code, :product_name, :quantity, :uom, :unit_price, :disc_amount, :amount)'
        );
        $lineNo = 1;
        foreach ($items as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $disc = (float) ($item['disc_amount'] ?? 0);
            if ($qty <= 0 && $price <= 0) {
                continue;
            }
            $itemStmt->execute([
                'sales_order_id' => $id,
                'line_no' => $lineNo++,
                'product_id' => $item['product_id'] !== '' ? (int) $item['product_id'] : null,
                'product_code' => trim($item['product_code'] ?? ''),
                'product_name' => trim($item['product_name'] ?? ''),
                'quantity' => $qty,
                'uom' => $item['uom'] ?? 'Liter',
                'unit_price' => $price,
                'disc_amount' => $disc,
                'amount' => round(($qty * $price) - ($qty * $disc), 2),
            ]);
        }

        $deliveryStmt = $pdo->prepare(
            'INSERT INTO sales_order_deliveries
                (sales_order_id, row_order, delivery_date, sales_order_no, liters, delivery_note_no, ar_invoice_no, tax_no)
             VALUES
                (:sales_order_id, :row_order, :delivery_date, :sales_order_no, :liters, :delivery_note_no, :ar_invoice_no, :tax_no)'
        );
        $rowOrder = 1;
        foreach ($deliveries as $row) {
            $hasData = trim($row['sales_order_no'] ?? '') !== '' || (float) ($row['liters'] ?? 0) > 0
                || trim($row['delivery_note_no'] ?? '') !== '' || trim($row['ar_invoice_no'] ?? '') !== ''
                || trim($row['tax_no'] ?? '') !== '' || trim($row['delivery_date'] ?? '') !== '';
            if (!$hasData) {
                continue;
            }
            $deliveryStmt->execute([
                'sales_order_id' => $id,
                'row_order' => $rowOrder++,
                'delivery_date' => $row['delivery_date'] !== '' ? $row['delivery_date'] : null,
                'sales_order_no' => trim($row['sales_order_no'] ?? ''),
                'liters' => (float) ($row['liters'] ?? 0),
                'delivery_note_no' => trim($row['delivery_note_no'] ?? ''),
                'ar_invoice_no' => trim($row['ar_invoice_no'] ?? ''),
                'tax_no' => trim($row['tax_no'] ?? ''),
            ]);
        }

        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function sales_order_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM sales_orders WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
