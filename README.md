# <img class="phpbb-logo-rm" height="48" width="146" src="style/assets/img/logo_medium_cosmos.svg" alt="phpBB"> QuickInstall

QuickInstall is a tool we built to support the community of phpBB extension developers (and previously MOD authors). It simplifies and accelerates the process of creating and configuring local phpBB3 forum installations. These boards can then be used to safely install, develop and test extensions in isolation without having to worry about external conflicts.

> ##### ⚠️ QuickInstall is not intended for use on a live production website.
> QuickInstall stores all board and database passwords in a plain text file. They are hidden in the user interface, but can be read by anyone with access to the QuickInstall directory. Therefore, if you do use QuickInstall on a public server, you do so at your own risk and must protect access to the directory where it resides from unauthorised users. No support is provided for QuickInstall other than local use.

## 📦 Installation
1. Get the latest version of [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/).

2. Extract it and copy the `quickinstall` folder to your local web server.

3. [Download a copy of phpBB3](https://www.phpbb.com/downloads/). Extract it and copy the `phpBB3` folder to `quickinstall/sources/`.

4. Point your web browser to the QuickInstall directory (`http://localhost/quickinstall` for instance) and follow the setup instructions.

> **Alternate phpBB Profiles:**<br>
> You can store additional versions of phpBB and boards with alternate styles or language packs in `sources/phpBB3_alt/` . You can name these alternate phpBB folders whatever you want, e.g.:  `sources/phpBB3_alt/phpBB-3.0.12`, `sources/phpBB3_alt/phpBB-sv`, etc. They will then be available as alternative phpBB3 boards you can choose to install or save as Profiles.

> **Adding phpBB Extras**:<br>
> If you want additional files/folders, such as extensions, to be copied to your boards when they are created, you can put them in the `sources/extra/` directory. By using the same directory structure in `sources/extra/` as phpBB, the files/folders should be mapped to the correct locations in your boards. For example: `sources/extra/ext/phpbb/pages`.

## 🛠 Upgrading
1. Get the latest version of [QuickInstall](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/) and extract it. 

2. Copy everything into your existing QuickInstall directory **except for the 📁`boards/`, 📁`sources/` and 📁`settings/` directories**. 

> If you are upgrading from QuickInstall 1.1.8 (or older) you MUST review and re-save your old Profile settings.

## 💻 Requirements

##### Browsers
QuickInstall is designed to run on all modern browsers. Please don't use old stuff anymore...seriously.

|  |  |  |  |  |  |
|-|-|-|-|-|-|
| Desktop: | ![Chrome](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/chrome/chrome_32x32.png) 60+ | ![Firefox](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/firefox/firefox_32x32.png) 60+ | ![Safari](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/safari/safari_32x32.png) 12+ | ![Edge](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/edge/edge_32x32.png) 80+ | ![Opera](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/opera/opera_32x32.png) 40+ |
| Mobile: | ![iOS](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/safari-ios/safari-ios_32x32.png) 12+ | ![Android](https://cdnjs.cloudflare.com/ajax/libs/browser-logos/69.0.4/android-webview/android-webview_32x32.png) 6+ |  |  |  |

##### phpBB Requirements
phpBB boards require a web server running PHP and one of the following database management systems.

| phpBB          | PHP           | MySQL  | MariaDB | PostgreSQL | SQLite         | MS SQL       |
| -------------- | ------------- |------- |-------- |----------- |--------------- |------------- |
| 3.0.x          | 5.4.7 - 5.6.x | 3.23+  | -       | 7.x        | SQLite 2       | Server 2000  |
| 3.1.x          | 5.4.7 - 5.6.x | 3.23+  | 5.1+    | 8.3+       | SQLite 2 or 3  | Server 2000+ |
| 3.2.0 - 3.2.1  | 5.4.7 - 7.1.x | 3.23+  | 5.1+    | 8.3+       | SQLite 3.6.15+ | Server 2000+ |
| 3.2.2 - 3.2.x  | 5.4.7 - 7.2.x | 3.23+  | 5.1+    | 8.3+       | SQLite 3.6.15+ | Server 2000+ |
| 3.3.x          | 7.1.3 - 8.x   | 4.1.3+ | 5.1+    | 8.3+       | SQLite 3.6.15+ | Server 2000+ |
| 4.0.x (alpha)  | 7.3.0 - 8.x   | 4.1.3+ | 5.1+    | 8.3+       | SQLite 3.6.15+ | Server 2000+ |

## 🐞 Support
You can receive support at the [phpBB3 QuickInstall Discussion/Support](https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support) forum.

Please report all bugs to our [Issues Tracker](https://github.com/phpbb/quickinstall/issues). Even reports for small bugs are welcome to help make QuickInstall even better than it is now.

## 👋 Contributing
Feel free to contribute to this project. Please read our [Contributing Guidelines](https://github.com/phpbb/quickinstall/blob/master/.github/CONTRIBUTING.md) before submitting Pull Requests with any bug fixes or feature enhancements to this repository.

## 💖 Credits
The project is maintained by the phpBB Extensions Team.
- Credits go to the phpBB team, especially the development team which created such a wonderful piece of software.
- Originally created by Igor “igorw” Wiedler in the summer of 2007.
- Maintained by Jari “tumba25” Kanerva from March 2010 to March 2015.
- Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD.
- Thanks to the beta testers!
- Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!

## 📜 License
phpBB QuickInstall is distributed under the terms of the [GNU General Public License 2 (GPL)](license.txt).
