<?php

function warehouse_all(): array
{
    return db()->query('SELECT * FROM warehouses ORDER BY code')->fetchAll();
}
