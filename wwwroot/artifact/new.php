<?php

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $description = $_POST['description'] ?? '';

    if (empty($name)) {
        http_response_code(400);
        die('Name is required');
    }

    require_once __DIR__ . '/../../init.php';

    $link = db_open();
    $query = "INSERT INTO artifact (display_name, description) VALUES ($1, $2) RETURNING id";
    $result = pg_query_params($link, $query, array($name, $description));
    if (!$result) {
        http_response_code(500);
        die('Could not register artifact');
    }

    $new_id = pg_fetch_result($result, 0, 0);

    db_close($link);

    // Return see other
    header('Location: /artifact/info.php?id=' . $new_id, true, 303);

    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title>New Artifact - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1>New Artifact</h1>
    <form action="/artifact/new.php" method="post">
        <div>
            <div>
                <label for="name">Name</label>
            </div>
            <div>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="description">Description</label>
            </div>
            <div>
                <textarea id="description" name="description"></textarea>
            </div>
            <div>
                <button type="submit">Create</button>
            </div>
        </div>
    </form>
</body>
</html>