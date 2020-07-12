# Contributing to QuickInstall

:+1::tada: Thanks for taking the time to contribute! :tada::+1:

## Contents:
1. [Fork and Clone](#fork_and_knife-fork-and-clone)
2. [Gear up for development](#gear-gear-up-for-development)
3. [Make something great](#computer-make-something-great)
4. [Submit a Pull Request](#tophat-submit-a-pull-request)
5. [Collaborate](#thumbsup-collaborate)

## :fork_and_knife: Fork and Clone

First steps include creating your own repository of QuickInstall, and getting a copy of it onto your computer:

1. On GitHub, Fork your own copy of `phpbb/quickinstall` to your account.

2. Create a local clone of your fork:
```
$ git clone git://github.com/<my_github_name>/quickinstall.git
```

## :gear: Gear up for development

Assuming you have a local web development server up and running and know how to use a command line terminal application:

1. From QI's root directory, run the following command to install QI's dependencies:
```
$ php composer.phar install
```

2. Open QuickInstall in a browser on your local web server (e.g., `https://localhost/quickinstall`).

> Optional: QuickInstall uses the Bootstrap framework which is compiled via NPM. To update or customise QuickInstall's Bootstrap files you must:
> - Have [Node JS](https://nodejs.org/) installed.
> - Navigate to the `quickinstall/develop` folder: `$ cd develop`.
> - Run `$ npm install` to install its node dependencies.
> - Edit the `qi_bootstrap.scss` file to customise Bootstrap variables.
> - Run `$ npm run all` to compile and deploy new Bootstrap CSS/JS files to QuickInstall.


## :computer: Make something great

1. Create a new branch in your repository before doing any work. It should be based off the `develop` branch:
```
$ git checkout -b myNewbranch origin/develop
```

2. Do work on your branch, commit your changes and push it to your repository:
```
$ git commit -a -m "My new feature or bug fixes"

$ git push origin myNewbranch
```


## :tophat: Submit a Pull Request

1. Got to your repository on GitHub.com.

2. Click the Pull Request button.

3. Make sure the **develop** branch is selected in the base branch dropdown menu.


## :thumbsup: Collaborate

Be prepared for:
- Constructive criticism of your code changes.
- phpBB team members or the community at large may request changes to your code (repeat [step 2 from here](#computer-make-something-great)).
- That feeling when your Pull Request is accepted and merged. :sunglasses:

