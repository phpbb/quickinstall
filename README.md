# <img height="48" width="146" src="style/assets/img/logo_medium_cosmos.svg" alt="phpBB">  QuickInstall

QuickInstall is a developer tool used to create multiple phpBB3 installations. You can quickly install phpBB3 boards with a single mouse click.

QuickInstall was made to support the community of phpBB extension developers (and previously MOD authors). It speeds up and simplifies the process of creating separate *vanilla* phpBB environments to safely install, develop and test extensions in without having to worry about conflicts. 

> ⚠️ **QuickInstall is not intended for use on a live production web site.** QuickInstall stores all passwords in a plain text file. They are hidden in the user interface, but can be read by anyone with access to the QuickInstall folder. Therefor, if you do use QuickInstall on a public server, you do so at your own risk and must protect access to the folder where it resides from unauthorised users. No support is given for QuickInstall other than local use.

## 📦 Installation & Setup
1. Get the latest version of [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/).

2. Extract it and copy the QuickInstall folder to your local web server.

3. [Download a copy of phpBB3](https://www.phpbb.com/downloads/), extract it and move the phpBB3 folder into `sources/`. Choose the version you do the most work with, for your own convenience.

4. Point your browser to the QuickInstall folder (e.g., `https://localhost/quickinstall`). You should be taken directly to the "Install QuickInstall" page. Some default values will already be filled in but you should add your database connection settings. Click "Save" once you have configured all the settings to your liking and you are good to go. If you don't set a profile name, the name "default" will be used.

> In `sources/phpBB3_alt/` you can store additional versions of phpBB, or boards with alternate styles or language packs. You can name these alternate phpBB folders whatever you want, e.g.:  `sources/phpBB3_alt/phpBB-3.0.12`, `sources/phpBB3_alt/phpBB-sv`, etc. They will then be available as alternative phpBB3 boards you can choose to install or create install Profiles for.

> If you want additional files/folders to be copied to your boards when they are created (i.e., extensions), you can put them in the `sources/extra/` folder. By using the same folder structure in `sources/extra/` as phpBB, the files/folders should be mapped to the correct locations in your boards.

## 🛠 Upgrading
1. Download the latest QuickInstall and extract it. 

2. Copy everything into your existing QuickInstall folder **except for the `boards/`, `sources/` and `settings/` folders**. 

*Note that when upgrading from a very old version of QuickInstall (1.1.8 or less) you will need to reconfigure your settings as a fresh install.*

## 💻 Requirements

##### Browsers
QuickInstall is designed to run on all modern browsers. Please don't use old stuff anymore...seriously.

|  |  |  |  |  |  |  |
|-|-|-|-|-|-|-|
| Desktop: | ![Chrome](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/chrome/chrome_32x32.png) 45+ | ![Firefox](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/firefox/firefox_32x32.png) 38+ | ![Safari](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/safari/safari_32x32.png) 9+ | ![Edge](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/edge/edge_32x32.png) 12+ | ![Explorer](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/archive/internet-explorer_9-11/internet-explorer_9-11_32x32.png) 10+ | ![Opera](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/opera/opera_32x32.png) 30+ |
| Mobile: | ![iOS](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/safari-ios/safari-ios_32x32.png) 9+ | ![Android](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/67.0.1/android-webview/android-webview_32x32.png) 4.4+ |  |  |  |  |
<br>

##### Servers
- Apache or Ngnix
- PHP 5.4.7 or above, with the JSON module

> Note that each version of phpBB has its own requirements and limitations:
>
> PHP requirements:
> - phpBB 3.0 and 3.1 will only work with PHP 5.4.7 through 5.6.40
> - phpBB 3.2.0-3.2.1 will only work with PHP 5.4.7 through 7.1.x
> - phpBB 3.2.2-3.2.x will only work with PHP 5.4.7 through 7.2.x
> - phpBB 3.3.x will only work with PHP 7.1.3 or above
> 
> Database minimum requirements:
> - phpBB 3.0 - MySQL 3.23+, MS SQL Server 2000, PostgreSQL 7.x, or SQLite 2
> - phpBB 3.1 - MySQL 3.23+ (MySQLi supported), MariaDB 5.1+, MS SQL Server 2000+, PostgreSQL 8.3+, SQLite 2 or 3
> - phpBB 3.2 - MySQL 3.23+ (MySQLi supported), MariaDB 5.1+, MS SQL Server 2000+, PostgreSQL 8.3+, SQLite 3.6.15+
> - phpBB 3.3 - MySQL 4.1.3+ (MySQLi required), MariaDB 5.1+, MS SQL Server 2000+, PostgreSQL 8.3+, SQLite 3.6.15+

## 🐞 Support
You can receive support at the [phpBB3 QuickInstall Discussion/Support](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support) forum.

Please report all bugs to our [Issues Tracker](https://github.com/phpbb/quickinstall/issues). Even reports for small bugs are welcome to help make QuickInstall even better than it is now.

## 👋 Contributing
Feel free to contribute to this project. Please read our [Contributing Guidelines](https://github.com/phpbb/quickinstall/blob/master/.github/CONTRIBUTING.md) before submitting Pull Requests with any bug fixes or feature enhancements to this repository.

## 💖 Credits
The project is maintained by the phpBB Extensions Team.
- Credits go to the phpBB team, especially the development team which 
created such a wonderful piece of software.
- Originally created by Igor “igorw” Wiedler in the summer of 2007.
- Mantained by Jari “tumba25” Kanerva from March 2010 to March 2015.
- Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD.
- Thanks to the beta testers!
- Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!

## 📜 License
phpBB QuickInstall is distributed under the terms of the [GNU General Public License 2 (GPL)](license.txt).
