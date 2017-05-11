# phpBB3 QuickInstall

phpBB3 QuickInstall (QI) is a tool used to manage multiple phpBB3 installations. You can quickly install phpBB 3.2.x, 3.1.x and 3.0.x boards with one single mouse click.

QuickInstall is not intended for use on a live server. It is aimed at users developing extensions and modifications for phpBB. QuickInstall can set up new phpBB boards to develop and test extensions or MODs in without having to worry about conflicts. If you insist on using QuickInstall on a public server, make sure to protect the directory where it is. No support is given for QI other than local use.   

## Installation:
Copy the quickinstall folder to your local web space. Make sure `boards/`, `cache/` and `settings/` are writable by your web server. You can move `boards` and `cache` to other locations after the initial configuration if you wish.

## Usage:
Download the latest phpBB 3.2.x, 3.1.x or 3.0.x from [phpBB.com](https://www.phpbb.com/downloads/), extract it and copy the phpBB3 folder to `sources/`. Choose the version you do the most work with, for your own convenience.

In `sources/phpBB3_alt` you can store additional versions of phpBB, or boards with other style or language files. You can name them whatever you want to make it easier for you to recognize them when you create boards. 

Example: `sources/phpBB3_alt/phpBB-3.0.12`, `sources/phpBB3_alt/phpBB-sv`, and so on.

> If you want to use phpBB 3.0.x with AutoMOD you will also need to download [AutoMOD](https://www.phpbb.com/customise/db/official_tool/automod/). Unzip AutoMOD and copy the contents of the root or upload folder, depending on the AutoMOD version, to `sources/automod/`.

Then point your browser to the QI folder (e.g., https://localhost/quickinstall). You should be taken directly to the QI settings page. Some default values will already be filled in but you will need to add your database connection settings. Click "Save" once you have configured all the settings to your liking and you are good to go. If you don't set a profile name, the name "Default" will be used.

If you want additional files/folders to be copied to your boards when they are created, you can put them in the `sources/extra` folder. By using the same folder structure in `sources/extra` as phpBB you can get the files where you want.

## Upgrading from QI 1.1.8:
Download the package and unzip it into your QI directory. Point your browser to Manage profiles, make sure the new settings are in order, submit and you are done.

## Upgrading from QI older than 1.1.8:
Download the package and unzip it into your QI directory. Make sure the new `settings` folder is writable by PHP and point your browser to your QI folder. QI will convert your old config to a profile named "default", you can of course choose some other name for it.

## Support and Bugs:
You can receive support at: https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support

Please report all bugs you find, even reports for small bugs are welcome to make QI even better than it is now. The right place for bug reports is: https://github.com/phpbb/quickinstall/issues

## A Note About Passwords:
QI stores all passwords in a plain text file. They are hidden in the user interface, but can be read by anyone with access to the QI folder.

## License:
phpBB3 QuickInstall is distributed under the terms of the GNU General Public License 2 (GPL). A copy has been included in the package ([license.txt](license.txt)).
