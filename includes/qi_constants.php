<?php
/**
*
* @package quickinstall
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

define('QI_VERSION', '1.2.0-DEV');

// Chunk sizes
define('CHUNK_POST', 1000);
define('CHUNK_TOPIC', 2000);
define('CHUNK_USER', 5000);

// Cookies set by QI
define('QI_PROFILE_COOKIE', 'qi_profile');	// Cookie with the latest used profile name as payload.
