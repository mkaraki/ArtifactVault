<?php
const DB_CON_STRING = "host=db dbname=artifact user=artifact password=artifact";

const S3_BUCKET = 'artifact-vault';

const S3_CONFIG = [
    'version' => 'latest',
    'region' => 'us-east-1',
    'endpoint' => 'http://s3:9000',
    'use_path_style_endpoint' => true,
    'credentials' => [
        'key' => 'minioadmin',
        'secret' => 'minioadmin',
    ],
];