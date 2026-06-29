# QuickInstall Modern CLI Prototype

This is the first step toward making QuickInstall a board factory instead of a web request that directly drives phpBB internals.

The legacy web app remains unchanged. The new CLI writes all generated state to `.qi/`.

## Commands

```bash
php bin/qi init
php bin/qi source:add 3.3.17
php bin/qi source:fetch 3.3.17
php bin/qi board:create test --phpbb 3.3.17 --db mariadb --port 8081 --populate extension-dev
docker compose -f .qi/runtime/test/compose.yml up -d
```

`source:fetch` runs Composer or Git and downloads phpBB into `.qi/sources/phpbb-<version>`.

After Docker starts, open:

```text
http://localhost:8081/
```

Admin login defaults:

```text
admin / password
```

## Source Model

Registered sources are stored in `.qi/sources.json`.

Composer sources use `phpbb/phpbb`:

```bash
php bin/qi source:add 3.3.17
```

Git sources use the phpBB repository:

```bash
php bin/qi source:add master --git --url https://github.com/phpbb/phpbb.git
```

Fetched source code is expected at:

```text
.qi/sources/phpbb-<version>
```

## Board Model

`board:create` creates:

```text
.qi/boards/<name>
.qi/runtime/<name>/compose.yml
.qi/runtime/<name>/Dockerfile
.qi/runtime/<name>/entrypoint.sh
.qi/runtime/<name>/install-config.yml
.qi/db/<name>
```

The container copies the selected phpBB source into the board directory on first boot. If `install/phpbbcli.php` exists, it runs phpBB's installer CLI with the generated YAML config.

## Current Limits

- phpBB source fetch requires `composer` for release sources and `git` plus `composer` for Git sources.
- Docker images are generic `php:<version>-apache` builds with DB extensions installed at build time.
- phpBB 3.2+ installer CLI is the intended path. Older branches still need a legacy installer adapter.
- Fixture population is represented as metadata only. Seeder extraction from `includes/qi_populate.php` is next.
- The web UI does not call the CLI yet.

## Next Implementation Steps

1. Add `board:start`, `board:stop`, `board:destroy`, and `board:list`.
2. Add branch-specific installer adapters for phpBB 3.0/3.1.
3. Extract fixture seeding behind `qi board:seed`.
4. Put web UI behind the same board/source services.
