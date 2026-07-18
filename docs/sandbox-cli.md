# QuickInstall CLI

QuickInstall CLI creates disposable local phpBB boards for extension, style, and development testing.

You do not need MAMP, WAMP, XAMPP, or any local Apache/MySQL setup. QuickInstall uses Docker for the board runtime and stores generated boards under `.qi/`.

## Quick Start

Install requirements:

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (must be installed and running)
- PHP 8.0 or newer for the CLI command
- Git, only required for Git sources

From the QuickInstall project root:

Initialize if this is your first time:

```bash
php bin/qi init
```

Create your first board:

```bash
php bin/qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate none
```

When creation finishes, QuickInstall asks whether to start the board.

Open:

```text
http://localhost:8081/
```

Admin login:

```text
admin / password
```

That is the normal workflow. `board:create` downloads the requested phpBB source if needed, writes Docker config, prepares the board, and offers to start it. Starting a board starts the containers with Docker Compose, installs phpBB, applies the selected seed preset once, and waits until the board URL responds before printing the final URL.

If you ever need help with commands, run:

```bash
php bin/qi help
```

If you prefer a browser workflow, start the QuickInstall Dashboard UI:

```bash
php bin/qi ui:start
```

QuickInstall opens the Dashboard in your default browser. If that fails, open the URL printed by the command:

```text
http://127.0.0.1:8079/
```

## Commands by OS

The examples in this guide use the portable command form:

```bash
php bin/qi <command>
```

It works on macOS, Linux, and Windows when PHP is available in `PATH`.

Windows also includes a `qi.cmd` launcher. From PowerShell:

```powershell
.\bin\qi.cmd <command>
```

From Command Prompt:

```batch
bin\qi.cmd <command>
```

Arguments and options are identical with every launcher. Run commands from the QuickInstall project root. Windows drive-letter paths, UNC paths, backslashes, spaces, and case-insensitive filesystem paths are supported.

Docker Desktop on Windows must use its WSL 2 or Hyper-V Linux-container backend rather than Windows containers.

## Common Recipes

Create a small empty board:

```bash
php bin/qi board:create clean --phpbb 3.3 --db mariadb --port 8081 --populate none
```

Create a board with extension-development fixtures:

```bash
php bin/qi board:create extdev --phpbb 3.3.17 --db mariadb --port 8082 --populate extension-dev
```

Create a board with phpBB debug output enabled:

```bash
php bin/qi board:create debug --phpbb 3.3 --db mariadb --port 8085 --populate extension-dev --debug
```

Create an older supported phpBB 3.2 board:

```bash
php bin/qi board:create old --phpbb 3.2 --db mariadb --port 8083 --populate tiny
```

Create an experimental master branch board:

```bash
php bin/qi board:create alpha --phpbb master --db mariadb --port 8084 --populate tiny
```

List boards (shows all created boards and their statuses):

```bash
php bin/qi board:list
```

Start a board:

```bash
php bin/qi board:start demo
```

Stop or remove a board:

```bash
php bin/qi board:stop demo
php bin/qi board:destroy demo
```

`board:destroy` removes the board files, Docker runtime files, database files, local Docker containers, local Docker image, and board registry entry.

Board names are unique. To reuse a name with a different setup, destroy it first:

```bash
php bin/qi board:destroy demo
php bin/qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate tiny
```

Or recreate it in one command:

```bash
php bin/qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate tiny --replace
```

## Fixture Presets

Fixture seeding populates a board with categories, forums, users, topics, and replies. For non-tiny presets, it also adds a few seeded users to Global Moderators and Newly Registered Users. Newly registered users are kept at zero posts. It does not create custom groups, permission matrices, or attachments.

Use `--populate <preset>` during `board:create`:

```bash
php bin/qi board:create demo --populate extension-dev
```

Available presets:

| Preset          | Description                                                          |
|-----------------|----------------------------------------------------------------------|
| `none`          | No seed data                                                         |
| `tiny`          | 3 users, 1 category, 2 forums, 2 topics, 2 replies per topic         |
| `extension-dev` | 10 users, 2 categories, 6 forums, 25 topics, 10 replies per topic    |
| `load-test`     | 100 users, 4 categories, 20 forums, 100 topics, 20 replies per topic |
| `random`        | Random counts up to load-test size                                   |

Fixture seeding is supported for MariaDB, MySQL, and PostgreSQL boards. SQLite boards currently support `--populate none` only; phpBB's posting and permission APIs can hold SQLite write locks too long for reliable fixture generation.

You can seed again manually:

```bash
php bin/qi board:seed demo --preset extension-dev --seed 1
```

Replace seed data:

```bash
php bin/qi board:seed demo --preset extension-dev --seed 1 --replace
```

Remove seed data:

```bash
php bin/qi board:seed demo --preset extension-dev --seed 1 --reset
```

`--seed` is a repeatable random seed number. Use the same seed to get the same fixture shape.

## Extensions

Put downloaded extensions under `customisations/`:

```text
customisations/vendor/extname/composer.json
```

Mount into a board:

```bash
php bin/qi ext:mount demo customisations/vendor/extname
```

Mount every extension found under a directory:

```bash
php bin/qi ext:mount demo customisations --recursive
```

QuickInstall reads the extension `composer.json` name, such as `vendor/extname`, and bind-mounts it to:

```text
/var/www/html/ext/vendor/extname
```

Edits in `customisations/vendor/extname` are reflected in the board immediately. If the board is running, QuickInstall recreates the web container and purges phpBB cache.

List and unmount extensions:

```bash
php bin/qi ext:list demo
php bin/qi ext:unmount demo vendor/extname
```

Copy instead of bind-mount:

```bash
php bin/qi ext:mount demo customisations/vendor/extname --copy
```

`--copy` is only supported for one extension at a time. Recursive mounting always uses bind mounts.

By default, extension sources must live under `customisations/`. To mount a trusted extension from somewhere else on your machine:

```bash
php bin/qi ext:mount demo /path/to/vendor/extname --allow-external
```

On Windows, quote external paths containing spaces:

```powershell
.\bin\qi.cmd ext:mount demo "C:\Path\To\My Extensions\vendor\extname" --allow-external
```

## Styles

Put downloaded styles under `customisations/`:

```text
customisations/stylename/style.cfg
```

Mount into a board:

```bash
php bin/qi style:mount demo customisations/stylename
```

Mount every style found under a directory:

```bash
php bin/qi style:mount demo customisations --recursive
```

QuickInstall uses the style folder name and bind-mounts it to:

```text
/var/www/html/styles/stylename
```

List and unmount styles:

```bash
php bin/qi style:list demo
php bin/qi style:unmount demo stylename
```

Copy instead of bind-mount:

```bash
php bin/qi style:mount demo customisations/stylename --copy
```

`--copy` is only supported for one style at a time. Recursive mounting always uses bind mounts.

By default, style sources must live under `customisations/`. To mount a trusted style from somewhere else on your machine:

```bash
php bin/qi style:mount demo /path/to/stylename --allow-external
```

On Windows, quote external paths containing spaces:

```powershell
.\bin\qi.cmd style:mount demo "C:\Path\To\My Styles\stylename" --allow-external
```

## Supported phpBB Versions

Show supported selectors:

```bash
php bin/qi phpbb:list
```

Supported selectors:

| Selector           | Resolves to                          |
|--------------------|--------------------------------------|
| `latest`           | Defaults to the supported 3.3 line   |
| `3.3`              | Latest 3.3.x Composer release        |
| `3.3.x`            | Exact 3.3 tag, such as 3.3.17        |
| `3.2`              | Latest 3.2.x Composer release        |
| `3.2.x`            | Exact 3.2 tag, such as 3.2.11        |
| `4.0.x` / `master` | Experimental                         |
| `3.0` / `3.1`      | Unsupported by QuickInstall CLI      |

In the `3.3.x` and `3.2.x` examples above, `x` is a placeholder. Use the exact phpBB release tag you want, such as `3.3.17`, whenever you know it. If you literally enter `3.3.x`, `3.2.x`, `3.3`, or `3.2`, QuickInstall treats that as a convenience fallback and resolves the newest matching release available from Composer.

QuickInstall CLI intentionally does not support phpBB 3.0 and 3.1. They are too old for this modern installer-based flow.

## Sources

Most users do not need source commands. `board:create --phpbb <version>` automatically registers and downloads normal Composer release sources.

Show downloaded phpBB sources:

```bash
php bin/qi source:list
```
`source:list` shows whether each source has been downloaded and which boards use it

Register and download a phpBB source:

```bash
php bin/qi source:fetch 3.3.17
```

`source:fetch` simultaneously registers and downloads a phpBB source into `.qi/sources`.

Delete a phpBB source: 

```bash
php bin/qi source:remove 3.3.17
```

`source:remove` deletes one source from `.qi/sources/` and removes it from the source registry. It refuses to remove a source still used by a board unless `--force` is passed.

Delete all unused phpBB sources: 

```bash
php bin/qi source:prune
```

`source:prune` removes all unused sources. It never removes sources referenced by existing boards.

Use explicit Git sources for custom branches or forks:

```bash
php bin/qi source:fetch master --git --url https://github.com/phpbb/phpbb.git
```

Custom Git URLs can run Composer code on your host during fetch. QuickInstall only accepts the official phpBB Git URL by default. For a trusted fork, opt in explicitly:

```bash
php bin/qi source:fetch my-branch --git --url https://github.com/example/phpbb.git --allow-external
```

Fetched sources live under:

```text
.qi/sources/phpbb-<source>
```

## Dashboard UI

The QuickInstall Dashboard UI provides a local browser interface for the same workflows exposed by the CLI. It is served by PHP's built-in web server and backed by the same `.qi/` workspace. The Dashboard UI can be started, checked, restarted, and stopped on macOS, Linux, or native Windows.

Start the UI:

```bash
php bin/qi ui:start
```

QuickInstall opens the Dashboard in your default browser after the local server is ready. The default URL is:

```text
http://127.0.0.1:8079/
```

To start it without opening a browser:

```bash
php bin/qi ui:start --no-open
```

Use a different local port:

```bash
php bin/qi ui:start --port 8088
```

Check or stop the tracked Dashboard UI server:

```bash
php bin/qi ui:status
php bin/qi ui:stop
```

Restart it:

```bash
php bin/qi ui:restart
```

`ui:restart` also opens the Dashboard when the new server is ready. Pass `--no-open` to either `ui:start` or `ui:restart` when running headlessly or when you want to open the printed URL yourself.

Supported Dashboard UI server hosts are loopback-only:

```bash
php bin/qi ui:start --host 127.0.0.1
php bin/qi ui:start --host localhost
php bin/qi ui:start --host ::1
```

## Where Files Go

Generated state:

| Path                     | Contents                                     |
|--------------------------|----------------------------------------------|
| `.qi/boards/<name>`      | Installed phpBB board files                  |
| `.qi/runtime/<name>`     | Docker Compose, Dockerfile, installer config |
| `.qi/db/<name>`          | Database files                               |
| `.qi/sources/<source>`   | Downloaded phpBB source                      |
| `.qi/runtime/ui.json`    | Tracked Dashboard UI server state            |
| `.qi/runtime/ui.log`     | Dashboard UI server output log               |
| `.qi/runtime/ui.log.err` | Windows Dashboard UI server error log        |
| `.qi/cache/`             | Cached update-check metadata                 |

User-managed drop zone:

```text
customisations/
```

## Safety Defaults

- Board web ports bind to `127.0.0.1`, not every network interface.
- `board:create` refuses to overwrite an existing board unless `--replace` is used.
- `board:create` rejects ports already registered to another board or already in use on the host.
- `ext:mount` and `style:mount` only use `customisations/` unless `--allow-external` is used.
- Custom Git source URLs require `--allow-external`; only use trusted forks.
- The Dashboard UI server only accepts loopback hosts (`127.0.0.1`, `localhost`, or `::1`) and rejects non-local requests.
- `ui:start` refuses ports already in use on the selected loopback host.
- Dashboard UI form submissions use CSRF tokens and only accept local origins or referrers.

## Troubleshooting

If QuickInstall starts but a command is not working as expected, run the environment check from the project root:

```bash
php bin/qi doctor
```

On Windows PowerShell, you can use the Windows launcher instead:

```powershell
.\bin\qi.cmd doctor
```

Every check should report `OK`; failures identify the missing or unavailable requirement.

#### QuickInstall does not start on Windows

First, check whether PHP is available:

```powershell
php --version
```

If Windows does not recognize `php`, install PHP 8 or newer and add the directory containing `php.exe` to the Windows `PATH`. Open a new terminal, confirm `php --version` works, then run `.\bin\qi.cmd doctor` again.

#### Docker command fails

Check that Docker Desktop is running and that the docker command works in this terminal.

On Windows, also confirm Docker Desktop is using Linux containers. `php bin/qi doctor` reports `Linux containers: OK` when configured correctly.

#### Composer command fails

QuickInstall uses composer from PATH first, then `composer.phar` from the project root. Restore `composer.phar` or install Composer if both are missing.

#### View container logs

If a board starts but the browser shows an error, or `board:start` waits longer than expected, inspect the Docker logs. The `web` logs usually show phpBB, PHP, or web server failures. The `db` logs show database startup and connection problems.

```bash
docker compose -f .qi/runtime/demo/compose.yml logs web
docker compose -f .qi/runtime/demo/compose.yml logs db
```

#### Reset a board completely

Use this when a board's files, database, or generated Docker runtime are no longer worth repairing. Destroying a board removes its generated state, so create it again afterward.

```bash
php bin/qi board:destroy demo
php bin/qi board:create demo --phpbb 3.3 --db mariadb --port 8081 --populate none
```

#### Dashboard UI will not start

Check the tracked status:

```bash
php bin/qi ui:status
```

If the status is stale, clear the old state and start again:

```bash
php bin/qi ui:stop
php bin/qi ui:start
```

If the selected port is already in use, choose a different local port:

```bash
php bin/qi ui:start --port 8088
```

The Dashboard UI server output log is written to `.qi/runtime/ui.log`. On Windows, PHP server errors are written to `.qi/runtime/ui.log.err`.
