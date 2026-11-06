# Schema deployment maintenance

The deployment still creates a recovery snapshot before pending migrations. For a fully InnoDB database, `mysqldump --single-transaction` runs while the application stays online. Maintenance starts only after the snapshot succeeds, immediately before migration and the release switch. Failure hooks restore the application and release the deployment lock.

The snapshot is a consistent point at the start of the database transaction, not at the later release switch. If a recovery restores that snapshot, writes made after its transaction began require reconciliation. Existing provider backups remain separate. No schema changes should run in parallel with a snapshot; deployments are serialized.

The deployment checks storage engines first. Any non-transactional table retains the previous behavior: maintenance before the snapshot, so those tables are quiescent. An engine-check or online-snapshot failure aborts before entering maintenance. Releases without pending migrations still skip both snapshot and maintenance.

The regression test executes the registered snapshot task against a fake command runner: transactional, non-transactional, failed snapshot and no-pending-migration scenarios. It never connects to a database or executes backup commands.
