phpBB3 QuickInstall

About:
phpBB3 QuickInstall (QI) is tool used to manage many installations of phpBB3. It
is not intended for use on a live server. If you insist on using it on a public
server, make sure to protect the directory where it is. No support is given for
other than local use. You can install a phpBB 3.1.x or 3.0.x board with one
single mouse click. This is useful for developing modifications and extensions,
as you won't have any conflicts of different MODs or extensions. And might need
to install several boards before your testing is done.


License:
phpBB3 QuickInstall is distributed under the terms of the GNU General Public
License 2 (GPL). A copy has been included in the package (license.txt).


Installation:
Copy the quickinstall folder to your local web space. Make sure boards/, cache/
and settings/ are writable by your web server.

Download the latest phpBB 3.1.x or 3.0.x from https://www.phpbb.com/downloads/,
unzip it and copy the phpBB3 folder to sources/. Choose the one you do most work
with, for your own convenience.

In sources/phpBB3_alt you can put folders with other versions of phpBB, or
boards with other style or language files. You can name them what you want to
make it easier for you to recognize them when you create boards.
Eg: sources/phpBB3_alt/phpBB-3.0.12, sources/phpBB3_alt/phpBB-sv and so on.

Make sure the folders /boards, /cache and /settings are writable by your web
server. You can move "boards" and "cache" to other locations after the initial
configuration.

If you want to use phpBB 3.0.x with AutoMOD you also need to download that from
https://www.phpbb.com/customise/db/official_tool/automod/ if you want the stable
or from the AutoMOD repo https://github.com/phpbb/automod for the dev version.
Unzip AutoMOD and copy the contents of the root or upload folder, depending on
AutoMOD version, to sources/automod/.

Then point your browser to the QI folder (i.e. https://localhost/quickinstall).
You should be taken directly to the settings page with some default values
already filled in. At least you need to fill DB user and password. But it might
be a good time to go through all settings. Then click "Save" and you are good
to go. If you don't set a profile name, the name "default" will be used.


Upgrading from QI older than 1.1.8:
Just download the package and unzip it into your QI folder. Make sure the new
"settings" folder is writable by PHP and point your browser to your QI folder.
QI will convert your old config to a profile named "default", you can of course
choose some other name for it.


Upgrading from QI 1.1.8:
Just download the package and unzip it into your QI directory. Point your
browser to Manage profiles, make sure the new settings are in order, submit and
you are done.


Support and Bugs:
You can receive support at:
https://www.phpbb.com/customise/db/official_tool/phpbb3_quickinstall/support

Please report all bugs you find, even reports for small bugs are welcome to make
QI even better than it is now. The right place for bug reports are:
https://github.com/phpbb/quickinstall/issues


Passwords:
QI stores all passwords in a text file in plain text. They are hidden in the
user interface, but can be read by anyone with access to the QI folder.


Now go and write great extensions. :)
The extensions team
