<?php

function product_all(): array
{
    return db()->query('SELECT * FROM products ORDER BY code')->fetchAll();
}

function product_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function product_save(array $data, ?int $id = null): int
{
    $params = [
        'code' => trim($data['code'] ?? ''),
        'name' => trim($data['name'] ?? ''),
        'uom' => trim($data['uom'] ?? 'Liter'),
        'default_unit_price' => (float) ($data['default_unit_price'] ?? 0),
    ];

    if ($id === null) {
        $stmt = db()->prepare(
            'INSERT INTO products (code, name, uom, default_unit_price) VALUES (:code, :name, :uom, :default_unit_price) RETURNING id'
        );
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    $params['id'] = $id;
    $stmt = db()->prepare(
        'UPDATE products SET code = :code, name = :name, uom = :uom, default_unit_price = :default_unit_price, updated_at = now() WHERE id = :id'
    );
    $stmt->execute($params);
    return $id;
}

function product_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM products WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
