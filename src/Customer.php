<?php

function customer_all(): array
{
    return db()->query('SELECT * FROM customers ORDER BY company_name')->fetchAll();
}

function customer_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM customers WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function customer_save(array $data, ?int $id = null): int
{
    $fields = [
        'code', 'company_name', 'village', 'district', 'province',
        'contact_person', 'phone', 'fax', 'tax_id', 'payment_term', 'ship_to_address',
    ];
    $params = [];
    foreach ($fields as $field) {
        $params[$field] = trim($data[$field] ?? '');
    }

    if ($id === null) {
        $columns = implode(', ', $fields);
        $placeholders = implode(', ', array_map(fn($f) => ":$f", $fields));
        $stmt = db()->prepare("INSERT INTO customers ($columns) VALUES ($placeholders) RETURNING id");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    $assignments = implode(', ', array_map(fn($f) => "$f = :$f", $fields));
    $params['id'] = $id;
    $stmt = db()->prepare("UPDATE customers SET $assignments, updated_at = now() WHERE id = :id");
    $stmt->execute($params);
    return $id;
}

function customer_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM customers WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
