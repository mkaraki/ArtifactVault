<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/_config.php';

function db_open() {
    // Create postgres link
    $link = pg_connect(DB_CON_STRING);
    if (!$link) {
        die('Could not connect to DB');
    }
    return $link;
}

function db_close($link): void
{
    pg_close($link);
}

function escape($string): string
{
    if ($string === null) {
        return 'NULL';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

function s3_client_open(): Aws\S3\S3Client
{
    return new Aws\S3\S3Client(S3_CONFIG);
}