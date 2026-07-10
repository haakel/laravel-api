<?php
header('Content-Type: application/json');
echo json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'files' => $_FILES,
    'post' => $_POST,
    'server' => [
        'php_version' => PHP_VERSION,
        'upload_max' => ini_get('upload_max_filesize'),
        'post_max' => ini_get('post_max_size'),
        'tmp_dir' => ini_get('upload_tmp_dir') ?: sys_get_temp_dir(),
    ],
], JSON_PRETTY_PRINT);
