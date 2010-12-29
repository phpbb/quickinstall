phpBB3 QuickInstall ReadMe

About:
phpBB3 QuickInstall is tool used to manage many installations of phpBB3. It is
not meant to be used on a remote server, it should be used only locally. You
can install a phpBB3 board with one single mouse click. Now this is useful for
developing modifications, as you won't have any conflicts of different mods.

Installation:
Copy the quickinstall folder to your local web space. Make sure boards/ and
cache/ are writable by your web server.

Rename qi_config_sample.cfg as qi_config.cfg and make sure it is writable by
your web server.

Download the latest phpBB version from http://www.phpbb.com/downloads/, unzip it
and copy the phpBB3 directory to sources/

Download AutoMOD from http://www.phpbb.com/mods/automod/ or from the AutoMOD repo
https://github.com/phpbb/automod depending on what version you want. Unzip the
package and copy the contents of the root, or upload depending on AutoMOD version,
directory to sources/automod/.

Then point your browser to quickinstall/index.php. You should be taken to the
settings page. After that open index.php in your browser and it should work. If
it does not, seek support here: http://www.phpbb.com/community/viewforum.php?f=71

phpBB3 QuickInstall is distributed under the terms of the GNU General Public
License 2 (GPL). A copy has been included in the package (license.txt).

I will not be held responsible for any damage caused by this script. Use it at
your own risk.

Password notice:
The passwords that you enter in the "settings" page are stored in plain text,
these passwords however are hidden in the user interface, but can be revealed
by hovering over the password field.
