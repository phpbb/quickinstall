# <img height="48" width="146" src="style/assets/img/logo_medium_cosmos.svg" alt="phpBB">  QuickInstall

QuickInstall is a developer tool used to create multiple phpBB3 installations. You can quickly install phpBB3 boards with a single mouse click.

QuickInstall was made to support the community of phpBB extension developers (and previously MOD authors). It speeds up and simplifies the process of creating separate *vanilla* phpBB environments to safely install, develop and test extensions in without having to worry about conflicts. 

> ⚠️ **QuickInstall is not intended for use on a live production web site.** QuickInstall stores all passwords in a plain text file. They are hidden in the user interface, but can be read by anyone with access to the QuickInstall folder. Therefor, if you do use QuickInstall on a public server, you do so at your own risk and must protect access to the folder where it resides from unauthorised users. No support is given for QuickInstall other than local use.

## Installation & Setup
1. Get the latest version of [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/).

2. Extract it and copy the QuickInstall folder to your local web server.

3. [Download a copy of phpBB3](https://www.phpbb.com/downloads/), extract it and move the phpBB3 folder into `sources/`. Choose the version you do the most work with, for your own convenience.

4. Point your browser to the QuickInstall folder (e.g., `https://localhost/quickinstall`). You should be taken directly to the "Install QuickInstall" page. Some default values will already be filled in but you should add your database connection settings. Click "Save" once you have configured all the settings to your liking and you are good to go. If you don't set a profile name, the name "default" will be used.

> In `sources/phpBB3_alt/` you can store additional versions of phpBB, or boards with alternate styles or language packs. You can name these alternate phpBB folders whatever you want, e.g.:  `sources/phpBB3_alt/phpBB-3.0.12`, `sources/phpBB3_alt/phpBB-sv`, etc. They will then be available as alternative phpBB3 boards you can choose to install or create install Profiles for.

> If you want additional files/folders to be copied to your boards when they are created (i.e., extensions), you can put them in the `sources/extra/` folder. By using the same folder structure in `sources/extra/` as phpBB, the files/folders should be mapped to the correct locations in your boards.

## Upgrading
* Download the latest QuickInstall and extract it. Copy everything into your QuickInstall folder **except for the `boards/`, `sources/` and `settings/` folders**.

##### Upgrading from QuickInstall 1.1.8 or earlier?
* If upgrading from QuickInstall 1.1.8 or earlier, follow the normal upgrade procedure mentioned above. Then point your browser to the Profiles page, make sure the settings are in order and click "Save".  QuickInstall will convert your old `qi_config.cfg` to a settings profile named "default".

## Support
You can receive support at the [phpBB3 QuickInstall Discussion/Support](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support) forum.

Please report all bugs to our [Issues Tracker](https://github.com/phpbb/quickinstall/issues). Even reports for small bugs are welcome to help make QuickInstall even better than it is now.

## Contributing
Feel free to contribute to this project. Submit Pull Requests with any bug fixes or feature enhancements.

1. On GitHub, Fork your own copy of `phpbb/quickinstall` to your account.
2. Create a local clone of your fork `$ git clone https://github.com/YOUR-USERNAME/quickinstall`.
3. From QI's root directory, run `$ php composer.phar install` to install its dependencies. 
4. Open QuickInstall in a browser on your local web server (e.g., `https://localhost/quickinstall`).
5. Do work on a new branch, push it to your repository, and submit a Pull Request.

> Optional: QuickInstall uses the Bootstrap framework which is compiled via NPM and located in the `develop` folder. To update or customise Quickinstall's Bootstrap files you must:
> - Have [Node JS](https://nodejs.org/) installed.
> - Navigate to the `develop` folder: `$ cd develop`.
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
