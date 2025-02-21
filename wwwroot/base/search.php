<?php

$base_systems = false;

if (!empty($_GET['q'])) {
    require_once __DIR__ . '/../../init.php';

    $link = db_open();
    $result = pg_query_params($link, 'SELECT * FROM base_system WHERE display_name ILIKE $1', ['%' . $_GET['q'] . '%']);
    if (!$result) {
        http_response_code(500);
        die('Could not search');
    }

    $base_systems = pg_fetch_all($result);

    db_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title>Search Base Systems - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1>Search Base Systems</h1>
    <form action="/base/search.php" method="get">
        <div>
            <div>
                <label for="q">Query</label>
            </div>
            <div>
                <input type="text" id="q" name="q" required>
            </div>
            <div>
                <?php if (isset($_GET['aid']) && is_numeric($_GET['aid'])): ?>
                    <input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
                <?php endif; ?>
                <button type="submit">Search</button>
            </div>
        </div>
    </form>
    <?php if ($base_systems): ?>
        <section>
            <h2>Results</h2>
            <ul>
                <?php foreach ($base_systems as $base_system): ?>
                    <li>
                        <a href="/base/info.php?id=<?= $base_system['id'] ?>">
                            <?= escape($base_system['display_name']) ?>
                        </a>

                        <?php if (isset($_GET['aid']) && is_numeric($_GET['aid'])): ?>
                            <form action="/base/assign_artifact.php" method="post" class="inline-block">
                                <input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
                                <input type="hidden" name="bid" value="<?= $base_system['id'] ?>">
                                <button type="submit">Assign</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
</body>
</html>