CREATE TABLE artifact_dependency_map(
    artifact_id BIGINT NOT NULL,
    dependent_artifact_id BIGINT NOT NULL,
    is_optional BOOLEAN NOT NULL,
    PRIMARY KEY(artifact_id, dependent_artifact_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(dependent_artifact_id) REFERENCES artifact(id)
);

CREATE TABLE artifact_tree_map(
    artifact_id BIGINT NOT NULL,
    parent_artifact_id BIGINT NOT NULL,
    PRIMARY KEY(artifact_id, parent_artifact_id),
    FOREIGN KEY(artifact_id) REFERENCES artifact(id),
    FOREIGN KEY(parent_artifact_id) REFERENCES artifact(id)
);