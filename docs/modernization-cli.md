# QuickInstall CLI

QuickInstall CLI creates disposable local phpBB boards for extension, style, and compatibility testing.

You do not need MAMP, WAMP, XAMPP, or a local Apache/MySQL setup. QuickInstall uses Docker for the board runtime and stores generated boards under `.qi/`.

## Quick Start

Install requirements:

- Docker Desktop (must be running)
- PHP CLI
- Composer, used to download phpBB source packages
- Git, required for Git sources

From the QuickInstall project root:

```bash
php bin/qi init
php bin/qi board:create test --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev
php bin/qi board:start test
```

Open:

```text
http://localhost:8081/
```

Admin login:

```text
admin / password
```

That is the normal workflow. `board:create` downloads the requested phpBB source if needed, writes Docker config, and prepares the board. `board:start` starts Docker, installs phpBB, applies the selected seed preset once, and waits until the board URL responds before printing the final URL.

## Common Recipes

Create a small empty board:

```bash
php bin/qi board:create clean --phpbb 3.3 --db mariadb --port 8081 --populate none
php bin/qi board:start clean
```

Create a board with extension-development fixtures:

```bash
php bin/qi board:create extdev --phpbb 3.3.17 --db mariadb --port 8082 --populate extension-dev
php bin/qi board:start extdev
```

Create an older supported phpBB 3.2 board:

```bash
php bin/qi board:create old --phpbb 3.2 --db mariadb --port 8083 --populate tiny
php bin/qi board:start old
```

Create an experimental master branch board:

```bash
php bin/qi board:create alpha --phpbb master --db mariadb --port 8084 --populate tiny
php bin/qi board:start alpha
```

Create an SQLite board without fixtures:

```bash
php bin/qi board:create sqlite --phpbb 3.3 --db sqlite --port 8085 --populate none
php bin/qi board:start sqlite
```

List boards:

```bash
php bin/qi board:list
```

Stop or remove a board:

```bash
php bin/qi board:stop test
php bin/qi board:destroy test
```

`board:destroy` removes the board files, Docker runtime files, database files, local Docker containers, local Docker image, and board registry entry.

## Fixture Presets

Fixture seeding populates a board with categories, forums, users, topics, and replies. It does not cover groups, permission matrices, or attachments.

Use `--populate <preset>` during `board:create`:

```bash
php bin/qi board:create test --populate extension-dev
```

Available presets:

```text
none           no seed data
tiny           3 users, 1 category, 2 forums, 2 topics, 2 replies per topic
extension-dev 10 users, 2 categories, 6 forums, 25 topics, 10 replies per topic
load-test     100 users, 4 categories, 20 forums, 100 topics, 20 replies per topic
random        random counts up to load-test size
```

Fixture seeding is supported for MariaDB, MySQL, and PostgreSQL boards. SQLite boards currently support `--populate none` only; phpBB's posting and permission APIs can hold SQLite write locks too long for reliable fixture generation.

You can seed again manually:

```bash
php bin/qi board:seed test --preset extension-dev --seed 1
```

Replace seed data:

```bash
php bin/qi board:seed test --preset extension-dev --seed 1 --replace
```

Remove seed data:

```bash
php bin/qi board:seed test --preset extension-dev --seed 1 --reset
```

`--seed` is a repeatable random seed number. Use the same seed to get the same fixture shape.

## Extensions

Put downloaded extensions under `extensions/`:

```text
extensions/vendor/extname/composer.json
```

Mount into a board:

```bash
php bin/qi ext:mount test extensions/vendor/extname
```

QuickInstall reads the extension `composer.json` name, such as `vendor/extname`, and bind-mounts it to:

```text
/var/www/html/ext/vendor/extname
```

Edits in `extensions/vendor/extname` are reflected in the board immediately. If the board is running, QuickInstall recreates the web container and purges phpBB cache.

List and unmount extensions:

```bash
php bin/qi ext:list test
php bin/qi ext:unmount test vendor/extname
```

Copy instead of bind-mount:

```bash
php bin/qi ext:mount test extensions/vendor/extname --copy
```

## Styles

Put downloaded styles under `styles/`:

```text
styles/stylename/style.cfg
```

Mount into a board:

```bash
php bin/qi style:mount test styles/stylename
```

QuickInstall uses the style folder name and bind-mounts it to:

```text
/var/www/html/styles/stylename
```

List and unmount styles:

```bash
php bin/qi style:list test
php bin/qi style:unmount test stylename
```

Copy instead of bind-mount:

```bash
php bin/qi style:mount test styles/stylename --copy
```

## Supported phpBB Versions

Show supported selectors:

```bash
php bin/qi phpbb:list
```

Supported selectors:

```text
latest        defaults to the supported 3.3 line
3.3           latest 3.3.x Composer release
3.3.x         exact 3.3 tag, such as 3.3.17
3.2           latest 3.2.x Composer release
3.2.x         exact 3.2 tag, such as 3.2.11
4.0.x/master  experimental
3.0/3.1       unsupported by the modern Docker CLI
```

phpBB 3.0 and 3.1 are intentionally not supported by the Docker CLI. They are too old for this modern installer-based flow.

## Sources

Most users do not need source commands. `board:create --phpbb <version>` automatically registers and downloads normal Composer release sources.

Useful source commands:

```bash
php bin/qi source:list
php bin/qi source:fetch 3.3.17
```

Use explicit Git sources for custom branches or forks:

```bash
php bin/qi source:add master --git --url https://github.com/phpbb/phpbb.git
php bin/qi source:fetch master
```

Fetched sources live under:

```text
.qi/sources/phpbb-<source>
```

## Where Files Go

Generated state:

```text
.qi/boards/<name>       installed phpBB board files
.qi/runtime/<name>      Docker Compose, Dockerfile, installer config
.qi/db/<name>           database files
.qi/sources/<source>    downloaded phpBB source
```

User-managed drop zones:

```text
extensions/
styles/
```

## Troubleshooting

Docker command fails:

```text
Check that Docker Desktop is running and that the docker command works in this terminal.
```

Composer command fails:

```text
Install Composer or make sure composer is available in PATH.
```

Port already in use:

```bash
php bin/qi board:create test --port 8090
```

See container logs:

```bash
docker compose -f .qi/runtime/test/compose.yml logs web
docker compose -f .qi/runtime/test/compose.yml logs db
```

Reset a board completely:

```bash
php bin/qi board:destroy test
php bin/qi board:create test --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev
php bin/qi board:start test
```
