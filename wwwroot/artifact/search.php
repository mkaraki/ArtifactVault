<?php
$artifacts = [];

if (!empty($_GET['q'])) {
    $query = trim($_GET['q']);

    require_once __DIR__ . '/../../init.php';

    $link = db_open();

    $artifacts = pg_query_params($link, 'SELECT * FROM artifact WHERE display_name ILIKE $1', array("%$query%"));
    $artifacts = pg_fetch_all($artifacts);

    db_close($link);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title>Search - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1>Artifact Search</h1>

<form action="/artifact/search.php" method="get">
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

<ul>
    <?php foreach ($artifacts as $artifact): ?>
        <li>
            <a href="/artifact/info.php?id=<?= $artifact['id'] ?>"><?= escape($artifact['display_name']) ?></a>
            <?php if (isset($_GET['aid']) && is_numeric($_GET['aid'])): ?>
                <?php foreach (['parent', 'child', 'dependency', 'dependent', 'deprecated', 'deprecated by this'] as $link_type) : ?>
                    <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                        <input type="hidden" name="aid" value="<?= $_GET['aid'] ?>">
                        <input type="hidden" name="link_id" value="<?= $artifact['id'] ?>">
                        <input type="hidden" name="link_type" value="<?= $link_type ?>">
                        <button type="submit">Assign as <?= $link_type ?></button>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
</body>
</html>