<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../ui_elem/head.php'; ?>
    <title>Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../ui_elem/navbar.php'; ?>

<?php

require_once __DIR__ . '/../init.php';

$link = db_open();

$last_10_artifacts = pg_query($link, 'SELECT * FROM artifact ORDER BY id DESC LIMIT 10');
if (!$last_10_artifacts) {
    http_response_code(500);
    die('Could not get last 10 artifacts');
}
$last_10_artifacts = pg_fetch_all($last_10_artifacts);
?>

<h2>Latest artifacts</h2>

<ul>
    <?php foreach ($last_10_artifacts as $artifact): ?>
        <li>
            <a href="/artifact/info.php?id=<?= $artifact['id'] ?>">
                <?= escape($artifact['display_name']) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

</body>
</html>