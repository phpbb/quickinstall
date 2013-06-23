<?php
/**
*
* qi [Română]
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
    'ABOUT_QUICKINSTALL' => 'Despre phpBB3 QuickInstall',
    'ADMIN_EMAIL' => 'E-mail administrator',
    'ADMIN_EMAIL_EXPLAIN' => 'E-mailul administratorului care va fi folosit pentru forum-uri',
    'ADMIN_NAME' => 'Numele de utilizator al administratorului',
    'ADMIN_NAME_EXPLAIN' => 'Numele de utilizator implicit al administratorului care va fi folosit pe forum-uri. Acesta poate fi schimbat atunci când creaţi forumurile.',
    'ADMIN_PASS' => 'Parola administratorului',
    'ADMIN_PASS_EXPLAIN' => 'Parola implicită a administratorului care va fi folosită pe forumuri. Aceasta poate fi schimbată atunci când creaţi forumurile.',
    'ALT_ENV' => 'Mediu alternativ',
    'AUTOMOD' => 'AutoMOD',
    'AUTOMOD_EXPLAIN' => 'Setatare ca implicit instalarea AutoMOD. Aceasta poate fi schimbată atunci când creaţi un forum.',
    'AUTOMOD_INSTALL' => 'Instalare AutoMOD',

	  'BACK_TO_MAIN' => '<a href="%s">Înapoi la pagina principală</a>',
    'BACK_TO_MANAGE' => '<a href="%s">Înapoi la pagina de administrare</a>',
    'BOARD_CREATED' => 'Forumul a fost creat cu succes!',
    'BOARD_DBNAME' => 'Baza de date a forumului şi numele directorului',
    'BOARD_DESC' => 'Descrierea forumului',
    'BOARD_EMAIL' => 'Adresa de e-mail a forumului',
    'BOARD_EMAIL_EXPLAIN' => 'Expeditorul e-mailurilor pentru forumurile create.',
    'BOARD_NAME' => 'Numele forumului',
    'BOARDS_DELETED' => 'Forumurile au fost şterse cu succes.',
    'BOARDS_DELETED_TITLE' => 'Forumuri şterse',
    'BOARDS_DIR' => 'Directorul forumurilor',
    'BOARDS_DIR_EXPLAIN' => 'Directorul unde forumurile vor fi create. Este nevoie ca PHP să aibă permisiuni de scriere pe acest director.',
    'BOARDS_LIST' => 'Lista forumurilor',
    'BOARDS_NOT_WRITABLE' => 'Directorul forumurilor nu poate fi scris.',

		'CACHE_NOT_WRITABLE' => 'Directorul cache nu poate fi scris.',
    'CHANGELOG' => 'Log schimbări',
    'CHECK_ALL' => 'Verifică tot',
		'CONFIG_EMPTY' => 'Şirul de configurare a fost găsit gol. Probabil e nevoie să raportaţi problema.',
		'CONFIG_NOT_WRITABLE' => 'Fişierul qi_config.php nu poate fi scris.',
    'COOKIE_DOMAIN' => 'Domeniu cookie',
    'COOKIE_DOMAIN_EXPLAIN' => 'Acesta ar putea fi de obicei localhost.',
    'COOKIE_SECURE' => 'Securizare cookie',
		'COOKIE_SECURE_EXPLAIN' => 'Setaţi această opţiune dacă serverul propriu funcţionează via SSL, altfe lasaţi neselectat. Av&and această opţiune activată şi nefolosind SSL, va determina erori ale serverului &in timpul redirectărilor.',
		'CREATE_ADMIN' => 'Crează administrator',
    'CREATE_ADMIN_EXPLAIN' => 'Setaţi pe da dacă doriţi să fie creat un administrator care nu va fi fondator. Acesta va fi tester_1.',
    'CREATE_MOD' => 'Crează moderator',
    'CREATE_MOD_EXPLAIN' => 'Setaţi pe da dacă doriţi să fie creat un moderator global. Acesta va fi tester_1 sau tester_2 dacă un administrator este selectat.',

		'DB_EXISTS' => 'Baza de date %s există deja.',
    'DB_PREFIX' => 'Prefix bază de date',
		'DB_PREFIX_EXPLAIN' => 'Aceasta este adăugată &inaintea oricărui nume pentru baza de date pentru a evita suprascrierea bazelor de date nefolosite de QuickInstall.',
		'DBHOST' => 'Server bază de date',
    'DBHOST_EXPLAIN' => 'De obicei localhost.',
    'DBMS' => 'DBMS',
    'DBMS_EXPLAIN' => 'Sistemul bazei de date. Dacă nu sunteţi sigur setaţi-l ca MySQL.',
    'DBPASSWD' => 'Parola bază de date',
    'DBPASSWD_EXPLAIN' => 'Parola utilizatorului bazei de date',
    'DBPORT' => 'Port bază de date',
    'DBPORT_EXPLAIN' => 'De cele mai multe ori câmpul poate fi lăsat gol.',
    'DBUSER' => 'Utilizator bază de date',
    'DBUSER_EXPLAIN' => 'Utilizatorul bazei de date. Este necesar să fie un utilizator care are permisiuni de creare a unor noi baze de date.',
    'DEFAULT' => 'Implicit',
    'DEFAULT_ENV' => 'Mediu standard (ultima versiune phpBB)',
    'DEFAULT_LANG' => 'Limbă implicită',
    'DEFAULT_LANG_EXPLAIN' => 'Această limbă va fi folosită pentru forumurile create',
    'DELETE' => 'Şterge',
    'DELETE_FILES_IF_EXIST' => 'Şterge fişierele dacă există',
    'DIR_EXISTS' => 'Directorul %s există deja.',
    'DISABLED' => 'Dezactivat',
    'DROP_DB_IF_EXISTS' => 'Şterge baza de date dacă există deja',

		'EMAIL_DOMAIN' => 'Domeniu e-mail',
		'EMAIL_DOMAIN_EXPLAIN' => 'Domeniul e-mail folosit de către cei care testează. Adresa lor de email va fi tester_x@&lt;domain.com&gt;.',
		'EMAIL_ENABLE' => 'Activare email',
		'EMAIL_ENABLE_EXPLAIN' => 'Activează email-urile pe tot forumul. Pentru un forum local de test această setare ar fi &in mod normal dezactivată, trebuie activată doar dacă testaţi email-urile.',
		'ENABLED' => 'Activată',

		'GENERAL_ERROR' => 'Eroare generală',

		'IN_SETTINGS' => 'Administrare setări QuickInstall.',
   	'INCLUDE_MODS' => 'Include MOD-uri',
	'INCLUDE_MODS_EXPLAIN' => 'Select folders from the sources/mods/ folder in this list, those files will then be copied to your new board’s root dir, also overwriting old files (so you can have premodded boards in here for example). If you select “None”, it will not be used (because it’s a pain to deselect items).',
	'INSTALL_BOARD' => 'Instalează un forum',
	'INSTALL_QI' => 'Instalează QuickInstall',
	'IS_NOT_VALID' => 'Nu este valid.',

	'LICENSE' => 'Licenţă?',
	'LICENSE_EXPLAIN' => 'Aceste script este oferit sub termenii licenţei <a href="license.txt">GNU General Public versiunea 2</a>. Acest lucru se datoreaza faptului că foloseşte porţiuni mari de cod ale phpBB-ului care este oferit sub această licenţă şi necesită ca orice modificări să o folosească. De asemenea este o licenţă excelentă pentru că menţine gratuit un software gratuit :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Instalat de phpBB Quickinstall versiunea %s</strong>',
	'LOREM_IPSUM' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE' => 'Setează fişierele pentru scriere',
	'MAKE_WRITABLE_EXPLAIN' => 'Setează fişierele, config.php şi directoarele să poată fi scrise &in mod standard. Acest fapt poate schimbat c&and creaţi un forum.',
	'MANAGE_BOARDS' => 'Administrare forumuri',
	'MAX' => 'Max',
	'MIGHT_TAKE_LONG' => '<strong>Reţineţi:</strong> Crearea unui forum poate dura ceva timp poate chiar un minut sau mai mult aşa că <strong>nu</strong> trimiteţi acest formular de două ori.',
	'MIN' => 'Min',

	'NEED_EMAIL_DOMAIN' => 'Este nevoie de un domeniu e-mail pentru a crea utilizatorii de test',
	'NEED_WRITABLE' => 'QuickInstall necesit&ă ca forumurile şi directoarele cache să poată fi scrise &in orice moment.<br />Fişierul qi_config.php trebuie să poată fi scris doar la instalarea QuickInstall.',
	'NO' => 'Nu',
	'NO_ALT_ENV' => 'Mediul alternativ specificat nu există.',
	'NO_AUTOMOD' => 'AutoMOD nu a fost găsit &in directorul sources. Trebuie să descărcaţi AutoMOD şi să copiaţi directorul root &in directorul sources/automod, apoi redenumiţi root &in automod.',
	'NO_BOARDS' => 'Nu aveţi forumuri.',
	'NO_DB' => 'Nicio bază de date selectată.',
	'NO_IMPACT_WIN' => 'Această setare nu are niciun impact pe sistemele Windows mai vechi dec&at Win7.',
	'NO_MODULE' => 'Modulul %s nu a putut fi &incărcat.',
	'NO_PASSWORD' => 'Nicio parolă',
	'NO_DBPASSWD_ERR' => 'Aţi setat o parolă pentru baza de date dar nu aţi ales parola. <strong>Nu puteţi avea</strong> ambele variante',
	'NONE' => 'Niciuna',
	'NUM_CATS' => 'Numărul de categorii',
	'NUM_CATS_EXPLAIN' => 'Numărul categoriilor forumului ce trebuie create.',
	'NUM_FORUMS' => 'Numărul de forumuri',
	'NUM_FORUMS_EXPLAIN' => 'Numărul forumurilor ce trebuie create, acestea vor fi distribuite egal pe categoriile create.',
	'NUM_NEW_GROUP' => '&Inregistraţi recent',
	'NUM_NEW_GROUP_EXPLAIN' => 'Numărul utilizatorilor ce vor fi incluşi &in grupul celor &inregistraţi recent.<br />Dacă acest număr este mai mare dec&at numărul tuturor utilizatorilor atunci toţi utilizatorii noi vor fi incluşi &in grupul celor &inregistraţi recent.',
	'NUM_REPLIES' => 'Numărul de răspunsuri',
  'NUM_REPLIES_EXPLAIN' => 'Numărul de răspunsuri. Fiecare subiect va primi un număr aleator între aceste valori maxime şi minime de răspunsuri.',
  'NUM_TOPICS' => 'Numărul de subiecte',
  'NUM_TOPICS_EXPLAIN' => 'Numărul de subiecte, care vor fi create în fiecare forum. Fiecare forum va primi un număr aleatoriu de subiecte între aceste valori maxime şi minime.',
  'NUM_USERS' => 'Numărul de utilizatori',
  'NUM_USERS_EXPLAIN' => 'Numărul de utilizatori cu care forumul nou va fi populat.<br />Aceştia vor primi numele de utilizator Tester_x (x poate lua valori de la 1 p&ană la num_utilizatori). Toţi vor primi parola "123456"',

  'ONLY_LOCAL' => 'Reţineţi: QuickInstall este destinat numai pentru a fi utilizat local.<br />Nu ar trebui să fie utilizat pe un server web accesibil prin internet.',
  'OPTIONS' => 'Opţiuni',
  'OPTIONS_ADVANCED' => 'Opţiuni avansate',

	'POPULATE' => 'Populează forum',
	'POPULATE_OPTIONS' => 'Opţiuni populare',
	'POPULATE_MAIN_EXPLAIN' => 'Utilizatori: tester x, Parola: 123456',
	'POPULATE_EXPLAIN' => 'Populează forumul cu numărul de utilizatori, forumuri, mesaje şi subiecte pe care l-aţi specificat mai jos. Reţineţi ca dacă vreţi mai mulţi utilizatori, forumuri, mesaje şi subiecte, acest fapt va avea impact asupra duratei de iniţializare a forumului.<br />Toate aceste setări pot fi schimbate c&and creaţi un forum.',

	'QI_ABOUT' => 'Despre',
	'QI_ABOUT_ABOUT' => 'Fratele cel mare te iubeşte şi vrea ca tu să fii fericit.',
	'QI_DST' => 'Activare timp de vară',
	'QI_DST_EXPLAIN' => 'Vreţi să activaţi timpul de vară?',
	'QI_LANG' => 'Limbă QuickInstall',
	'QI_LANG_EXPLAIN' => 'Limba pe care ar trebui să o folosească QuickInstall. Trebuie să fie un director cu acelaşi nume &in language/. Această limbă va fi folosită ca limbă standard pentru toate forumurile dumneavoastră dacă limba există &in directorul sources/phpBB3/language/.',
	'QI_MAIN' => 'Pagina principală',
	'QI_MAIN_ABOUT' => 'Instalează aici un forum.<br /><br />“Numele bazei de date a forumului” este singurul c&amp ce trebuie completat, toate celelalte vor fi completate cu valorile standard din fişierul <em>includes/qi_config.php</em>.<br /><br />Accesaţi “Opţiuni avansate” pentru mai multe setări.',
	'QI_MANAGE' => 'Administrare forumuri',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Fus orar',
	'QI_TZ_EXPLAIN' => 'Fusul orar. Va fi fusul orar standard pentru forumurile create: -1, 0, 1 etc.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Redirectare',
	'REDIRECT_EXPLAIN' => 'Setează redirectarea &in mod standard pentru forumurile noi. Această opţiune poate fi schimbată c&and creaţi un forum.',
	'REDIRECT_BOARD' => 'Redirectare la forumul nou',
	'REQUIRED' => 'este necesară',
	'RESET' => 'Resetare',

	'SELECT' => 'Selectare',
	'SETTINGS' => 'Setări',
	'SETTINGS_FAILURE' => 'Au apărut erori, verificaţi căsuţa de mai jos.',
	'SETTINGS_SUCCESS' => 'Setările proprii au fost salvate cu succes.',
	'SERVER_NAME' => 'Nume server',
	'SERVER_NAME_EXPLAIN' => 'Acesta ar trebui să fie &in mod normal localhost deoarece QuickInstall <strong>nu</strong> trebuie folosit pentru servere publice.',
	'SERVER_PORT' => 'Port server',
	'SERVER_PORT_EXPLAIN' => '&In mod normal 80.',
	'SITE_DESC' => 'Descriere site',
	'SITE_DESC_EXPLAIN' => 'Descrierea standard pentru forumurile proprii. Aceasta poate fi schimbată c&and sunt create forumurile.',
	'SITE_NAME' => 'Nume site',
	'SITE_NAME_EXPLAIN' => 'Numele standard al site-ului ce va fi folosit pentru forumurile proprii. Acesta poate fi schimbat c&and sunt create forumurile.',
	'SMTP_AUTH' => 'Metoda de autentificare pentru SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Folosită doar dacă un nume de utilizator/parolă au fost setate.',
	'SMTP_DELIVERY' => 'Foloseşte serverul SMTP pentru mesaje electronice',
	'SMTP_DELIVERY_EXPLAIN' => 'Selectaţi “Da” dacă doriţi sau trebuie să trimiteţi mesaje electronice prin intermediul unui anumit server &in loc de funcţia de mesagerie locală.',
	'SMTP_HOST' => 'Adresă server SMTP',
	'SMTP_HOST_EXPLAIN' => 'Adresa serverului SMTP pe care vreţi să-l folosiţi',
	'SMTP_PASS' => 'Parolă SMTP',
	'SMTP_PASS_EXPLAIN' => 'Specificaţi o parolă doar dacă serverul SMTP o cere.',
	'SMTP_PORT' => 'Port server SMTP',
	'SMTP_PORT_EXPLAIN' => 'Schimbaţi această valoare doar dacă serverul SMTP propriu este pe un port diferit.',
	'SMTP_USER' => 'Nume utilizator SMTP',
	'SMTP_USER_EXPLAIN' => 'Specificaţi un nume de utilizator doar dacă serverul SMTP o cere.',
	'STAR_MANDATORY' => '* = obligatoriu',
	'SUBMIT' => 'Trimite',
	'SUBSILVER' => 'Instalare Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Selectaţi dacă vreţi ca tema Subsilver2 să fie instalată şi vreţi să fie stilul standard. Această opţiune poate fi schimbată c&and creaţi un forum.',
	'SUCCESS' => 'Succes',

	'TABLE_PREFIX' => 'Prefix tabelă',
	'TABLE_PREFIX_EXPLAIN' => 'Prefixul tabelei ce va fi folosit pentru forumurile proprii. Puteţi schimba această valoare accesând opţiunile avansate c&and creaţi forumuri noi.',
	'TEST_CAT_NAME' => 'Testare categorie %d',
	'TEST_FORUM_NAME' => 'Testare forum %d',
	'TEST_POST_START' => 'Testare mesaj %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Testare subiect %d',

	'UNCHECK_ALL' => 'Deselectează tot',
	'UP_TO_DATE' => 'Fratele cel mare spune că totul este actualizat.',
	'UP_TO_DATE_NOT' => 'Fratele cel mare spune că aveţi nevoie de actualizări.',
	'UPDATE_CHECK_FAILED' => 'Verificarea versiunii a eşuat.',
	'UPDATE_TO' => '<a href="%1$s"> actualizat la versiunea %2$s.</a>',

	'YES' => 'Da',

	'VERSION_CHECK' => 'Verificare versiune',
	'VISIT_BOARD' => '<a href="%s">Accesare forum</a>',

	'WHAT' => 'Ce?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall este un script pentru a instala rapid phpBB. Destul de clar... ;-)',
	'WHO_ELSE' => 'Ce altceva?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Mulţumiri echipei phpBB, în special echipei de dezvoltare care a creat acest software de excepţie.',
		'Mulţumiri echipei de MOD-uri phpBB.com (în special Josh, aka “A_Jelly_Doughnut”) pentru AutoMOD.',
		'Mulţumiri lui Mike TUMS pentru logo!',
		'Mulţumiri pentru testerii beta!',
		'Mulţumiri comunităţii community incluzând phpBB.com, startrekguide.com şi phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Cine? Când?',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall a fost original creat de către Igor “eviL&lt;3” Wiedler în vara anului 2007. A fost parţial rescris de către acesta în martie 2008.<br />Din martie 2010, acest proiect este întreţinut de către Jari “Tumba25” Kanerva.',
	'WHY' => 'De ce?',
	'WHY_EXPLAIN' => 'Ca şi în phpBB2, dacă folosiţi multe MOD-uri (creaţi modificări), nu puteţi pune toate MOD-urile într-o singură instalare phpBB. Aşa că e mai bine să aveţi instalări separate. Acum problema este că este dificil să copiaţi fişierele şi să parcurgeţi procesul de instalare de fiecare dată. Pentru a expedia acest process, a apărut Quickinstall.',
));

?>