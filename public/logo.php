<?php
$path = __DIR__ . '/../assets/logo (1).png';
header('Content-Type: image/png');
header('Cache-Control: public, max-age=86400');
readfile($path);
