<?php

if (empty($_POST['aid']) || empty($_POST['bid'])) {
    http_response_code(400);
    die('Missing required fields');
}

if (!is_numeric($_POST['aid']) || !is_numeric($_POST['bid'])) {
    http_response_code(400);
    die('Invalid required fields');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();

$artifacts = pg_select($link, 'artifact', ['id' => $_POST['aid']]);
if (!$artifacts || count($artifacts) === 0) {
    http_response_code(404);
    die('Artifact not found');
}

$base_systems = pg_select($link, 'base_system', ['id' => $_POST['bid']]);
if (!$base_systems || count($base_systems) === 0) {
    http_response_code(404);
    die('Base system not found');
}

$query = "INSERT INTO artifact_base_system_map (artifact_id, base_system_id) VALUES ($1, $2)";
$result = pg_query_params($link, $query, array($_POST['aid'], $_POST['bid']));

if (!$result) {
    http_response_code(500);
    die('Could not assign artifact');
}

db_close($link);

header('Location: /artifact/info.php?id=' . $_POST['aid'], true, 303);
