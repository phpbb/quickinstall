<?php
/**
*
* qi [Turkish]
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
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
	'ABOUT_QUICKINSTALL' => 'phpBB3 QuickInstall Hakkında',
	'ADMIN_EMAIL' => 'Yönetim e-postası',
	'ADMIN_EMAIL_EXPLAIN' => 'Forumlarınız için kullanılacak yönetim e-posta adresi',
	'ADMIN_NAME' => 'Yönetici kullanıcı adı',
	'ADMIN_NAME_EXPLAIN' => 'Forumlarınızda kullanılacak varsayılan yönetici kullanıcı adı. Forumlar oluşturulduktan sonra bu değiştirilebilir.',
	'ADMIN_PASS' => 'Yönetici şifresi',
	'ADMIN_PASS_EXPLAIN' => 'Forumlarınızda kullanılacak varsayılan yönetici şifresi. Forumlar oluşturulduktan sonra bu değiştirilebilir.',
	'ALT_ENV' => 'Alternatif ortam',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Varsayılan olarak AutoMOD kurulumunun ayarını evet olarak ayarlayın. Forumlar oluşturulduktan sonra bu değiştirilebilir.',
	'AUTOMOD_INSTALL' => 'AutoMOD’u kur',

	'BACK_TO_MAIN' => '<a href="%s">Ana sayfa’ya geri dön</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Yönetim sayfasına geri dön</a>',
	'BOARD_CREATED' => 'Mesaj panosu başarıyla oluşturuldu!',
	'BOARD_DBNAME' => 'Mesaj panosu veritabanı ve dizin adı',
	'BOARD_DESC' => 'Mesaj panosu açıklaması',
	'BOARD_EMAIL' => 'Mesaj panosu e-posta adresi',
	'BOARD_EMAIL_EXPLAIN' => 'Oluşturulan forumlarınız için gönderici e-postası.',
	'BOARD_NAME' => 'Mesaj panosu adı',
	'BOARDS_DELETED' => 'Mesaj panoları başarıyla silindi.',
	'BOARDS_DELETED_TITLE' => 'Mesaj panoları silindi',
	'BOARDS_DIR' => 'Mesaj panoları dizini',
	'BOARDS_DIR_EXPLAIN' => 'Forumlarınızın oluşturulacağı dizin adı. PHP, bu dizin için yazma izninin olmasını gerekli tutar.',
	'BOARDS_LIST' => 'Mesaj panolarının listesi',
	'BOARDS_NOT_WRITABLE' => 'Mesaj panoları dizini yazılabilir değil.',

	'CACHE_NOT_WRITABLE' => 'Cache dizini yazılabilir değil.',
	'CHANGELOG' => 'Değişiklikler',
	'CHECK_ALL' => 'Tümünü kontrol et',
	'CONFIG_EMPTY' => 'Ayar dizisi boş oluşturuldu. Bu, muhtemelen bir hata raporu değerindedir.',
	'CONFIG_NOT_WRITABLE' => 'qi_config.php dosyası yazılabilir değil.',
	'COOKIE_DOMAIN' => 'Çerez alan adı adresi',
	'COOKIE_DOMAIN_EXPLAIN' => 'Bu genellikle localhost olmalıdır.',
	'COOKIE_SECURE' => 'Çerez güvenliği',
	'COOKIE_SECURE_EXPLAIN' => 'Eğer sunucunuz SSL yoluyla çalışıyorsa bunu açın aksi halde kapalı olarak bırakın. Sunucunuz SSL yoluyla çalışmadığında bunu açarsanız yönlendirmeler sırasında sunucu hataları oluşacaktır.',
	'CREATE_ADMIN' => 'Yönetici oluştur',
	'CREATE_ADMIN_EXPLAIN' => 'Eğer bir yönetici oluşturmak istiyorsanız evet olarak ayarlayın. Bu bir kurucu olmayacaktır, tester_1 adında bir kullanıcı olacaktır.',
	'CREATE_MOD' => 'Moderatör oluştur',
	'CREATE_MOD_EXPLAIN' => 'Eğer bir global moderatör oluşturmak istiyorsanız evet olarak ayarlayın. Bu, yönetici seçimine bağlı olarak tester_1 ya da tester_2 adında bir kullanıcı olacaktır.',

	'DB_EXISTS' => '%s adında veritabanı zaten mevcut.',
	'DB_PREFIX' => 'Veritabanı öneki',
	'DB_PREFIX_EXPLAIN' => 'Bu, QuickInstall tarafından kullanılmayan veritabanlarının üzerine yazmayı önlemek amacıyla tüm veritabanlarının önüne eklenen ektir.',
	'DBHOST' => 'Veritabanı sunucusu',
	'DBHOST_EXPLAIN' => 'Genellikle localhost’tur.',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => 'Veritabanı sisteminiz. Eğer emin değilseniz MySQL olarak ayarlayın.',
	'DBPASSWD' => 'Veritabanı şifresi',
	'DBPASSWD_EXPLAIN' => 'Veritabanı kullanıcınızın şifresi',
	'DBPORT' => 'Veritabanı portu',
	'DBPORT_EXPLAIN' => 'Çoğu zaman boş bırakılabilir.',
	'DBUSER' => 'Veritabanı kullanıcısı',
	'DBUSER_EXPLAIN' => 'Veritabanınızın kullanıcısı. Bu, yeni veritabanı oluşturmak için izin verilmiş bir kullanıcı olmalıdır.',
	'DEFAULT' => 'varsayılant',
	'DEFAULT_ENV' => 'Varsayılan ortam (son phpBB)',
	'DEFAULT_LANG' => 'Varsayılan dil',
	'DEFAULT_LANG_EXPLAIN' => 'Bu dil forumları oluşturmak için kullanılacaktır.',
	'DELETE' => 'Sil',
	'DELETE_FILES_IF_EXIST' => 'Eğer mevcutsa dosyaları sil',
	'DIR_EXISTS' => '%s adlı dizin zaten mevcut.',
	'DISABLED' => 'Kapat',
	'DROP_DB_IF_EXISTS' => 'Eğer mevcutsa veritabanını kaldır',

	'EMAIL_DOMAIN' => 'E-posta alan adı',
	'EMAIL_DOMAIN_EXPLAIN' => 'Test kullanıcılar için kullanılacak e-posta alan adı. Bu kullanıcıların e-posta adresi tester_x@&lt;domain.com&gt; şeklinde olacaktır.',
	'EMAIL_ENABLE' => 'E-postayı aç',
	'EMAIL_ENABLE_EXPLAIN' => 'Mesaj panosu genelinde e-postaları açar. Localde test edilen forumlar için e-postaları test etmedikçe bu genellikle kapalı olmalıdır.',
	'ENABLED' => 'Aç',

	'GENERAL_ERROR' => 'Genel Hata',

	'IN_SETTINGS' => 'QuickInstall ayarlarınızı yönetin.',
	'INCLUDE_MODS' => 'MODları dahil et',
	'INCLUDE_MODS_EXPLAIN' => 'Bu listedeki kaynaklar/modlar/ klasöründen klasörleri seçin, bu dosyalar yeni mesaj panonuzun ana dizinine kopyalanacaktır, ayrıca eski dosyaların üzerine yazılacaktır (Böylece örneğin buradan premod destekli bir mesaj panosu sahibi olabilirsiniz). Eğer “Hiçbiri” seçeneğini seçerseniz, bunlar kullanılmayacaktır (çünkü ögelerin seçimini kaldırmak için bu bir yoldur).',
	'INSTALL_BOARD' => 'Bir mesaj panosu kur',
	'INSTALL_QI' => 'QuickInstall’u kur',
	'IS_NOT_VALID' => 'Geçerli değil.',

	'LICENSE' => 'Lisans?',
	'LICENSE_EXPLAIN' => 'Bu yazılım scripti <a href="license.txt">GNU General Public License version 2</a> koşulları altında yayınlanmıştır. Bu lisans altında yayınlanmasının başlıca sebebi phpBB kodlarının büyük bölümünün kullanılmasıdır, ve herhangi bir modifikasyon ve değişikliklerde bu lisansın kullanılması gereklidir. Fakat aynı zamanda bu lisans özgür yazılımı özgür tutan büyük bir lisanstır :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>phpBB Quickinstall sürüm %s tarafından kuruldu</strong>',

	// To translators: Lorem Ipsum is a dummy place holder string. Do not translate this string.
	'LOREM_IPSUM' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE' => 'Dosyaların yazılabilir olduğuna emin ol',
	'MAKE_WRITABLE_EXPLAIN' => 'Dosyaları, config.php dosyasını, ve dizinleri varsayılan olarak yazılabilir ayarlar. Bir forum oluşturduktan sonra bu ayar değiştirilebilir.',
	'MANAGE_BOARDS' => 'Mesaj panosu yönetimi',
	'MAX' => 'En yüksek',
	'MIGHT_TAKE_LONG' => '<strong>Not:</strong> Mesaj panosunun oluşturulması bir dakikadan daha uzun bir zaman alabilir, bu yüzden formu iki kere <strong>göndermeyin</strong>.',
	'MIN' => 'En düşük',

	'NEED_EMAIL_DOMAIN' => 'Test kullanıcıların oluşturulması için bir e-posta alan adı gereklidir',
	'NEED_WRITABLE' => 'QuickInstall, mesaj panolarının ve cache dizinlerinin her zaman yazılabilir olmasına gereksinim duyar.<br />QuickInstall’un kurulumu için sadece qi_config.php dosyası yazılabilir olmalıdır.',
	'NO' => 'Hayır',
	'NO_ALT_ENV' => 'Belirtilen alternatif ortam yok.',
	'NO_AUTOMOD' => 'Kaynak dizinde AutoMOD bulunamadı. AutoMOD’u indirmelisiniz ve sources/automod dizininin içerisine kopyalamalısınız.',
	'NO_BOARDS' => 'Hiç bir mesaj panonuz yok.',
	'NO_DB' => 'Hiç bir veritabanı seçilmedi.',
	'NO_IMPACT_WIN' => 'Bu ayarın Win7’den daha eski Windows sistemlerinde bir etkisi olmaz.',
	'NO_MODULE' => '%s modülü yüklenemedi.',
	'NO_PASSWORD' => 'Şifre yok',
	'NO_DBPASSWD_ERR' => 'Bir veritabanı şifresi belirttiniz ve şifre yok seçeneğini işaretlediniz. Hem bir şifreniz varken hemde şifrenizin olmadığını belirtemezsiniz.',
	'NONE' => 'Yok',
	'NUM_CATS' => 'Kategorilerin sayısı',
	'NUM_CATS_EXPLAIN' => 'Oluşturacağınız forum kategorilerinin sayısı.',
	'NUM_FORUMS' => 'Forumların sayısı',
	'NUM_FORUMS_EXPLAIN' => 'Oluşturacağınız forumların sayısı, bunlar oluşturduğunuz kategorilere dağıtılacak.',
	'NUM_NEW_GROUP' => 'Yeni kayıt olanlar',
	'NUM_NEW_GROUP_EXPLAIN' => 'Yeni kayıt olanlar gurubuna yerleştirilecek kullanıcıların sayısı.<br />Eğer bu sayı kullanıcıların sayısından büyük olursa, tüm yeni kullanıcılar yeni kayıt olanlar gurubuna yerleştirilecektir.',
	'NUM_REPLIES' => 'Cevapların sayısı',
	'NUM_REPLIES_EXPLAIN' => 'Cevapların sayısı. Her başlık cevapların en yüksek ve en düşük değerleri arasında rastgele sayıda cevaplar alacaktır.',
	'NUM_TOPICS' => 'Başlıkların sayısı',
	'NUM_TOPICS_EXPLAIN' => 'Her forumda oluşturulacak başlıkların sayısı. Her forum başlıkların en yüksek ve en düşük değerleri arasında rastgele sayıda başlıklar alacaktır.',
	'NUM_USERS' => 'Kullanıcıların sayısı',
	'NUM_USERS_EXPLAIN' => 'Mesaj panonuzun nüfusunu arttırmak için kullanıcı sayısı.<br />Bunlar Tester_x (x değeri kullanıcı sayısına göre değişir) şeklinde kullanıcı adları alacaklardır. Kullanıcı şifreleri ise "123456" olacaktır.',

	'ONLY_LOCAL' => 'Not: QuickInstall sadece lokal olarak kullanılmak üzere tasarlanmıştır.<br />Internet üzerinden erişilebilen bir web sunucusu üzerinde kullanılamaz.',
	'OPTIONS' => 'Ayarlar',
	'OPTIONS_ADVANCED' => 'Gelişmiş ayarlar',

	'POPULATE' => 'Mesaj panosu nüfusunu arttır',
	'POPULATE_OPTIONS' => 'Nüfus arttırma ayarları',
	'POPULATE_MAIN_EXPLAIN' => 'Kullanıcılar: tester x, Şifre: 123456',
	'POPULATE_EXPLAIN' => 'Mesaj panosu nüfusu, aşağıda belirleyeceğiniz kullanıcı, forum, mesaj ve başlık sayıları ile arttırılır. İstediğinizden fazla kullanıcı, forum, mesaj ve başlık belirlemeyin, aksi takdirde forumu oluşturmak çok uzun bir zaman alacaktır.<br />Bir forum oluşturduğunuz zaman tüm bu ayarlar değiştirilebilir.',

	'QI_ABOUT' => 'Hakkında',
	'QI_ABOUT_ABOUT' => 'Big brother sizi seviyor ve sizinle mutlu olmak istiyor.',
	'QI_DST' => 'Yaz saati',
	'QI_DST_EXPLAIN' => 'Gün ışığından yararlanma zamanını açmak ya da kapatmak istiyor musunuz?',
	'QI_LANG' => 'QuickInstall dili',
	'QI_LANG_EXPLAIN' => 'QuickInstall kullanma dili. language/ dizini içerisinde kullanılacak dilin isminde bir dizine ihtiyaç vardır. Ayrıca bu dil eğer sources/phpBB3/language/ dizininde de mevcutsa forumlarınız için varsayılan dil olarak kullanılacaktır.',
	'QI_MAIN' => 'Ana sayfa',
	'QI_MAIN_ABOUT' => 'Buradan yeni bir mesaj panosu kurabilirsiniz.<br /><br />Sadece “Mesaj panosu veritabanı adı” doldurmanız gereken bir alandır, diğerleri <em>includes/qi_config.php</em> dosyasından varsayılan değerler alınarak doldurulmuştur.<br /><br />Daha fazla bilgi için “Gelişmiş ayarlar” bağlantısına tıklayın.',
	'QI_MANAGE' => 'Mesaj panolarını yönet',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Zaman dilimi',
	'QI_TZ_EXPLAIN' => 'Zaman diliminiz. Bu, oluşturulan forumlar için varsayılan zaman dilimi olacaktır. -1, 0, 1 v.b. gibi.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Yönlendirme',
	'REDIRECT_EXPLAIN' => 'Yeni forumlar için yönlendirme varsayılan olarak evet şeklinde ayarlanmıştır. Bu ayar bir forum oluşturulduktan sonra değiştirilebilir.',
	'REDIRECT_BOARD' => 'Yeni mesaj panosuna yönlendirme',
	'REQUIRED' => 'gereklidir',
	'RESET' => 'Sıfırla',

	'SELECT' => 'Seç',
	'SETTINGS' => 'Ayarlar',
	'SETTINGS_FAILURE' => 'Hatalar oluştu, ayrıntılı bilgi için alttaki kutuya bakın.',
	'SETTINGS_SUCCESS' => 'Ayarlarınız başarıyla kaydedildi.',
	'SERVER_NAME' => 'Sunucu adı',
	'SERVER_NAME_EXPLAIN' => 'QuickInstall genel sunucular için tasarlanmadığı için bu ayar tipik olarak localhost olmalıdır.',
	'SERVER_PORT' => 'Sunucu port',
	'SERVER_PORT_EXPLAIN' => 'Genellikle 80 olur.',
	'SITE_DESC' => 'Site açıklaması',
	'SITE_DESC_EXPLAIN' => 'Forumlar için kullanılacak varsayılan bir açıklama. Bu ayar forumlar oluşturulduktan sonra değiştirilebilir.',
	'SITE_NAME' => 'Site adı',
	'SITE_NAME_EXPLAIN' => 'Forumlarınız için kullanılacak varsayılan site adı. Bu ayar forumlar oluşturulduktan sonra değiştirilebilir.',
	'SMTP_AUTH' => 'SMTP için yetkilendirme metotu',
	'SMTP_AUTH_EXPLAIN' => 'Sadece kullanıcı adı/şifre ayarlandıysa kullanılır.',
	'SMTP_DELIVERY' => 'E-posta için SMTP sunucusu kullan',
	'SMTP_DELIVERY_EXPLAIN' => 'Lokal mail fonksiyonu yerine bir named sunucusu üzerinden e-posta gönderilmesini istiyorsanız “Evet” olarak ayarlayın.',
	'SMTP_HOST' => 'SMTP sunucu adresi',
	'SMTP_HOST_EXPLAIN' => 'Kullanmak istediğiniz SMTP sunucu adresi',
	'SMTP_PASS' => 'SMTP şifresi',
	'SMTP_PASS_EXPLAIN' => 'Sadece SMTP sunucunuz istiyorsa bir şifre girin.',
	'SMTP_PORT' => 'SMTP sunucu portu',
	'SMTP_PORT_EXPLAIN' => 'Sadece SMTP sunucunuzun farklı bir port üzerinde olduğunu biliyorsanız bu ayarı değiştirin.',
	'SMTP_USER' => 'SMTP kullanıcı adı',
	'SMTP_USER_EXPLAIN' => 'Sadece SMTP sunucunuz istiyorsa bir kullanıcı adı girin.',
	'STAR_MANDATORY' => '* = zorunlu',
	'SUBMIT' => 'Gönder',
	'SUBSILVER' => 'Subsilver2’yi kur',
	'SUBSILVER_EXPLAIN' => 'Eğer Subsilver2 temasının kurulmasını ve varsayılan stil olarak ayarlanmasını istiyorsanız seçin. Bu ayar bir forum oluşturulduktan sonra değiştirilebilir.',
	'SUCCESS' => 'Başarılı',

	'TABLE_PREFIX' => 'Tablo öneki',
	'TABLE_PREFIX_EXPLAIN' => 'Forumlarınız için kullanılacak tablo öneki. Bu ayarı yeni forumlarınızı oluşturduktan sonra gelişmiş ayarlar bölümünden değiştirebilirsiniz.',
	'TEST_CAT_NAME' => 'Test kategori %d',
	'TEST_FORUM_NAME' => 'Test forum %d',
	'TEST_POST_START' => 'Test mesaj %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Test başlık %d',

	'UNCHECK_ALL' => 'İşaretlenenleri kaldır',
	'UP_TO_DATE' => 'Big brother güncel olduğunuzu söylüyor.',
	'UP_TO_DATE_NOT' => 'Big brother güncel olmadığınızı söylüyor.',
	'UPDATE_CHECK_FAILED' => 'Big brother’ın sürüm kontrolü başarısız oldu.',
	'UPDATE_TO' => '<a href="%1$s">%2$s sürümüne güncelleyin.</a>',

	'YES' => 'Evet',

	'VERSION_CHECK' => 'Büyük sürüm kontrolü',
	'VISIT_BOARD' => '<a href="%s">Mesaj panosunu ziyaret edin</a>',

	'WHAT' => 'Ne?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall, phpBB’yi kolayca kurmaya yarayan bir yazılımdır. Oldukça açıklayıcı oldu... ;-)',
	'WHO_ELSE' => 'Başka kim?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'phpBB takımına teşekkürler, özellikle yazılımın mükemmel parçalarını oluşturan geliştirme takımına...',
		'Bu paket içerisine dahil olan AutoMOD için phpBB.com MOD takımına (özellikle Josh’a, diğer adıyla “A_Jelly_Doughnut”’a) teşekkürler.',
		'Güzel logo için Mike TUMS’a teşekkürler!',
		'Beta testçilere teşekkürler!',
		'phpBB.com dahilindeki phpBB topluluk forumlarına, startrekguide.com ve phpBBModders.net’e teşekkürler!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Kim? Ne zaman?',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall orijinal olarak Igor “eviL&lt;3” Wiedler tarafından 2007 yaz aylarında oluşturulmuştur. Yazılım gene aynı kişi tarafından, Mart 2008’de kısmen yeniden yazılmıştır.<br />Bu proje Mart 2010’dan beri Jari “Tumba25” Kanerva tarafından devam ettirilmektedir.',
	'WHY' => 'Neden?',
	'WHY_EXPLAIN' => 'Tıpkı phpBB2deki gibi, eğer bir çok mod yaptıysanız (modifikasyon oluşturma), tüm MODları basit bir phpBB kurulumuna koyamazsınız. Bu yüzden, ayrı ayrı kurulumlar en iyisidir. Şimdiki problem ise dosyaları kopyalamak ve kurulum işlemi boyunca devam etmek sıkıntısıdır. İşte bu işlemleri hızlandırmak için, quickinstall doğdu.',
));

?>