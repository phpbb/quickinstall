# Contributing to QuickInstall

:+1::tada: Thanks for taking the time to contribute! :tada::+1:

## Contents:
1. [Fork and Clone](#fork_and_knife-fork-and-clone)
2. [Gear up for development](#gear-gear-up-for-development)
3. [Make something great](#computer-make-something-great)
4. [Submit a Pull Request](#tophat-submit-a-pull-request)
5. [Collaborate](#thumbsup-collaborate)

## :fork_and_knife: Fork and Clone

The first steps include creating your own repository of QuickInstall and getting a copy of it onto your computer:

1. On GitHub, Fork your own copy of `phpbb/quickinstall` to your account.

2. Create a local clone of your fork:
```bash
git clone https://github.com/YOUR-USERNAME/quickinstall.git
```

## :gear: Gear up for development

Assuming you have a local web development server up and running and know how to use a command line terminal application:

From QI's root directory, run the following command to install QI's dependencies:
```bash
php composer.phar install
```

Open QuickInstall in a browser on your local web server (e.g., `https://localhost/quickinstall`).

> Optional: QuickInstall uses the Bootstrap framework, which is compiled via NPM. To update or customise QuickInstall's Bootstrap files, you must:
> - Have [Node.js](https://nodejs.org/) installed.
> - Run `npm install` to install its node dependencies.
> - Edit the `scss/qi_bootstrap.scss` file to customise Bootstrap variables.
> - Run `npm run all` to compile and deploy new Bootstrap CSS/JS files to QuickInstall.

### CLI tests

The new CLI test suite uses an isolated Composer config, so it can require PHPUnit without changing QuickInstall's legacy runtime dependency constraints.

To initially set up the test environment on your local web server:

```bash
cd tests
COMPOSER=composer.cli-tests.json composer install
cd ..
```

Run the test suite:

```bash
tests/vendor/bin/phpunit -c phpunit.xml.dist
```

The CLI test dependencies install into `tests/vendor/`, leaving the main application `vendor/` directory unchanged.

On Windows PowerShell, set the Composer config and run the same suite with:

```powershell
# Initialise the test environment
Set-Location tests
$env:COMPOSER = 'composer.cli-tests.json'
composer install
Set-Location ..

# Run the test suite
php tests/vendor/bin/phpunit -c phpunit.xml.dist
```

## :computer: Make something great

Create a new branch in your repository before doing any work. It should be based off the `develop` branch:
```bash
git checkout -b myNewbranch origin/develop
```

Do work on your branch, commit your changes and push it to your repository:
```bash
git commit -a -m "My new feature or bug fixes"

git push origin myNewbranch
```

## :tophat: Submit a Pull Request

1. Go to your repository on GitHub.com.

2. Click the Pull Request button.

## :thumbsup: Collaborate

Be prepared for:
- Constructive criticism of your code changes.
- phpBB team members or the community at large may request changes to your code.
- That feeling when your Pull Request is accepted and merged. :sunglasses:
