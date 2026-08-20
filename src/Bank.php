<?php

function bank_all(): array
{
    return db()->query('SELECT * FROM company_banks ORDER BY bank_name')->fetchAll();
}

function bank_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM company_banks WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function bank_save(array $data, ?int $id = null): int
{
    $fields = ['bank_name', 'account_name', 'account_lak', 'account_thb', 'account_usd', 'swift_code'];
    $params = [];
    foreach ($fields as $field) {
        $params[$field] = trim($data[$field] ?? '');
    }
    $params['is_active'] = isset($data['is_active']) ? 'true' : 'false';

    if ($id === null) {
        $columns = implode(', ', [...$fields, 'is_active']);
        $placeholders = implode(', ', array_map(fn($f) => ":$f", [...$fields, 'is_active']));
        $stmt = db()->prepare("INSERT INTO company_banks ($columns) VALUES ($placeholders) RETURNING id");
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    $assignments = implode(', ', array_map(fn($f) => "$f = :$f", [...$fields, 'is_active']));
    $params['id'] = $id;
    $stmt = db()->prepare("UPDATE company_banks SET $assignments, updated_at = now() WHERE id = :id");
    $stmt->execute($params);
    return $id;
}

function bank_delete(int $id): void
{
    $stmt = db()->prepare('DELETE FROM company_banks WHERE id = :id');
    $stmt->execute(['id' => $id]);
}
