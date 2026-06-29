# QuickInstall Modern CLI Prototype

This is the first step toward making QuickInstall a board factory instead of a web request that directly drives phpBB internals.

The legacy web app remains unchanged. The new CLI writes all generated state to `.qi/`.

## Commands

```bash
php bin/qi init
php bin/qi source:add 3.3.17
php bin/qi source:fetch 3.3.17
php bin/qi board:create test --phpbb 3.3.17 --db mariadb --port 8081 --populate extension-dev
php bin/qi board:start test
php bin/qi board:seed test --preset extension-dev --seed 1
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

Useful board commands:

```bash
php bin/qi board:list
php bin/qi board:start test
php bin/qi board:stop test
php bin/qi board:destroy test
```

`board:destroy` stops containers and removes generated board files, runtime files, database files, and registry metadata for that board.

`board:list` shows each registered board plus Docker status:

```text
test  running  3.3.17  PHP 8.1  mariadb  http://localhost:8081/
```

Statuses are `running`, `stopped`, `partial`, `missing`, or `error`.

## Fixture Seeding

Seed an installed, running board:

```bash
php bin/qi board:seed test --preset extension-dev --seed 1
```

Available presets:

```text
tiny           3 users, 2 topics, 2 replies per topic
extension-dev 10 users, 5 topics, 4 replies per topic
load-test     50 users, 25 topics, 10 replies per topic
```

The first seeder targets phpBB 3.2+ style boards and uses phpBB APIs inside the `web` container: `user_add()` for users and `submit_post()` for topics/replies.

## Current Limits

- phpBB source fetch requires `composer` for release sources and `git` plus `composer` for Git sources.
- Docker images are generic `php:<version>-apache` builds with DB extensions installed at build time.
- phpBB 3.2+ installer CLI is the intended path. Older branches still need a legacy installer adapter.
- Fixture population currently supports users, topics, and replies. It does not yet cover categories, permissions matrices, custom groups, styles, or attachments.
- The web UI does not call the CLI yet.

## Next Implementation Steps

1. Add branch-specific installer adapters for phpBB 3.0/3.1.
2. Expand `board:seed` for categories, forums, groups, permissions, styles, and attachments.
3. Put web UI behind the same board/source services.
