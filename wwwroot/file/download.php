<?php

if (empty($_GET['id'])) {
    http_response_code(400);
    die('Missing required fields');
}

if (!is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid required fields');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();

$files = pg_select($link, 'file', ['id' => $_GET['id']]);
if (!$files || count($files) === 0) {
    http_response_code(404);
    die('File not found');
}
$file = $files[0];
$file['file_size'] = intval($file['file_size']);
$file['file_hash_sha256'] = pg_unescape_bytea($file['file_hash_sha256']);

$s3_client = s3_client_open();

$object_info = $s3_client->headObject([
    'Bucket' => S3_BUCKET,
    'Key' => $file['id'],
]);

if ($object_info['ContentLength'] !== $file['file_size']) {
    http_response_code(500);
    die('File size mismatch. file may be tampered/corrupted');
}

$object = $s3_client->getObject([
    'Bucket' => S3_BUCKET,
    'Key' => $file['id'],
]);

// Validate hash
if (!empty($file['file_hash_md5'])) {
    $hash = hash('md5', $object['Body']);
    if ($hash !== bin2hex(pg_unescape_bytea($file['file_hash_md5']))) {
        http_response_code(500);
        die('File hash mismatch. file may be tampered/corrupted');
    }
}
else if (!empty($file['file_hash_sha1'])) {
    $hash = hash('sha1', $object['Body']);
    if ($hash !== bin2hex(pg_unescape_bytea($file['file_hash_sha1']))) {
        http_response_code(500);
        die('File hash mismatch. file may be tampered/corrupted');
    }
}
else if (!empty($file['file_hash_sha256'])) {
    $hash = hash('sha256', $object['Body']);
    if ($hash !== bin2hex(pg_unescape_bytea($file['file_hash_sha256']))) {
        http_response_code(500);
        die('File hash mismatch. file may be tampered/corrupted');
    }
}
else if (!empty($file['file_hash_sha512'])) {
    $hash = hash('sha512', $object['Body']);
    if ($hash !== bin2hex(pg_unescape_bytea($file['file_hash_sha512']))) {
        http_response_code(500);
        die('File hash mismatch. file may be tampered/corrupted');
    }
}
else if (!empty($file['file_hash_crc32'])) {
    $hash = hash_file('crc32b', $object['Body']);
    if ($hash !== bin2hex(pg_unescape_bytea($file['file_hash_crc32']))) {
        http_response_code(500);
        die('File hash mismatch. file may be tampered/corrupted');
    }
}

header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
header('Content-Length: ' . $file['file_size']);
echo $object['Body'];