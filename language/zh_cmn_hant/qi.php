<?php
/**
*
* qi [正體中文]
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c)  2010 phpBB-TW 心靈捕手 (wang5555) http://phpbb-tw.net/phpbb/
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

/**
* DO NOT CHANGE
*/
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, array(
	'ABOUT_QUICKINSTALL' => '關於 phpBB3 QuickInstall',
	'ADMIN_EMAIL' => '管理員之 e-mail',
	'ADMIN_EMAIL_EXPLAIN' => '這是您論壇管理員之 e-mail。',
	'ADMIN_NAME' => '管理員之會員名稱',
	'ADMIN_NAME_EXPLAIN' => '這是您論壇預設的管理員之會員名稱。當論壇建立後，可以改變它。',
	'ADMIN_PASS' => '管理員之密碼',
	'ADMIN_PASS_EXPLAIN' => '這是您論壇預設的管理員之密碼。當論壇建立後，可以改變它。',
	'ALT_ENV' => '輪流的環境',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => '預設下，設定安裝 AutoMOD 為是。當論壇建立後，可以改變它。',
	'AUTOMOD_INSTALL' => '安裝 AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">返回主頁</a>',
	'BACK_TO_MANAGE' => '<a href="%s">返回管理頁</a>',
	'BOARD_CREATED' => '論壇已經成功地建立！',
	'BOARD_DBNAME' => '論壇資料庫以及目錄名稱',
	'BOARD_DESC' => '論壇描述',
	'BOARD_EMAIL' => '論壇 e-mail',
	'BOARD_EMAIL_EXPLAIN' => '您論壇的寄件者之 e-mail。',
	'BOARD_NAME' => '論壇名稱',
	'BOARDS_DELETED' => '論壇已經成功地刪除！',
	'BOARDS_DELETED_TITLE' => '論壇已刪除',
	'BOARDS_DIR' => '論壇目錄',
	'BOARDS_DIR_EXPLAIN' => '這目錄是您的論壇將被建立的位置。PHP 必須有寫入這個目錄的權限。',
	'BOARDS_LIST' => '目錄列表',
	'BOARDS_NOT_WRITABLE' => '這論壇目錄是不可寫入的。',

	'CACHE_NOT_WRITABLE' => '這 cache 目錄是不可寫入的。',
	'CHANGELOG' => '改變記錄',
	'CHECK_ALL' => '檢查所有',
	'CONFIG_EMPTY' => '這 config 配置是空的。這可能是值得的錯誤報告。',
	'CONFIG_NOT_WRITABLE' => '這 qi_config.php 檔案是不可寫入的。',
	'COOKIE_DOMAIN' => 'Cookie 網域',
	'COOKIE_DOMAIN_EXPLAIN' => '一般設定為 localhost。',
	'COOKIE_SECURE' => 'Cookie 安全',
	'COOKIE_SECURE_EXPLAIN' => '如果您的伺服器是經由 SSL 運行，那麼設定為啟用。否則，設定為停用。如果伺服器不是經由 SSL 運行，而設定為啟用的話，將會導致伺服器在重新導向時出現錯誤。',
	'CREATE_ADMIN' => '建立管理員',
	'CREATE_ADMIN_EXPLAIN' => '如果您要建立一個管理員，那麼設定為是。他不是論壇創始者，而只是一個測試者_1。',
	'CREATE_MOD' => '建立版主',
	'CREATE_MOD_EXPLAIN' => '如果您要建立一個全域版主，那麼設定為是。如果有管理員可選，那麼他將是測試者_1，或測試者_2。',

	'DB_EXISTS' => '資料庫 %s 已經存在。',
	'DB_PREFIX' => '資料庫資料表字首',
	'DB_PREFIX_EXPLAIN' => '這是加在所有資料表前面的名稱，以避免被 QuickInstall 覆寫已存在的資料表。',
	'DBHOST' => '資料庫伺服器',
	'DBHOST_EXPLAIN' => '通常設定為 localhost。',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => '您的資料庫系統。如果您不確定，那麼設定它為 MySQL。',
	'DBPASSWD' => '資料庫密碼',
	'DBPASSWD_EXPLAIN' => '您的資料庫使用者之密碼',
	'DBPORT' => '資料庫連接埠',
	'DBPORT_EXPLAIN' => '大都保留空白。',
	'DBUSER' => '資料庫使用者',
	'DBUSER_EXPLAIN' => '您的資料庫使用者。這裡必須指定有權限建立新資料庫的使用者。',
	'DEFAULT' => '預設的',
	'DEFAULT_ENV' => '預設的環境 (最新的 phpBB)',
	'DEFAULT_LANG' => '預設的語言',
	'DEFAULT_LANG_EXPLAIN' => '將用來建立論壇的語言。',
	'DELETE' => '刪除',
	'DELETE_FILES_IF_EXIST' => '假如檔案已經存在，那麼刪除之。',
	'DIR_EXISTS' => '這目錄 %s 已經存在。',
	'DISABLED' => '停用的',
	'DROP_DB_IF_EXISTS' => '如果資料庫已經存在，那麼刪除之。',

	'EMAIL_DOMAIN' => 'E-mail 網域',
	'EMAIL_DOMAIN_EXPLAIN' => '這 e-mail 網域是提供測試者使用的。他們 e-mail 將是 tester_x@&lt;domain.com&gt;。',
	'EMAIL_ENABLE' => '啟用 e-mail',
	'EMAIL_ENABLE_EXPLAIN' => '啟用論壇發送 e-mails。對於本機測試論壇而言，這個通常是關閉的，除非您要測試 e-mails。',
	'ENABLED' => '啟用的',

	'GENERAL_ERROR' => '一般錯誤',

	'IN_SETTINGS' => '管理您的 QuickInstall 設定。',
	'INCLUDE_MODS' => '包含外掛',
	'INCLUDE_MODS_EXPLAIN' => '在列表中，從 sources/mods/ 目錄選擇資料夾，那些檔案將被複製到您新的論壇根目錄，同時也會覆寫舊的檔案 (因此您可以有已預先安裝外掛的論壇)。如果您選擇「None」，那麼它將不會被使用 (因為取消項目是通苦的)。',
	'INSTALL_BOARD' => '安裝論壇',                                                                           
	'INSTALL_QI' => '安裝 QuickInstall',
	'IS_NOT_VALID' => '無效的。',

	'LICENSE' => '許可？',
	'LICENSE_EXPLAIN' => '這 script 是基於 <a href="license.txt">GNU General Public License version 2</a> 而發布。這主要是因為它使用了 phpBB 的大部分代碼，而 phpBB 也是根據這許可而發布，以及需要任何的修改也是如此。但也因為它是自由軟體的偉大許可 :)。',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>已安裝 phpBB Quickinstall %s 版</strong>',
	'LOREM_IPSUM' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE' => '使所有檔案為可寫入的',
	'MAKE_WRITABLE_EXPLAIN' => '預設下，設定檔案、config.php、以及所有目錄都是可寫入的。當論壇建立後，可以改變它。',
	'MANAGE_BOARDS' => '管理論壇',
	'MAX' => '最大值',
	'MIGHT_TAKE_LONG' => '<strong>請注意：</strong>論壇的建立可能需要一段時間、幾分鐘或更久，因此請<strong>不要</strong>送出表單兩次。',
	'MIN' => '最小值',

	'NEED_EMAIL_DOMAIN' => '為了要建立測試使用者，e-mail 網域是必須的。',
	'NEED_WRITABLE' => 'QuickInstall 需要論壇以及 cache 資料夾在任何時候總是可以寫入的。<br />這 qi_config.php 只需要在安裝 QuickInstall 時可以寫入。',
	'NO' => '否',
	'NO_ALT_ENV' => '所指定之替代的環境不存在。',
	'NO_AUTOMOD' => '在 sources 目錄裡沒有找到 AutoMOD。您需要下載 AutoMOD，以及複製其 root 目錄到 sources/automod，然後重新命名 root 為 automod。',
	'NO_BOARDS' => '您沒有論壇。',
	'NO_DB' => '沒有選擇的資料庫。',
	'NO_IMPACT_WIN' => '這個設定在 Win7 系統以下舊版，沒有任何影響。',
	'NO_MODULE' => '這模組 %s 無法無法加載。.',
	'NO_PASSWORD' => '沒有密碼',
	'NO_DBPASSWD_ERR' => '您有設定資料庫密碼，然而檢查不到密碼。您不能 <strong>有</strong> 而 <strong>檢查不到</strong> 密碼。',
	'NONE' => '一點也不',
	'NUM_CATS' => '分區的數目',
	'NUM_CATS_EXPLAIN' => '所要建立的論壇分區之數量。',
	'NUM_FORUMS' => '版面的數目',
	'NUM_FORUMS_EXPLAIN' => '所要建立的論壇版面之數量，他們將均勻分佈到已建立的分區。',
	'NUM_NEW_GROUP' => '新註冊',
	'NUM_NEW_GROUP_EXPLAIN' => '要放進新註冊群組的會員數量。<br />如果這個數量比會員數目還大，那麼所有的新註冊會員都將放進新註冊群組中。',
	'NUM_REPLIES' => '回覆數目',
	'NUM_REPLIES_EXPLAIN' => '回覆的數量。每個主題將收到一個介於最大與最小值之隨機的數字。',
	'NUM_TOPICS' => '主題數目',
	'NUM_TOPICS_EXPLAIN' => '在每個版面中所要建立的主題之數量。每個版面將得到一個介於最大與最小值之隨機的數字。',
	'NUM_USERS' => '會員數目',
	'NUM_USERS_EXPLAIN' => '用來填充您新論壇的會員之數量。<br />他們將設定會員名稱為 Tester_x (x 值是 1 到您所填入的數字)，而密碼全都是「123456」。',

	'ONLY_LOCAL' => '請注意：QuickInstall 的目的只是用來安裝在個人主機使用，並非要對外公開。<br />它不該用在經由網路而可輕易進入的網站伺服器上。',
	'OPTIONS' => '選項',
	'OPTIONS_ADVANCED' => '進階的選項',                                   

	'POPULATE' => '填充論壇',
	'POPULATE_OPTIONS' => '填充選項',
	'POPULATE_MAIN_EXPLAIN' => '會員名稱：tester x，密碼：123456',
	'POPULATE_EXPLAIN' => '由您在下面所指定的會員、版面、文章以及主題之數量來填充論壇。請注意：如果您想要較多的數量，那麼就得花更長的時間來建立論壇。<br />當您論壇建立後，可以改變所有的這些設定。',

	'QI_ABOUT' => '關於',
	'QI_ABOUT_ABOUT' => '大哥愛您以及希望您能夠快樂。',
	'QI_DST' => '日光節約時間',
	'QI_DST_EXPLAIN' => '您想開啟或關閉日光節約時間？',
	'QI_LANG' => 'QuickInstall 語言',
	'QI_LANG_EXPLAIN' => 'QuickInstall 將使用的語言。在 language/ 目錄底下需要有相同名稱的資料夾。如果那個語言存在於 sources/phpBB3/language/ 目錄底下，那麼它也是您論壇的預設語言。',
	'QI_MAIN' => '主頁',
	'QI_MAIN_ABOUT' => '在這裡安裝一個新的論壇。<br /><br />您只必須填入「論壇資料庫名稱」，其他都會由 <em>includes/qi_config.php</em> 預設值填入。<br /><br />點選「進階的選項」以獲得更多的設定。',
	'QI_MANAGE' => '管理論壇',                                                        
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => '時區',
	'QI_TZ_EXPLAIN' => '您的時區。它將是論壇的預設時區，例如：-1、0、1 等。',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => '重新導向',
	'REDIRECT_EXPLAIN' => '預設下，設定重新導向新論壇為是。當論壇建立後，可以改變它。',
	'REDIRECT_BOARD' => '重新導向新論壇',
	'REQUIRED' => '需要',
	'RESET' => '重新設定',

	'SELECT' => '選擇',
	'SETTINGS' => '設定',
	'SETTINGS_FAILURE' => '有錯誤，請看一下下面的訊息。',
	'SETTINGS_SUCCESS' => '您的設定已經成功地儲存。',
	'SERVER_NAME' => '伺服器名稱',
	'SERVER_NAME_EXPLAIN' => '通常這裡應該設定為 localhost，因為 QuickInstall <strong>不是</strong>要用於公開的伺服器上。',
	'SERVER_PORT' => '伺服器連接埠',
	'SERVER_PORT_EXPLAIN' => '通常設定為 80。',
	'SITE_DESC' => '網站描述',
	'SITE_DESC_EXPLAIN' => '關於您論壇的預設描述。當論壇建立後，可以改變它。',
	'SITE_NAME' => '網站名稱',
	'SITE_NAME_EXPLAIN' => '用作為您論壇的預設網站名稱。當論壇建立後，可以改變它。',
	'SMTP_AUTH' => 'SMTP 的認證方式',
	'SMTP_AUTH_EXPLAIN' => '只用在如果需要設定會員名稱或密碼時。',
	'SMTP_DELIVERY' => '使用 SMTP 伺服器傳送 e-mail',
	'SMTP_DELIVERY_EXPLAIN' => '選擇「是」，如果您想要或必須經由 SMTP 伺服器而不是本地的信件函數發送 e-mail。',
	'SMTP_HOST' => 'SMTP 伺服器位址',
	'SMTP_HOST_EXPLAIN' => '您想要使用的 SMTP 伺服器位址。',
	'SMTP_PASS' => 'SMTP 密碼',
	'SMTP_PASS_EXPLAIN' => '只有當您的 SMTP 伺服器需要時，才只要輸入一個密碼。',
	'SMTP_PORT' => 'SMTP 伺服器連接埠',
	'SMTP_PORT_EXPLAIN' => '只有在您知道您的 SMTP 伺服器運行在一個不同的連接埠時才需要變更。',
	'SMTP_USER' => 'SMTP 會員名稱',
	'SMTP_USER_EXPLAIN' => '只有當您的 SMTP 伺服器需要它時才要輸入。',
	'STAR_MANDATORY' => '* = 必填的',
	'SUBMIT' => '送出',
	'SUBSILVER' => '安裝 Subsilver2',
	'SUBSILVER_EXPLAIN' => '如果您想要安裝與使用 Subsilver2 風格做為論壇的預設風格，那麼選擇它。當論壇建立後，可以改變它。',
	'SUCCESS' => '成功',

	'TABLE_PREFIX' => '資料表字首',
	'TABLE_PREFIX_EXPLAIN' => '用作為您論壇的資料表字首。當論壇建立後，可以在進階的選項改變它。',
	'TEST_CAT_NAME' => '測試分區 %d',
	'TEST_FORUM_NAME' => '測試版面 %d',
	'TEST_POST_START' => '測試文章 %d', // 這將出現在每篇文章的第一行，然後以 lorem ipsum 填滿。
	'TEST_TOPIC_TITLE' => '測試主題 %d',

	'UNCHECK_ALL' => '取消所有選取',
	'UP_TO_DATE' => '大哥說您是最新的。',
	'UP_TO_DATE_NOT' => '大哥說您不是最新的。',
	'UPDATE_CHECK_FAILED' => '大哥版本檢查已失敗。',
	'UPDATE_TO' => '<a href="%1$s">更新到 %2$s 版。</a>',

	'YES' => '是',

	'VERSION_CHECK' => '大哥版本檢查',
	'VISIT_BOARD' => '<a href="%s">訪問論壇</a>',

	'WHAT' => '什麼？',
	'WHAT_EXPLAIN' => '很明顯地，phpBB3 QuickInstall 是一個快速安裝 phpBB 的 script。 ;-)',
	'WHO_ELSE' => '還有誰？',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'歸功於 phpBB 團隊，特別是發展團隊建立了一個奇妙的軟體。',
		'感謝 phpBB.com 外掛團隊 (特別是 Josh、aka「A_Jelly_Doughnut」) 將 AutoMOD 也已經包含在這個套件中。',
		'感謝 Mike TUMS 的 logo！',
		'感謝測試者！',
		'感謝 phpBB 社群，包括 phpBB.com、startrekguide.com 以及 phpBBModders.net！',
	)) . '</li></ul>',
	'WHO_WHEN' => '誰？何時？',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall 最初是由 Wiedler 的 Igor「eviL&lt;3」於 2007 年的夏天所建立。並且於 2008 年三月由他自己改寫了部份。<br />自從 2010 年三月起，這個計畫由 Kanerva 的 Jari「Tumba25」繼續維護。',
	'WHY' => '為什麼？',
	'WHY_EXPLAIN' => '就好像 phpBB2，如果您做了許多修改 (建立外掛)，那麼您無法將所有的外掛放進單一的 phpBB 安裝。因此它是最適用於各別地安裝。現在的問題是：每次複製檔案以及安裝過程是痛苦的。為了要加快完成，於是 quickinstall 誕生了。',
));

?>