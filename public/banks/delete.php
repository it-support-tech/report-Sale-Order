<?php
require_once __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    try {
        bank_delete((int) $_POST['id']);
        flash('success', 'ລຶບຂໍ້ມູນທະນາຄານສຳເລັດ');
    } catch (PDOException $e) {
        flash('danger', 'ບໍ່ສາມາດລຶບໄດ້ ເນື່ອງຈາກມີ Sales Order ໃຊ້ຂໍ້ມູນນີ້ຢູ່');
    }
}

redirect('/banks/');
