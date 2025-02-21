<?php

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid id');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();
$data = pg_select($link, 'artifact', ['id' => $_GET['id']]);
if (!$data || count($data) === 0) {
    http_response_code(404);
    die('Artifact not found');
}

$data = $data[0];

$files = pg_query_params($link,
     "SELECT
               f.id as id,
               f.file_name as file_name,
               f.file_size as file_size,
               f.file_hash_crc32 as file_hash_crc32,
               f.file_hash_md5 as file_hash_md5,
               f.file_hash_sha1 as file_hash_sha1,
               f.file_hash_sha256 as file_hash_sha256,
               f.file_hash_sha512 as file_hash_sha512
            FROM
                file f,
                artifact_file_map afm
            WHERE
                afm.artifact_id = $1
                AND afm.file_id = f.id", [$data['id']]);

if (!$files) {
    http_response_code(500);
    die('Could not get files');
}
$files = pg_fetch_all($files);

$base_systems = pg_query_params($link,
    "SELECT
               *
            FROM
                base_system bs,
                artifact_base_system_map abm
            WHERE
                abm.artifact_id = $1
                AND abm.base_system_id = bs.id", [$data['id']]);
if (!$base_systems) {
    http_response_code(500);
    die('Could not get base systems');
}
$base_systems = pg_fetch_all($base_systems);

$dependency_artifacts = pg_query_params($link,
    "SELECT
               a.id as id,
               a.display_name as display_name,
               adm.is_optional as is_optional
            FROM
                artifact a,
                artifact_dependency_map adm
            WHERE
                adm.dependent_artifact_id = $1
                AND adm.artifact_id = a.id", [$data['id']]);
if (!$dependency_artifacts) {
    http_response_code(500);
    die('Could not get dependency artifacts');
}
$dependency_artifacts = pg_fetch_all($dependency_artifacts);

$dependent_artifacts = pg_query_params($link,
    "SELECT
               a.id as id,
               a.display_name as display_name,
               adm.is_optional as is_optional
            FROM
                artifact a,
                artifact_dependency_map adm
            WHERE
                adm.artifact_id = $1
                AND adm.dependent_artifact_id = a.id", [$data['id']]);
if (!$dependent_artifacts) {
    http_response_code(500);
    die('Could not get dependent artifacts');
}
$dependent_artifacts = pg_fetch_all($dependent_artifacts);

$deprecation_artifacts = pg_query_params($link,
    "SELECT
               a.id as id,
               a.display_name as display_name,
               a.description as description
            FROM
                artifact a,
                artifact_deprecation_map adm
            WHERE
                adm.artifact_id = $1
                AND adm.deprecated_artifact_id = a.id", [$data['id']]);
if (!$deprecation_artifacts) {
    http_response_code(500);
    die('Could not get deprecation artifacts');
}
$deprecation_artifacts = pg_fetch_all($deprecation_artifacts);

$make_depricated_artifacts = pg_query_params($link,
    "SELECT
               a.id as id,
               a.display_name as display_name,
               a.description as description
            FROM
                artifact a,
                artifact_deprecation_map adm
            WHERE
                adm.deprecated_artifact_id = $1
                AND adm.artifact_id = a.id", [$data['id']]);
if (!$make_depricated_artifacts) {
    http_response_code(500);
    die('Could not get make deprecate artifacts');
}
$make_depricated_artifacts = pg_fetch_all($make_depricated_artifacts);
$is_deprecated = count($make_depricated_artifacts) > 0;

$artifact_parents = pg_query_params($link, "SELECT
                a.id as id,
                a.display_name as display_name,
                a.description as description
                FROM
                 artifact a,
                 artifact_tree_map adm
                WHERE
                 adm.artifact_id = $1
                 AND adm.parent_artifact_id = a.id", [$data['id']]);
if (!$artifact_parents) {
    http_response_code(500);
    die('Could not get artifact parents');
}
$artifact_parents = pg_fetch_all($artifact_parents);

$artifact_children = pg_query_params($link, "SELECT
                a.id as id,
                a.display_name as display_name,
                a.description as description
                FROM
                 artifact a,
                 artifact_tree_map adm
                WHERE
                 adm.parent_artifact_id = $1
                 AND adm.artifact_id = a.id", [$data['id']]);
if (!$artifact_children) {
    http_response_code(500);
    die('Could not get artifact children');
}
$artifact_children = pg_fetch_all($artifact_children);

db_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title><?= escape($data['display_name']) ?> - Artifact Vault</title>
</head>
<body>
<main>
    <?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

    <h1>
        <?php if ($is_deprecated): ?><s><?php endif; ?>
            <?= escape($data['display_name']) ?>
        <?php if ($is_deprecated): ?></s><?php endif; ?>
    </h1>
    <pre><?= escape($data['description']) ?></pre>

    <a class="btn btn-primary" href="/artifact/search.php?aid=<?= $data['id'] ?>" role="button">Link to artifact</a>

    <section>
        <h2>Base Systems</h2>
        <ul>
            <?php foreach ($base_systems as $base_system): ?>
                <li>
                    <a href="/base/info.php?id=<?= $base_system['id'] ?>">
                        <?= escape($base_system['display_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li>
                <a href="/base/search.php?aid=<?= $data['id'] ?>">Assign new</a>
            </li>
        </ul>
    </section>
    <?php if (count($artifact_parents) > 0): ?>
        <section>
            <h2>Parent Artifacts</h2>
            <ul>
                <?php foreach ($artifact_parents as $artifact_parent): ?>
                    <li>
                        <a href="/artifact/info.php?id=<?= $artifact_parent['id'] ?>">
                            <?= escape($artifact_parent['display_name']) ?>
                        </a>
                        <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                            <input type="hidden" name="unlink" value="true">
                            <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                            <input type="hidden" name="link_id" value="<?= $artifact_parent['id'] ?>">
                            <input type="hidden" name="link_type" value="parent">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
    <?php if (count($artifact_children) > 0): ?>
        <section>
            <h2>Child Artifacts</h2>
            <ul>
                <?php foreach ($artifact_children as $artifact_child): ?>
                    <li>
                        <a href="/artifact/info.php?id=<?= $artifact_child['id'] ?>">
                            <?= escape($artifact_child['display_name']) ?>
                        </a>
                        <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                            <input type="hidden" name="unlink" value="true">
                            <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                            <input type="hidden" name="link_id" value="<?= $artifact_child['id'] ?>">
                            <input type="hidden" name="link_type" value="child">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
    <?php if (count($dependency_artifacts) > 0): ?>
        <section>
            <h2>Dependency Artifacts</h2>
            Artifacts that this artifact depends on:
            <ul>
                <?php foreach ($dependency_artifacts as $dependency_artifact): ?>
                    <li>
                        <a href="/artifact/info.php?id=<?= $dependency_artifact['id'] ?>">
                            <?= escape($dependency_artifact['display_name']) ?>
                        </a>
                        <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                            <input type="hidden" name="unlink" value="true">
                            <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                            <input type="hidden" name="link_id" value="<?= $dependency_artifact['id'] ?>">
                            <input type="hidden" name="link_type" value="dependent">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
    <?php if (count($dependent_artifacts) > 0): ?>
        <section>
            <h2>Dependent Artifacts</h2>
            Artifacts that depend on this artifact:
            <ul>
                <?php foreach ($dependent_artifacts as $dependent_artifact): ?>
                    <li>
                        <a href="/artifact/info.php?id=<?= $dependent_artifact['id'] ?>">
                            <?= escape($dependent_artifact['display_name']) ?>
                        </a>
                        <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                            <input type="hidden" name="unlink" value="true">
                            <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                            <input type="hidden" name="link_id" value="<?= $dependent_artifact['id'] ?>">
                            <input type="hidden" name="link_type" value="dependency">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
    <?php if (count($make_depricated_artifacts) > 0): ?>
        <section>
            <h2>Make Deprecated Artifacts</h2>
            This artifact has been deprecated by the following artifacts:
            <ul>
                <?php foreach ($make_depricated_artifacts as $make_depricated_artifact): ?>
                    <li>
                        <a href="/artifact/info.php?id=<?= $make_depricated_artifact['id'] ?>">
                            <?= escape($make_depricated_artifact['display_name']) ?>
                        </a>
                        <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                            <input type="hidden" name="unlink" value="true">
                            <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                            <input type="hidden" name="link_id" value="<?= $make_depricated_artifact['id'] ?>">
                            <input type="hidden" name="link_type" value="deprecated">
                            <button type="submit">Delete</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>
    <?php if (count($deprecation_artifacts) > 0) : ?>
    <section>
        <h2>Deprecation Artifacts</h2>
        Artifacts that are deprecated by this artifact:
        <ul>
            <?php foreach ($deprecation_artifacts as $deprecation_artifact): ?>
                <li>
                    <a href="/artifact/info.php?id=<?= $deprecation_artifact['id'] ?>">
                        <s><?= escape($deprecation_artifact['display_name']) ?></s>
                    </a>
                    <form action="/artifact/assign_artifact.php" method="post" class="inline-block">
                        <input type="hidden" name="unlink" value="true">
                        <input type="hidden" name="aid" value="<?= $data['id'] ?>">
                        <input type="hidden" name="link_id" value="<?= $deprecation_artifact['id'] ?>">
                        <input type="hidden" name="link_type" value="deprecated by this">
                        <button type="submit">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>
    <section>
        <h2>Files</h2>
        <ul>
            <?php foreach ($files as $file): ?>
                <li>
                    <a href="/file/info.php?id=<?= $file['id'] ?>">
                        <?= escape($file['file_name']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
            <li><a href="/file/upload.php?id=<?= $data['id'] ?>">Upload new</a></li>
        </ul>
    </section>
</main>
</body>
</html>
