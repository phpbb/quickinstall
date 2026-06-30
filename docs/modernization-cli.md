# QuickInstall Modern CLI Prototype

This is the first step toward making QuickInstall a board factory instead of a web request that directly drives phpBB internals.

The legacy web app remains unchanged. The new CLI writes all generated state to `.qi/`.

## Commands

```bash
php bin/qi init
php bin/qi board:create test --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev
php bin/qi board:start test
```

`board:create --phpbb <version>` validates supported phpBB selectors, then automatically registers and fetches missing Composer-based phpBB sources into `.qi/sources/phpbb-<source>`.

`--populate extension-dev` seeds the board once during `board:start`, after phpBB has installed successfully. Use `--populate none` to skip automatic seeding.

After Docker starts, open:

```text
http://localhost:8081/
```

Admin login defaults:

```text
admin / password
```

## Source Model

Registered sources are stored in `.qi/sources.json`. For normal released versions, you can skip source commands and let `board:create --phpbb <version>` register/fetch automatically.

Composer sources use `phpbb/phpbb`. Explicit source commands are still useful when you want to fetch source ahead of time:

```bash
php bin/qi source:add 3.3.17
```

Git sources use the phpBB repository:

```bash
php bin/qi source:add master --git --url https://github.com/phpbb/phpbb.git
php bin/qi source:fetch master
```

Use explicit `source:add --git` for branches, forks, and custom URLs. Automatic `board:create` fetching assumes Composer release sources.

Fetched source code is expected at:

```text
.qi/sources/phpbb-<version>
```

## Version Selection

Show supported selectors:

```bash
php bin/qi phpbb:list
```

Supported selectors:

```text
latest        Supported stable line, currently constrained to 3.3.*
3.3           Latest 3.3.x Composer release
3.3.x         Exact 3.3 tag, such as 3.3.17
3.2           Latest 3.2.x Composer release
3.2.x         Exact 3.2 tag, such as 3.2.11
4.0.x/master  Experimental
3.0/3.1       Unsupported by modern Docker CLI
```

Unsupported versions fail before source download:

```text
phpBB 3.1.12 is not supported by the modern Docker CLI. Use phpBB 3.2+ or the legacy web app for phpBB 3.0/3.1.
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
test  running  3.3.17  PHP 8.1  mariadb  populate:extension-dev  http://localhost:8081/
```

Statuses are `running`, `stopped`, `partial`, `missing`, or `error`.

## Extension Drop Zone

Downloaded extensions can be unzipped into the visible local extension library:

```text
extensions/
```

Example layout:

```text
extensions/phpbb/pages/composer.json
extensions/vendor/extname/composer.json
```

Mount an extension into a board:

```bash
php bin/qi ext:mount test extensions/phpbb/pages
```

The CLI reads `composer.json` and uses its `name`, such as `phpbb/pages`, to create the normal phpBB target inside the board container:

```text
/var/www/html/ext/phpbb/pages
```

Mounts use Docker bind mounts by default, so edits in `extensions/phpbb/pages` are reflected in the board immediately and phpBB generates normal web asset paths. To copy files instead:

```bash
php bin/qi ext:mount test extensions/phpbb/pages --copy
```

When a running board is mounted/unmounted, the CLI purges phpBB's cache so the ACP extension list refreshes.

List mounted extensions:

```bash
php bin/qi ext:list test
```

Unmount an extension from a board:

```bash
php bin/qi ext:unmount test phpbb/pages
```

## Fixture Seeding

Seed an installed, running board:

```bash
php bin/qi board:seed test --preset extension-dev --seed 1
php bin/qi board:seed test --seed 1 --reset
php bin/qi board:seed test --preset extension-dev --seed 1 --replace
```

This is separate from `board:create --populate`. Manual `board:seed` always runs when called. Automatic `--populate` runs once on `board:start` and writes a marker under `.qi/runtime/<board>/`.

`--reset` removes seed-generated users, forums/categories, topics, and replies for the selected `--seed`.

`--replace` runs `--reset` first, then creates fresh data with the selected preset and seed.

Available presets:

```text
tiny           3 users, 1 category, 2 forums, 2 topics, 2 replies per topic
extension-dev 10 users, 2 categories, 6 forums, 25 topics, 10 replies per topic
load-test     100 users, 4 categories, 20 forums, 100 topics, 20 replies per topic
random        up to 100 users, up to 4 categories, up to 20 forums, up to 100 topics, up to 20 replies per topic
```

The seeder targets phpBB 3.2+ style boards and uses phpBB APIs inside the `web` container. It creates categories/forums directly, creates users through `user_add()`, and creates topics/replies through `submit_post()`. Topic and reply authors are chosen randomly from the seeded users for the selected seed. Seeded topic titles use the DB topic ID suffix, so phpBB's default demo topic is reflected in numbering. After seed/reset, user post totals are recalculated from approved counted posts. The `random` preset uses `load-test` as caps and chooses counts from `1..cap` for users/categories/forums/topics and `0..cap` for replies.

## Current Limits

- phpBB source fetch requires `composer` for release sources and `git` plus `composer` for Git sources.
- Docker images are generic `php:<version>-apache` builds with DB extensions installed at build time.
- phpBB 3.2+ installer CLI is the supported path. phpBB 3.0/3.1 are intentionally out of scope for this modern CLI.
- Fixture population currently supports categories, forums, users, topics, and replies. It does not cover groups, permissions matrices, styles, or attachments.
- The web UI does not call the CLI yet.

## Next Implementation Steps

1. Put web UI behind the same board/source services.
