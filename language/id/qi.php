<?php
/**
*
* qi [English]
* Translated by zourbuth, 2010
* Email: zourbuth@gmail.com
* Site: http://www.phpbb-id.com
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
	'ABOUT_QUICKINSTALL' => 'Perihal phpBB3 QuickInstall',
	'ADMIN_EMAIL' => 'Email admin',
	'ADMIN_EMAIL_EXPLAIN' => 'Email admin yang digunkan untuk forum anda',
	'ADMIN_NAME' => 'Nama pengguna administrator',
	'ADMIN_NAME_EXPLAIN' => 'Nama pengguna dasar admin yang akan digunakan pada forum. Bagian ini bisa diganti setelah forum dibuat.',
	'ADMIN_PASS' => 'Kata sandi administrator',
	'ADMIN_PASS_EXPLAIN' => 'Kata sandi default admin yang digunakan pada forum. Bagian ini bisa diganti setelah forum dibuat.',
	'ALT_ENV' => 'Linkungan alternatif',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Atur install AutoMOD menjadi ya sebagai default. Bagian ini bisa diganti setelah forum dibuat.',
	'AUTOMOD_INSTALL' => 'Instal AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Kembali ke papan utama</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Kembali ke papan manajemen</a>',
	'BOARD_CREATED' => 'Papan berhasil dibuat!',
	'BOARD_DBNAME' => 'Database papan dan nama direktori',
	'BOARD_DESC' => 'Deskripsi papan',
	'BOARD_EMAIL' => 'Email papan',
	'BOARD_EMAIL_EXPLAIN' => 'Email pengirim untuk forum yang dibuat.',
	'BOARD_NAME' => 'Nama papan',
	'BOARDS_DELETED' => 'Papan berhasil dihapus.',
	'BOARDS_DELETED_TITLE' => 'Papan dihapus',
	'BOARDS_DIR' => 'Direktori papan',
	'BOARDS_DIR_EXPLAIN' => 'Direktori dimana forum anda akan dibuat. PHP membutuhkan perijinan penulisan pada direktori ini.',
	'BOARDS_LIST' => 'Daftar papan',
	'BOARDS_NOT_WRITABLE' => 'Direktori papan tidak bisa ditulisi.',

	'CACHE_NOT_WRITABLE' => 'Direktori cache atau tembolok tidak bisa ditulisi.',
	'CHANGELOG' => 'Log pengubahan',
	'CHECK_ALL' => 'Cek semua',
	'CONFIG_EMPTY' => 'Config array kosong. Ini kemungkinan sebuah laporan bug.',
	'CONFIG_NOT_WRITABLE' => 'qi_config.php tidak bisa ditulisi.',
	'COOKIE_DOMAIN' => 'Domain cookie',
	'COOKIE_DOMAIN_EXPLAIN' => 'Biasanya adalah localhost.',
	'COOKIE_SECURE' => 'Cookie aman',
	'COOKIE_SECURE_EXPLAIN' => 'Jika server anda berjalan melalui SSL, maka pilih aktifkan, selain itu biarkan nonaktif. Dengan mengaktifkan bagian ini dan ternyata tidak berjalan via SSL akan menghasilkan error pada server selama pengalihan.',
	'CREATE_ADMIN' => 'Buat admin',
	'CREATE_ADMIN_EXPLAIN' => 'Pilih menjadi ya jika anda hanya menginginkan satu admin saja yang dibuat, dan tidak akan menjadi seorang pendiri. Ini akan menjadi tester_1.',
	'CREATE_MOD' => 'Buat moderator',
	'CREATE_MOD_EXPLAIN' => 'Pilih menjadi ya jika anda menginginkan satu global moderator dibuat. Ini akan menjadi tester_1 atau tester_2 jika admin dipilih.',

	'DB_EXISTS' => 'Database %s sudah ada.',
	'DB_PREFIX' => 'Awalan prefik database',
	'DB_PREFIX_EXPLAIN' => 'Ini ditambahkan sebelum semua nama database untuk menghindari penimpaan database yang tidak digunakan oleh QuickInstall.',
	'DBHOST' => 'Server database',
	'DBHOST_EXPLAIN' => 'Biasanya localhost.',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => 'Sistem database anda. Jika anda tidak yakin maka pilih MySQL.',
	'DBPASSWD' => 'Kata sandi database',
	'DBPASSWD_EXPLAIN' => 'Kata sandi untuk pengguna database anda',
	'DBPORT' => 'port database',
	'DBPORT_EXPLAIN' => 'Bisa dibiarkan kosong.',
	'DBUSER' => 'Pengguna database',
	'DBUSER_EXPLAIN' => 'Pengguna database anda. Bagian ini membutuhkan seorang pengguna dengan perijinannya untuk membuat sebuah database baru.',
	'DEFAULT' => 'default',
	'DEFAULT_ENV' => 'Linkungan default (phpBB terbaru)',
	'DEFAULT_LANG' => 'Bahasa default',
	'DEFAULT_LANG_EXPLAIN' => 'Bahasa ini akan digunakan untuk forum yang dibuat.',
	'DELETE' => 'Hapus',
	'DELETE_FILES_IF_EXIST' => 'Hapus file jika sudah ada',
	'DIR_EXISTS' => 'direktori %s sudah ada.',
	'DISABLED' => 'Nonaktif',
	'DROP_DB_IF_EXISTS' => 'Hapus database jika sudah ada',

	'EMAIL_DOMAIN' => 'Domain email',
	'EMAIL_DOMAIN_EXPLAIN' => 'Domain emal yang digunakan untuk pencoba. Emailnya akan menjadi tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE' => 'Aktifkan email',
	'EMAIL_ENABLE_EXPLAIN' => 'Aktifkan email papan menyeluruh. Untuk percobaan lokal biasanya bagian ini tidak aktif atau off, kecuali anda mencoba emailnya.',
	'ENABLED' => 'Aktif',

	'GENERAL_ERROR' => 'General Error',

	'IN_SETTINGS' => 'Atur pengaturan QuickInstall anda.',
	'INCLUDE_MODS' => 'Termasuk MOD',
	'INCLUDE_MODS_EXPLAIN' => 'Pilih folder dari folder sources/mods/ pada daftar ini, berkas-berkas tersebut selanjutnya akan disalin ke direktori induk papan baru anda, dan juga akan menimpa semua berkas lama (disini anda memiliki papan pramodifikasi sebagai contohnya). Jika anda memilih  “Tidak”, maka tidak akan digunakan (karena cukup sulit untuk tidak memilih item).',
	'INSTALL_BOARD' => 'Instal sebuah papan',
	'INSTALL_QI' => 'Instal QuickInstall',
	'IS_NOT_VALID' => 'Tidak sah.',

	'LICENSE' => 'lisensi?',
	'LICENSE_EXPLAIN' => 'Skrip ini dirilis dibawah <a href="license.txt">GNU General Public License version 2</a>. Hal ini pada dasarnya adalah karena penggunaan kode phpBB dengan jumlah yang cukup banyak, yang juga dirilis di bawah lisensi ini, dan membutuhkan beberapa modifikasi untuk penggunaannya. Tetapi juga karena sebuah lisensi yang bagus sehingga perangkat lunak bebas menjadi gratis :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Diinstal oleh Quickinstall phpBB versi %s</strong>',
	'LOREM_IPSUM' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE' => 'Buat file bisa ditulisi',
	'MAKE_WRITABLE_EXPLAIN' => 'Buat berkas, config.php, dan direktori bisa ditulisi secara bawaannya. Bagian ini bisa diganti pada saat anda membuat forum.',
	'MANAGE_BOARDS' => 'Atur papan',
	'MAX' => 'Max',
	'MIGHT_TAKE_LONG' => '<strong>Mohon dicatat:</strong> Pembuatan papan bisa memakan waktu, mungkin bisa semenit atau lebih lama, jadi <strong>jangan</strong> ajukan dua kali.',
	'MIN' => 'Min',

	'NEED_EMAIL_DOMAIN' => 'Sebuah domain email dibutuhkan untuk membuat pengguna percobaan',
	'NEED_WRITABLE' => 'QuickInstall membutuhkan papan dan direktori cache bisa ditulisi setiap saat.<br />qi_config.php cukup bisa ditulisi untuk instalasi QuickInstall.',
	'NO' => 'Tidak',
	'NO_ALT_ENV' => 'Lingkungan alternatif yang ditentukan tidak tersedia.',
	'NO_AUTOMOD' => 'AutoMOD tidak ditemukan di direktori sumber. Anda harus mengunduh AutoMOD dan salinlah ke direktori induk ke sources/automod, kemudian ganti nama direktori menjadi automod.',
	'NO_BOARDS' => 'Anda tidak memiliki papan apapun.',
	'NO_DB' => 'Tidak ada database dipilih.',
	'NO_IMPACT_WIN' => 'Pengaturan tidak memiliki dampak pada sistem Windows systems yang lebih rendah dari Win7.',
	'NO_MODULE' => 'Modul %s tidak bisa dimunculkan.',
	'NO_PASSWORD' => 'Tidak ada kata sandi',
	'NO_DBPASSWD_ERR' => 'Anda harus mengatur sebuah kata sandi db dan cek tanpa kata sandi. Anda tidak bisa memilih kedua <strong>punya</strong> dan <strong>tidak punya</strong> kata sandi',
	'NONE' => 'Tidak ada',
	'NUM_CATS' => 'Jumlah kategori',
	'NUM_CATS_EXPLAIN' => 'Jumlah forum kategori untuk dibuat.',
	'NUM_FORUMS' => 'Jumlah forum',
	'NUM_FORUMS_EXPLAIN' => 'Jumlah forum untuk dibuat, yang akan disebar diseluruh kategori yang dibuat.',
	'NUM_NEW_GROUP' => 'Baru terdaftar',
	'NUM_NEW_GROUP_EXPLAIN' => 'Jumlah pengguna yang akan ditempatkan di grup pengguna baru terdaftar.<br />Jika angka ini lebih besar dari jumlah pengguna, semua pengguna baru akan dimasukkan ke dalam grup pengguna baru terdaftar.',
	'NUM_REPLIES' => 'Jumlah balasan',
	'NUM_REPLIES_EXPLAIN' => 'Jumlah balasan. Setiap topik akan menerima sebuah angka acak untuk balasan antar nilai max dan min ini.',
	'NUM_TOPICS' => 'Jumlah topik',
	'NUM_TOPICS_EXPLAIN' => 'Jumalh topik untuk dibuat di setiap forum. Setiap forum akan mendapatkan sebuah angka acak dari topik antara nalai max dan min ini.',
	'NUM_USERS' => 'Jumlah pengguna',
	'NUM_USERS_EXPLAIN' => 'Jumlah pengguna untuk dipopulasikan ke papan baru.<br />Mereka akan mendapat nama pengguna Tester_x (x adalah 1 ke num_users). Kesemuanya akan mendapatkan kata sandi "123456"',

	'ONLY_LOCAL' => 'Mohon dicatata: QuickInstall hanya ditujukan untuk digunakan secara lokal.<br />Sebaiknya tidak digunakan pada sebuah server web yang bisa diakses melalui internet.',
	'OPTIONS' => 'Pilihan',
	'OPTIONS_ADVANCED' => 'Pilihan lanjutan',

	'POPULATE' => 'Populasikan papan',
	'POPULATE_OPTIONS' => 'Populasikan pilihan',
	'POPULATE_MAIN_EXPLAIN' => 'Pengguna: tester x, Kata sandi: 123456',
	'POPULATE_EXPLAIN' => 'Mempopulasikan papan dengan jumlah pengguna, forum, post dan topik yang anda tentukan di bawah. Semakin banyak pengguna, forum, post dan topik yang anda inginkan, maka akan semakin lama waktu yang dibutuhkan untuk membuat forum.<br />Semua pengaturan ini bisa diubah setelah anda membuat forum.',

	'QI_ABOUT' => 'Perihal',
	'QI_ABOUT_ABOUT' => 'Kakak tertua mencintaimu dan menginginkanmu bahagia.',
	'QI_DST' => 'Daylight saving time',
	'QI_DST_EXPLAIN' => 'Apakah anda ingin daylight saving time digunakan atau tidak?',
	'QI_LANG' => 'Bahasa QuickInstall',
	'QI_LANG_EXPLAIN' => 'Bahasa yang digunakan QuickInstall. Harus ada direktori dengan nama ini di language/. Bahasa ini akan digunakan sebagai bahasa bawaan di forum anda jika bahasa tersedia di sources/phpBB3/language/.',
	'QI_MAIN' => 'Halaman utama',
	'QI_MAIN_ABOUT' => 'Instal sebuah papan baru di sini.<br /><br />“Nama database papan” adalah isian yang perlu anda isi saja, yang lainnya akan diisi dengan nilai default dari <em>includes/qi_config.php</em>.<br /><br />Klik “Pilihan lanjutan” untuk pengaturan lainnya.',
	'QI_MANAGE' => 'Atur papan',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Zona waktu',
	'QI_TZ_EXPLAIN' => 'Aona waktu anda. Merupakan zona waktu untuk forum yang dibuat. -1, 0, 1 dst.',
	'QUICKINSTALL' => 'QuickInstall phpBB',

	'REDIRECT' => 'Pengalihan',
	'REDIRECT_EXPLAIN' => 'Atur pengalihan ke forum baru menjadi ya sebagai defaultnya. Ini bisa dibuat pada saat anda membuat forum.',
	'REDIRECT_BOARD' => 'Alihkan ke papan baru',
	'REQUIRED' => 'dibutuhkan',
	'RESET' => 'Reset',

	'SELECT' => 'Pilih',
	'SETTINGS' => 'Pengaturan',
	'SETTINGS_FAILURE' => 'Ada kesalahan, lihat pada kotak di bawah ini.',
	'SETTINGS_SUCCESS' => 'Pengaturan anda berhasil disimpan.',
	'SERVER_NAME' => 'Nama server',
	'SERVER_NAME_EXPLAIN' => 'Biasanya adalah localhost sejak QuickInstall <strong>tidak</strong> bertujuan untuk penggunaan server publik.',
	'SERVER_PORT' => 'Posr server',
	'SERVER_PORT_EXPLAIN' => 'Biasanya 80.',
	'SITE_DESC' => 'Deskripsi situs',
	'SITE_DESC_EXPLAIN' => 'Deskripsi default untuk forum anda. Ini bisa dibuat pada saat forum diciptakan.',
	'SITE_NAME' => 'Nama situs',
	'SITE_NAME_EXPLAIN' => 'Nama situs defaultnya adalah nama yang akan digunakan pada forum. Bagian ini bisa diubah setelah forum dibuat.',
	'SMTP_AUTH' => 'Metode otentifikasi untuk SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Gunakan juka nama pengguna/kata sandi diatur.',
	'SMTP_DELIVERY' => 'Gunakan server SMTP untuk e-mail',
	'SMTP_DELIVERY_EXPLAIN' => 'Pilih “Ya” jika anda ingin mengirim email melalui sebauh nama server daripada fungsi email lokal.',
	'SMTP_HOST' => 'Alamat server SMTP',
	'SMTP_HOST_EXPLAIN' => 'Alamat server SMTP yang ingin anda gunakan',
	'SMTP_PASS' => 'Kata sandi SMTP',
	'SMTP_PASS_EXPLAIN' => 'Hanya isi sebuah kata sandi jika server SMTP memintanya.',
	'SMTP_PORT' => 'Port server SMTP',
	'SMTP_PORT_EXPLAIN' => 'Hanya ganti jika anda mengetahui server SMTP anda pada port yang berbeda.',
	'SMTP_USER' => 'Nama pengguna SMTP',
	'SMTP_USER_EXPLAIN' => 'Hanya masukkan nama pengguna jika server SMTP anda membutuhkannya.',
	'STAR_MANDATORY' => '* = perintah',
	'SUBMIT' => 'Ajukan',
	'SUBSILVER' => 'instal Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Pilih jika anda ingin thema Subsilver2 diinstal menjadi gaya default. Ini bisa diganti pada saat anda membuat sebuah forum.',
	'SUCCESS' => 'Berhasil',

	'TABLE_PREFIX' => 'Prefik table',
	'TABLE_PREFIX_EXPLAIN' => 'Prefil tabel yang akan digunakan pada forum anda. Anda bisa menganti bagian ini di pilihan lanjutan pada saat anda membuat forum baru.',
	'TEST_CAT_NAME' => 'Kategori percobaan %d',
	'TEST_FORUM_NAME' => 'Forum percobaan %d',
	'TEST_POST_START' => 'Post percobaan %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Topik percobaan %d',

	'UNCHECK_ALL' => 'Jangan pilih semua',
	'UP_TO_DATE' => 'Instalasi anda belum terbarukan.',
	'UP_TO_DATE_NOT' => 'Instalasi anda belum terbarukan.',
	'UPDATE_CHECK_FAILED' => 'Pemeriksaan pembaruan gagal.',
	'UPDATE_TO' => '<a href="%1$s">Membarui ke versi %2$s.</a>',

	'YES' => 'Ya',

	'VERSION_CHECK' => 'Pemeriksaan versi',
	'VISIT_BOARD' => '<a href="%s">Kunjungi papan</a>',

	'WHAT' => 'Apa?',
	'WHAT_EXPLAIN' => 'QuickInstall phpBB3 adalah sebuah skrip untuk instal cepat phpBB.',
	'WHO_ELSE' => 'Siapa yang lainnya?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Credits go to the phpBB team, especially the development team which created such a wonderful piece of software.',
		'Thanks to the phpBB.com MOD team (especially Josh, aka “A_Jelly_Doughnut”) for AutoMOD, which is included in this package.',
		'Thanks to Mike TUMS for the nice logo!',
		'Thanks to the beta testers!',
		'Thanks to the phpBB community including phpBB.com, startrekguide.com and phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Siapa? Kapan?',
	'WHO_WHEN_EXPLAIN' => 'QuickInstall phpBB3 dibuat oleh Igor “eviL&lt;3” Wiedler di musim panas 2007. Kemudian ditulisnya ulang pada bulan Maret 2008.<br />Sejak Maret 2010 proyek ini ditangani oleh Jari “Tumba25” Kanerva.',
	'WHY' => 'Kenapa?',
	'WHY_EXPLAIN' => 'Sama seperti phpBB2, jika anda banyak melakukan modifikasi (membuat modifikasi), maka anda tidak bisa memasukkan semua MOD pada sebuah instalasi phpBB. Sehingga lebih baik memiliki instalasi yang terpisah. Permasalahannya adalah cukup sulit untuk menyalin berkas melalui proses instalasi setiap waktu. Untuk mempercepat hal ini, maka Quickinstall lahir.',
));

?>