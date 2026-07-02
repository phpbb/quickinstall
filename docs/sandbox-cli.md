# QuickInstall CLI

QuickInstall CLI creates disposable local phpBB boards for extension, style, and development testing.

You do not need MAMP, WAMP, XAMPP, or any local Apache/MySQL setup. QuickInstall uses Docker for the board runtime and stores generated boards under `.qi/`.

## Quick Start

Install requirements:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (must be installed and running)
- PHP CLI
- Git, only required for Git sources

From the QuickInstall project root:

Initialize if this is your first time:

```bash
php bin/qi init
```

Create your first board:

```bash
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

If you ever need help with commands, run:

```bash
php bin/qi help
```

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

List boards (shows all created boards and their statuses):

```bash
php bin/qi board:list
```

Stop or remove a board:

```bash
php bin/qi board:stop test
php bin/qi board:destroy test
```

`board:destroy` removes the board files, Docker runtime files, database files, local Docker containers, local Docker image, and board registry entry.

Board names are unique. To reuse a name with a different setup, destroy it first:

```bash
php bin/qi board:destroy test
php bin/qi board:create test --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev
```

Or recreate it in one command:

```bash
php bin/qi board:create test --phpbb 3.3 --db mariadb --port 8081 --populate extension-dev --replace
```

## Fixture Presets

Fixture seeding populates a board with categories, forums, users, topics, and replies. For non-tiny presets, it also adds a few seeded users to Global Moderators and Newly Registered Users. Newly registered users are kept at zero posts. It does not create custom groups, permission matrices, or attachments.

Use `--populate <preset>` during `board:create`:

```bash
php bin/qi board:create test --populate extension-dev
```

Available presets:

```text
none           no seed data
tiny           3 users, 1 category, 2 forums, 2 topics, 2 replies per topic
extension-dev  10 users, 2 categories, 6 forums, 25 topics, 10 replies per topic
load-test      100 users, 4 categories, 20 forums, 100 topics, 20 replies per topic
random         random counts up to load-test size
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

Mount every extension found under a directory:

```bash
php bin/qi ext:mount test extensions --recursive
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

`--copy` is only supported for one extension at a time. Recursive mounting always uses bind mounts.

By default, extension sources must live under `extensions/`. To mount a trusted extension from somewhere else on your machine:

```bash
php bin/qi ext:mount test /path/to/vendor/extname --allow-external
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

Mount every style found under a directory:

```bash
php bin/qi style:mount test styles --recursive
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

`--copy` is only supported for one style at a time. Recursive mounting always uses bind mounts.

By default, style sources must live under `styles/`. To mount a trusted style from somewhere else on your machine:

```bash
php bin/qi style:mount test /path/to/stylename --allow-external
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
php bin/qi source:remove 3.3.17
php bin/qi source:prune
```

`source:list` shows whether each source has been downloaded and which boards use it. `source:remove` deletes one source from `.qi/sources/` and removes it from the source registry. It refuses to remove a source still used by a board unless `--force` is passed.

`source:prune` removes all unused sources. It never removes sources referenced by existing boards.

Use explicit Git sources for custom branches or forks:

```bash
php bin/qi source:add master --git --url https://github.com/phpbb/phpbb.git
php bin/qi source:fetch master
```

Custom Git URLs can run Composer code on your host during fetch. QuickInstall only accepts the official phpBB Git URL by default. For a trusted fork, opt in explicitly:

```bash
php bin/qi source:add my-branch --git --url https://github.com/example/phpbb.git --allow-external
php bin/qi source:fetch my-branch
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

## Safety Defaults

- Board web ports bind to `127.0.0.1`, not every network interface.
- `board:create` refuses to overwrite an existing board unless `--replace` is used.
- `board:create` rejects ports already registered to another board or already in use on the host.
- `ext:mount` and `style:mount` only use `extensions/` and `styles/` unless `--allow-external` is used.
- Custom Git source URLs require `--allow-external`; only use trusted forks.

## Troubleshooting

Docker command fails:

```text
Check that Docker Desktop is running and that the docker command works in this terminal.
```

Composer command fails:

```text
QuickInstall uses composer from PATH first, then composer.phar from the project root. Restore composer.phar or install Composer if both are missing.
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
