CREATE TABLE artifact(
    id BIGSERIAL PRIMARY KEY,
    display_name TEXT NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE file(
    id BIGSERIAL PRIMARY KEY,
    file_name TEXT NOT NULL,
    description TEXT NOT NULL,
    file_size BIGINT NOT NULL,
    file_hash_crc32 VARBINARY(5) NOT NULL,
    file_hash_md5 VARBINARY(16) NOT NULL,
    file_hash_sha1 VARBINARY(20) NOT NULL,
    file_hash_sha256 VARBINARY(32) NOT NULL,
    file_hash_sha512 VARBINARY(64) NOT NULL
);

CREATE artifact_file_map(
    artifact_id BIGINT NOT NULL,
    file_id BIGINT NOT NULL,
    PRIMARY KEY(artifact_id, file_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(file_id) REFERENCES file(id)
);

CREATE artifact_deprecation_map(
    artifact_id BIGINT NOT NULL,
    deprecated_artifact_id BIGINT NOT NULL,
    reason TEXT NOT NULL,
    PRIMARY KEY(artifact_id, deprecated_artifact_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(deprecated_artifact_id) REFERENCES artifact(id)
);

CREATE base_system(
    id BIGSERIAL PRIMARY KEY,
    display_name TEXT NOT NULL,
    description TEXT NOT NULL
);

CREATE artifact_base_system_map(
    artifact_id BIGINT NOT NULL,
    base_system_id BIGINT NOT NULL,
    PRIMARY KEY(artifact_id, base_system_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(base_system_id) REFERENCES base_system(id)
);
