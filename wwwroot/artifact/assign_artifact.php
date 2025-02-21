<?php

if (empty($_POST['aid']) || empty($_POST['link_id']) || empty($_POST['link_type'])) {
    http_response_code(400);
    die('Missing required fields');
}

if (!is_numeric($_POST['aid']) || !is_numeric($_POST['link_id'])) {
    http_response_code(400);
    die('Invalid required fields');
}

require_once __DIR__ . '/../../init.php';
$db = db_open();

$artifact = pg_select($db, 'artifact', ['id' => $_POST['aid']]);
if (!$artifact || count($artifact) === 0) {
    db_close($db);
    http_response_code(404);
    die('Artifact not found');
}

$link = pg_select($db, 'artifact', ['id' => $_POST['link_id']]);
if (!$link || count($link) === 0) {
    db_close($db);
    http_response_code(404);
    die('Link not found');
}

$unlink = isset($_POST['unlink']) && $_POST['unlink'] === 'true';

$result = false;

switch ($_POST['link_type']) {
    case 'parent':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_tree_map WHERE artifact_id = $1 AND parent_artifact_id = $2", array($_POST['aid'], $_POST['link_id']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_tree_map (artifact_id, parent_artifact_id) VALUES ($1, $2)", array($_POST['aid'], $_POST['link_id']));
        break;
    case 'child':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_tree_map WHERE artifact_id = $1 AND parent_artifact_id = $2", array($_POST['link_id'], $_POST['aid']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_tree_map (artifact_id, parent_artifact_id) VALUES ($1, $2)", array($_POST['link_id'], $_POST['aid']));
        break;
    case 'dependency':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_dependency_map WHERE artifact_id = $1 AND dependent_artifact_id = $2", array($_POST['aid'], $_POST['link_id']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_dependency_map (artifact_id, dependent_artifact_id, is_optional) VALUES ($1, $2, false)", array($_POST['aid'], $_POST['link_id']));
        break;
    case 'dependent':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_dependency_map WHERE artifact_id = $1 AND dependent_artifact_id = $2", array($_POST['link_id'], $_POST['aid']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_dependency_map (artifact_id, dependent_artifact_id, is_optional) VALUES ($1, $2, false)", array($_POST['aid'], $_POST['link_id']));
        break;
    case 'deprecated':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_deprecation_map WHERE artifact_id = $1 AND deprecated_artifact_id = $2", array($_POST['aid'], $_POST['link_id']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_deprecation_map (artifact_id, deprecated_artifact_id, reason) VALUES ($1, $2, '')", array($_POST['aid'], $_POST['link_id']));
        break;
    case 'deprecated by this':
        if ($unlink)
            $result = pg_query_params($db, "DELETE FROM artifact_deprecation_map WHERE artifact_id = $1 AND deprecated_artifact_id = $2", array($_POST['aid'], $_POST['link_id']));
        else
            $result = pg_query_params($db, "INSERT INTO artifact_deprecation_map (artifact_id, deprecated_artifact_id, reason) VALUES ($1, $2, '')", array($_POST['link_id'], $_POST['aid']));
        break;
    default:
        db_close($db);
        http_response_code(400);
        die('Invalid required fields');
}

if (!$result) {
    db_close($db);
    http_response_code(500);
    die('Could not assign artifact');
} else {
    db_close($db);
    header('Location: /artifact/info.php?id=' . $_POST['aid'], true, 303);
    exit;
}
