<?php

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid id');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();

$data = pg_select($link, 'base_system', ['id' => $_GET['id']]);
if (!$data || count($data) === 0) {
    http_response_code(404);
    die('Base system not found');
}

$data = $data[0];

$artifacts_query = pg_query_params($link,
    "SELECT
                a.id as id,
                a.display_name as display_name,
                a.description as description
             FROM
                 artifact a,
                 artifact_base_system_map abm
             WHERE
                 abm.base_system_id = $1
                 AND abm.artifact_id = a.id", [$data['id']]);
$artifacts = pg_fetch_all($artifacts_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title><?= escape($data['display_name']) ?> - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1><?= escape($data['display_name']) ?></h1>
    <pre><?= escape($data['description']) ?></pre>

    <section>
        <h2>Artifacts</h2>
        <ul>
            <?php foreach ($artifacts as $artifact): ?>
                <li>
                    <a href="/artifact/info.php?id=<?= $artifact['id'] ?>"><?= escape($artifact['display_name']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</body>
</html>