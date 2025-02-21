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
    file_hash_crc32  bytea,
    file_hash_md5    bytea,
    file_hash_sha1   bytea,
    file_hash_sha256 bytea,
    file_hash_sha512 bytea
);

CREATE TABLE artifact_file_map(
    artifact_id BIGINT NOT NULL,
    file_id BIGINT NOT NULL,
    PRIMARY KEY(artifact_id, file_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(file_id) REFERENCES file(id)
);

CREATE TABLE artifact_deprecation_map(
    artifact_id BIGINT NOT NULL,
    deprecated_artifact_id BIGINT NOT NULL,
    reason TEXT NOT NULL,
    PRIMARY KEY(artifact_id, deprecated_artifact_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(deprecated_artifact_id) REFERENCES artifact(id)
);

CREATE TABLE base_system(
    id BIGSERIAL PRIMARY KEY,
    display_name TEXT NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE artifact_base_system_map(
    artifact_id BIGINT NOT NULL,
    base_system_id BIGINT NOT NULL,
    PRIMARY KEY(artifact_id, base_system_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(base_system_id) REFERENCES base_system(id)
);
