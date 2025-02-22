<?php

if (isset($_FILES['file'])) {
    // Upload process

    if (!isset($_POST['artifact_id']) || !is_numeric($_POST['artifact_id']) ||
        empty($_POST['file_name']) || !isset($_POST['description'])) {
        http_response_code(400);
        die('Missing required fields');
    }

    $file_name = trim($_POST['file_name']);
    $description = trim($_POST['description']);

    $file_hash_crc32_user_calc = null;
    $file_hash_md5_user_calc = null;
    $file_hash_sha1_user_calc = null;
    $file_hash_sha256_user_calc = null;
    $file_hash_sha512_user_calc = null;

    switch (true) {
        case !empty($_POST['file_hash_md5']) && preg_match('/^[0-9a-f]{32}$/i', $_POST['file_hash_md5']) !== 1:
            $file_hash_md5_user_calc = strtolower(trim($_POST['file_hash_md5']));
            if ($file_hash_md5_user_calc !== strtolower(hash_file('md5', $_FILES['file']['tmp_name']))) {
                http_response_code(400);
                die('Invalid file hash');
            }
            break;
        case !empty($_POST['file_hash_sha1']) && preg_match('/^[0-9a-f]{40}$/i', $_POST['file_hash_sha1']) !== 1:
            $file_hash_sha1_user_calc = strtolower(trim($_POST['file_hash_sha1']));
            if ($file_hash_sha1_user_calc !== strtolower(hash_file('sha1', $_FILES['file']['tmp_name']))) {
                http_response_code(400);
                die('Invalid file hash');
            }
            break;
        case !empty($_POST['file_hash_sha256']) && preg_match('/^[0-9a-f]{64}$/i', $_POST['file_hash_sha256']) !== 1:
            $file_hash_sha256_user_calc = strtolower(trim($_POST['file_hash_sha256']));
            if ($file_hash_sha256_user_calc !== strtolower(hash_file('sha256', $_FILES['file']['tmp_name']))) {
                http_response_code(400);
                die('Invalid file hash');
            }
            break;
        case !empty($_POST['file_hash_sha512']) && preg_match('/^[0-9a-f]{128}$/i', $_POST['file_hash_sha512']) !== 1:
            $file_hash_sha512_user_calc = strtolower(trim($_POST['file_hash_sha512']));
            if ($file_hash_sha512_user_calc !== strtolower(hash_file('sha512', $_FILES['file']['tmp_name']))) {
                http_response_code(400);
                die('Invalid file hash');
            }
            break;
        case !empty($_POST['file_hash_crc32']) && preg_match('/^[0-9a-f]{8}$/i', $_POST['file_hash_crc32']) !== 1:
            $file_hash_crc32_user_calc = strtolower(trim($_POST['file_hash_crc32']));
            if ($file_hash_crc32_user_calc !== strtolower(hash_file('crc32b', $_FILES['file']['tmp_name']))) {
                http_response_code(400);
                die('Invalid file hash');
            }
            break;
        default:
            http_response_code(400);
            die('Missing/Invalid required fields. Hash is required');
    }

    if (empty($file_name)) {
        http_response_code(400);
        die('Invalid required fields');
    }

    // Check tmp file exists
    if (!file_exists($_FILES['file']['tmp_name'])) {
        http_response_code(400);
        die('Invalid file');
    }

    // Check file size is match
    if ($_FILES['file']['size'] !== filesize($_FILES['file']['tmp_name'])) {
        http_response_code(500);
        die('File size mismatch in internal process');
    }
    $file_size = $_FILES['file']['size'];

    require_once __DIR__ . '/../../init.php';

    $link = db_open();

    $query = "INSERT INTO file (file_name, file_size, description,
                    file_hash_crc32, file_hash_md5, file_hash_sha1, file_hash_sha256, file_hash_sha512)
              VALUES ($1, $2, $3) RETURNING id";
    $result = pg_query_params($link, $query, array($file_name, $file_size, $description,
        $file_hash_crc32_user_calc, $file_hash_md5_user_calc, $file_hash_sha1_user_calc,
        $file_hash_sha256_user_calc, $file_hash_sha512_user_calc));
    if (!$result) {
        http_response_code(500);
        die('Could not register file');
    }

    $new_file_id = pg_fetch_result($result, 0, 0);

    $s3_client = s3_client_open();

    $result = $s3_client->putObject([
        'Bucket' => S3_BUCKET,
        'Key' => $new_file_id,
        'Body' => fopen($_FILES['file']['tmp_name'], 'rb'),
        'ChecksumType' => 'FULL_BODY',
    ]);

    if ($result['@metadata']['statusCode'] !== 200) {
        http_response_code(500);

        // Rollback
        pg_query_params($link, 'DELETE FROM file WHERE id = $1', [$new_file_id]);

        die('Could not upload file');
    }

    // Assign artifact
    $query = "INSERT INTO artifact_file_map (artifact_id, file_id) VALUES ($1, $2)";
    $result = pg_query_params($link, $query, array($_POST['artifact_id'], $new_file_id));
    if (!$result) {
        http_response_code(500);

        // Rollback
        $s3_client->deleteObject([
            'Bucket' => S3_BUCKET,
            'Key' => $new_file_id,
        ]);
        pg_query_params($link, 'DELETE FROM file WHERE id = $1', [$new_file_id]);

        die('Could not assign artifact');
    }

    db_close($link);

    // Return see other
    header('Location: /artifact/info.php?id=' . $_POST['artifact_id'], true, 303);
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die('Invalid id');
}

require_once __DIR__ . '/../../init.php';

$link = db_open();

$artifact_data = pg_select($link, 'artifact', ['id' => $_GET['id']]);
if (!$artifact_data || count($artifact_data) === 0) {
    http_response_code(404);
    die('Artifact not found');
}
$artifact_data = $artifact_data[0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require __DIR__ . '/../../ui_elem/head.php'; ?>
    <title>New File - Artifact Vault</title>
</head>
<body>
<?php require __DIR__ . '/../../ui_elem/navbar.php'; ?>

<h1>New File</h1>

    This file will be assigned to <a href="/artifact/info.php?id=<?= $_GET['id'] ?>"><?= escape($artifact_data['display_name']) ?></a>

    <form action="/file/upload.php?id=<?= $_GET['id'] ?>" method="post" enctype="multipart/form-data">
        <div>
            <div>
                <label for="file">File</label>
            </div>
            <div>
                <input type="file" id="file" name="file" required onchange="file_changed()">
            </div>
            <div>
                <label for="file_name">Name</label>
            </div>
            <div>
                <input type="text" id="file_name" name="file_name" required>
            </div>
            <div>
                <label for="description">Description</label>
            </div>
            <div>
                <textarea id="description" name="description"></textarea>
            </div>
            <div>
                <div>
                    <label for="file_hash_crc32">CRC32</label>
                </div>
                <div>
                    <input type="text" id="file_hash_crc32" name="file_hash_crc32">
                </div>
                <div>
                    <label for="file_hash_md5">MD5</label>
                </div>
                <div>
                    <input type="text" id="file_hash_md5" name="file_hash_md5">
                </div>
                <div>
                    <label for="file_hash_sha1">SHA1</label>
                </div>
                <div>
                    <input type="text" id="file_hash_sha1" name="file_hash_sha1">
                </div>
                <div>
                    <label for="file_hash_sha256">SHA256</label>
                </div>
                <div>
                    <input type="text" id="file_hash_sha256" name="file_hash_sha256">
                    <button type="button" onclick="calc_sha256()">Calc</button>
                </div>
                <div>
                    <label for="file_hash_sha512">SHA512</label>
                </div>
                <div>
                    <input type="text" id="file_hash_sha512" name="file_hash_sha512">
                </div>
            </div>
            <div>
                <input type="hidden" name="artifact_id" value="<?= $_GET['id'] ?>">
                <button type="submit">Upload</button>
            </div>
        </div>
    </form>

    <script>
        function file_changed() {
            if (document.getElementById('file').files.length !== 1) {
                return;
            }

            // Set filename
            const file = document.getElementById('file').files[0];
            document.getElementById('file_name').value = file.name;
        }

        function calc_sha256() {
            if (document.getElementById('file').files.length === 0) {
                alert('Please select a file first');
                return;
            }

            // Use browser cryptoAPI to calc SHA256
            const file = document.getElementById('file').files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                const buffer = e.target.result;
                const hashBuffer = crypto.subtle.digest('SHA-256', buffer);
                hashBuffer.then(function(hash) {
                    const hashArray = Array.from(new Uint8Array(hash));
                    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
                    document.getElementById('file_hash_sha256').value = hashHex;
                });
            };
            reader.readAsArrayBuffer(file);
        }
    </script>
</body>
</html>