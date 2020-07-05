# <img height="48" width="146" src="style/assets/img/logo_medium_cosmos.svg" alt="phpBB">  QuickInstall

QuickInstall is a developer tool used to create multiple phpBB3 installations. You can quickly install phpBB 3.x.x boards with a single mouse click.

QuickInstall is aimed at users developing extensions and modifications for phpBB. The days of phpBB 2.x and 3.0 were all about modding (creating modifications). Authors could not effectively develop and test all their MODs in a single phpBB installation. QuickInstall was born to speed up and simplify the process of creating separate fresh installations for each of their MODs. Now, in the era of extensions, QuickInstall is still just as useful for rapidly generating fresh installations to safely install, develop and test extensions in without having to worry about conflicts. 

QuickInstall is not intended for use on a live server. If you do use QuickInstall on a public server, make sure to protect the folder where it is. No support is given for QuickInstall other than local use.

## Installation & Setup
1. Copy the QuickInstall folder to your local web server. Make sure `boards/`, `cache/` and `settings/` are writable by your web server. You can move `boards` and `cache` to other locations after the initial setup if you wish.

2. [Download a copy of phpBB 3.x.x](https://www.phpbb.com/downloads/), extract it and copy the phpBB3 folder into `sources/`. Choose the version you do the most work with, for your own convenience.

3. Point your browser to the QuickInstall folder (e.g., `https://localhost/quickinstall`). You should be taken directly to the "Install QuickInstall" page. Some default values will already be filled in but you should add your database connection settings. Click "Save" once you have configured all the settings to your liking and you are good to go. If you don't set a profile name, the name "default" will be used.

> In `sources/phpBB3_alt/` you can store additional versions of phpBB, or boards with other styles or language files. You can name them whatever you want to make it easier to recognize them when you create boards. e.g.,  `sources/phpBB3_alt/phpBB-3.0.12`, `sources/phpBB3_alt/phpBB-sv`, etc.

> If you want additional files/folders to be copied to your boards when they are created (i.e., extensions), you can put them in the `sources/extra/` folder. By using the same folder structure in `sources/extra/` as phpBB, the files/folders should be mapped to the correct locations in your boards.

> If you want to use phpBB 3.0.x with AutoMOD you will also need to download [AutoMOD](https://www.phpbb.com/customise/db/official_tool/automod/). Unzip AutoMOD and copy the contents of the `root` or `upload` folder, depending on the AutoMOD version, to `sources/automod/`.

#### A Note About Passwords
QuickInstall stores all passwords in a plain text file. They are hidden in the user interface, but can be read by anyone with access to the QuickInstall folder.

## Upgrading
* Download the latest QuickInstall and unzip it. Copy everything into your QuickInstall folder **except for the `boards/`, `sources/` and `settings/` folders**.

#### Upgrading from QuickInstall 1.1.8 or earlier?
* Download the latest QuickInstall and unzip it. Copy everything into your QuickInstall folder **except for the `boards/`, `sources/` and `settings/` folders**.

* If upgrading from QuickInstall 1.1.8, point your browser to the Profiles page, make sure the settings are in order and click "Save".

* If upgrading from QuickInstall earlier than 1.1.8, make sure the new `settings/` folder is writable by PHP and point your browser to your QuickInstall folder. QuickInstall will convert your old `qi_config.cfg` to a settings profile named "default". Make sure the settings are in order and click "Save".

## Contributing
You can receive support at [phpBB3 QuickInstall Discussion/Support](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support).

Please report all bugs to our [Issues Tracker](https://github.com/phpbb/quickinstall/issues). Even reports for small bugs are welcome to help make QuickInstall even better than it is now.

Feel free to contribute to this project. Submit Pull Requests with any bug fixes or feature enhancements. 

> QuickInstall also uses Twig for its template engine. Twig is not commited to the repository, so before you can use QI if you downloaded it from this repository, you must install Twig:
> - Have [composer](https://getcomposer.org/download/) installed.
> - From QI's root directory, run `$ php composer.phar install`
> - If you did things correctly, the vendor directory will be created in QI's root with Twig inside it.
>
> QuickInstall uses the Bootstrap framework which is compiled via NPM and located in the `develop` folder. To update or customise Quickinstall's Bootstrap files you must:
> - Have [Node JS](https://nodejs.org/) installed.
> - Navigate to the develop folder, `$ cd develop`.
> - Run `$ npm update bootstrap` to update to the latest Bootstrap release.
> - Edit the `qi_bootstrap.scss` file to customise Bootstrap variables.
> - Run `$ npm run all` to compile and deploy Bootstrap to QuickInstall.

## Credits
The project is maintained by the phpBB Extensions Team.
- Credits go to the phpBB team, especially the development team which 
created such a wonderful piece of software.
- Originally created by Igor “igorw” Wiedler in the summer of 2007.
- Mantained by Jari “tumba25” Kanerva from March 2010 to March 2015.
- Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD.
- Thanks to the beta testers!
- Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!

## License
phpBB QuickInstall is distributed under the terms of the [GNU General Public License 2 (GPL)](license.txt).
