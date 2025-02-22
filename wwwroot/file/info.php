<?php

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid id');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();
$data = pg_select($link, 'file', ['id' => $_GET['id']]);
if (!$data || count($data) === 0) {
    http_response_code(404);
    die('File not found');
}
$data = $data[0];

$artifacts = pg_query_params($link,
    "SELECT
                a.id as id,
                a.display_name as name,
                a.description as description
             FROM
                 artifact a,
                 artifact_file_map afm
             WHERE
                 afm.file_id = $1
                 AND afm.artifact_id = a.id", [$data['id']]);
if (!$artifacts) {
    http_response_code(500);
    die('Could not get assigned artifacts');
}
$artifacts = pg_fetch_all($artifacts);

db_close($link);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title><?= escape($data['file_name']) ?> - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1><?= escape($data['file_name']) ?></h1>

<a class="btn btn-primary" href="/file/download.php?id=<?= $data['id'] ?>" role="button">Download</a>

<dl>
    <dt>Size</dt>
    <dd><?= number_format($data['file_size']) ?> Bytes</dd>
    <?php if ($data['file_hash_crc32'] !== null) : ?>
        <dt>CRC32</dt>
        <dd><?= bin2hex(pg_unescape_bytea($data['file_hash_crc32'])) ?></dd>
    <?php endif; ?>
    <?php if ($data['file_hash_md5'] !== null) : ?>
        <dt>MD5</dt>
        <dd><?= bin2hex(pg_unescape_bytea($data['file_hash_md5'])) ?></dd>
    <?php endif; ?>
    <?php if ($data['file_hash_sha1'] !== null) : ?>
        <dt>SHA1</dt>
        <dd><?= bin2hex(pg_unescape_bytea($data['file_hash_sha1'])) ?></dd>
    <?php endif; ?>
    <?php if ($data['file_hash_sha256'] !== null) : ?>
        <dt>SHA256</dt>
        <dd><?= bin2hex(pg_unescape_bytea($data['file_hash_sha256'])) ?></dd>
    <?php endif; ?>
    <?php if ($data['file_hash_sha512'] !== null) : ?>
        <dt>SHA512</dt>
        <dd><?= bin2hex(pg_unescape_bytea($data['file_hash_sha512'])) ?></dd>
    <?php endif; ?>
</dl>

<section>
    <h2>Artifacts</h2>
    <ul>
        <?php foreach ($artifacts as $artifact): ?>
            <li>
                <a href="/artifact/info.php?id=<?= $artifact['id'] ?>"><?= escape($artifact['name']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js" integrity="sha512-7Pi/otdlbbCR+LnW+F7PwFcSDJOuUJB3OxtEHbg4vSMvzvJjde4Po1v4BR9Gdc9aXNUNFVUY+SK51wWT8WF0Gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>
</html>
