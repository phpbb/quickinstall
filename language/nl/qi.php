<?php
/**
*
* qi [Dutch]
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
	'ABOUT_QUICKINSTALL' => 'Over phpBB3 QuickInstall',
	'ADMIN_EMAIL' => 'E-mailadres beheerder',
	'ADMIN_EMAIL_EXPLAIN' => 'E-mailadres beheerder om te gebruiken op jouw forums',
	'ADMIN_NAME' => 'Gebruikersnaam beheerder',
	'ADMIN_NAME_EXPLAIN' => 'De standaard gebruikersnaam van de beheerder om te gebruiken op je forums. Dit kan veranderd worden als je forums gemaakt zijn.',
	'ADMIN_PASS' => 'Beheerder wachtwoord',
	'ADMIN_PASS_EXPLAIN' => 'Het standaard beheerder wachtwoord om te gebruiken op je forums. Dit kan veranderd worden als je forums gemaakt zijn.',
	'ALT_ENV' => 'Alternatieve omgeving',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Stel installeer AutoMOD in op standaard ja. Dit kan worden aangepast bij het aanmaken van een forum.',
	'AUTOMOD_INSTALL' => 'Installeer AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Ga terug naar de hoofdpagina</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Ga terug naar de beheerspagina</a>',
	'BOARD_CREATED' => 'Forum succesvol aangemaakt!',
	'BOARD_DBNAME' => 'Forum database en folder naam',
	'BOARD_DESC' => 'Forum omschrijving',
	'BOARD_EMAIL' => 'Forum e-mail',
	'BOARD_EMAIL_EXPLAIN' => 'Afzender e-mail voor jouw aangemaakte forums.',
	'BOARD_NAME' => 'Forum naam',
	'BOARDS_DELETED' => 'De forums zijn succesvol verwijderd.',
	'BOARDS_DELETED_TITLE' => 'Forums verwijderd',
	'BOARDS_DIR' => 'Forum folders',
	'BOARDS_DIR_EXPLAIN' => 'De folder waar je forums worden aangemaakt. PHP heeft de rechten nodig om in deze folder te schrijven.',
	'BOARDS_DIR_MISSING' => 'De folder %s bestaat niet of is niet beschrijfbaar.',
	'BOARDS_LIST' => 'Lijst van forums',
	'BOARDS_NOT_WRITABLE' => 'De forum folder is niet beschrijfbaar.',

	'CACHE_NOT_WRITABLE' => 'De cache folder is niet beschrijfbaar.',
	'CHANGELOG' => 'Changelog',
	'CHECK_ALL' => 'Selecteer alles',
	'CONFIG_EMPTY' => 'De config array was leeg. Dit is mogelijk een bug melding waard.',
	'CONFIG_NOT_WRITABLE' => 'Het qi_config.php bestand is niet beschrijfbaar.',
	'COOKIE_DOMAIN' => 'Cookie domein',
	'COOKIE_DOMAIN_EXPLAIN' => 'Dit zou over het algemeen localhost moeten zijn.',
	'COOKIE_SECURE' => 'Cookie beveiliging',
	'COOKIE_SECURE_EXPLAIN' => 'Als je server SSL heeft draaien schakel dit dan in, laat het dan uitgeschakeld. Door dit in te schakelen en geen SSL hebben draaien resulteert in fouten tijdens het doorsturen.',
	'CREATE_ADMIN' => 'Maak beheerder aan',
	'CREATE_ADMIN_EXPLAIN' => 'Selecteer ja als je één beheerder aangemaakt wil hebben, dit zal geen eigenaar worden. dit word tester_1.',
	'CREATE_MOD' => 'Maak moderator aan',
	'CREATE_MOD_EXPLAIN' => 'Selecteer ja als je één globale moderator wil aanmaken. Dit wordt tester_1 of tester_2 als er een beheerder is geselecteerd.',

	'DB_EXISTS' => 'De database %s bestaat al.',
	'DB_PREFIX' => 'Database prefix',
	'DB_PREFIX_EXPLAIN' => 'Dit wordt voor alle database namen toegevoegd, om te voorkomen dat er databases worden overschreven die niet gebruikt worden door QuickInstall.',
	'DBHOST' => 'Database server',
	'DBHOST_EXPLAIN' => 'Over het algemeen localhost.',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => 'Jouw database systeem. Als je niet zeker bent zet het op MySQL.',
	'DBPASSWD' => 'Database wachtwoord',
	'DBPASSWD_EXPLAIN' => 'Het wachtwoord voor jouw database gebruiker',
	'DBPORT' => 'Database poort',
	'DBPORT_EXPLAIN' => 'Kan meestal leeg gelaten blijven.',
	'DBUSER' => 'Database gebruiker',
	'DBUSER_EXPLAIN' => 'Jouw database gebruiker. Dit moet een gebruiker zijn die de rechten heeft om een nieuwe database aan te maken.',
	'DEFAULT' => 'standaard',
	'DEFAULT_ENV' => 'Standaard omgeving (meeste recente phpBB)',
	'DEFAULT_LANG' => 'Standaard taal',
	'DEFAULT_LANG_EXPLAIN' => 'Deze taal zal worden gebuikt voor de aangemaakte forums.',
	'DELETE' => 'Verwijder',
	'DELETE_FILES_IF_EXIST' => 'Verwijder bestanden als ze bestaan',
	'DIR_EXISTS' => 'De folder %s bestaat al.',
	'DISABLED' => 'Uitgeschakeld',
	'DROP_DB_IF_EXISTS' => 'Verwijder database als deze bestaat',

	'EMAIL_DOMAIN' => 'E-mail domein',
	'EMAIL_DOMAIN_EXPLAIN' => 'Het e-mail domein om te gebruiken voor de testers. Hun e-mail zal worden tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE' => 'E-mail inschakelen',
	'EMAIL_ENABLE_EXPLAIN' => 'Forum breed e-mails inschakelen. Voor een lokaal test forum zal het normaalgesproken uit staan, tenzij je de e-mails wil testen.',
	'ENABLED' => 'Ingeschakeld',

	'GENERAL_ERROR' => 'Algemene fout',

	'IN_SETTINGS' => 'Beheer je QuickInstall instellingen.',
	'INCLUDE_MODS' => 'MODs Invoegen',
	'INCLUDE_MODS_EXPLAIN' => 'Selecteer folders van de sources/mods/ folder in de lijst, deze bestanden worden dan gekopieerd naar jouw nieuwe forum root dir, daarnaast worden ook oude bestanden overschreven(Dus je kunt ook premodded forums hierin hebben bijvoorbeeld). Als je “Geen” selecteert zal het niet worden gebruikt (omdat het vervelend is om items te deselecteren).',
	'INSTALL_BOARD' => 'Installeer een forum',
	'INSTALL_QI' => 'Installeer QuickInstall',
	'IS_NOT_VALID' => 'Is niet geldig.',

	'LICENSE' => 'Licentie?',
	'LICENSE_EXPLAIN' => 'Dit script is uitgegeven onder de voorwaarde van de <a href="license.txt">GNU General Public License version 2</a>. Dit is voornamelijk, omdat het gebruik maakt van grote delen van phpBB’s code, welke ook is uitgebracht onder deze licentie en vereist dat elke aanpassing hiervan het ook gebruikt. Maar ook omdat het een geweldige licentie is die gratis software gratis houd :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Geïnstalleerd met phpBB Quickinstall versie %s</strong>',

	'MAKE_WRITABLE' => 'Maak bestanden volledig beschrijfbaar',
	'MAKE_WRITABLE_EXPLAIN' => 'Stel bestanden, config.php en folders standaard in op volledig beschrijfbaar. Dit kan worden aangepast als je een forum aanmaakt.',
	'MANAGE_BOARDS' => 'Beheer forums',
	'MAX' => 'Max',
	'MIGHT_TAKE_LONG' => '<strong>LET OP:</strong> Aanmaken van een forum kan even duren, mogelijk een minuut of langer, dus bevestig het formulier <strong>niet</strong> tweemaal.',
	'MIN' => 'Min',

	'NEED_EMAIL_DOMAIN' => 'Een e-maildomein is nodig om testgebruikers aan te maken',
	'NEED_WRITABLE' => 'QuickInstall moet de forum en cache folders ten alle tijde beschrijfbaar hebben.<br />De qi_config.php moet alleen beschrijfbaar zijn voor de installatie van QuickInstall.',
	'NO' => 'Nee',
	'NO_ALT_ENV' => 'De opgegeven alternatieve omgeving bestaat niet.',
	'NO_AUTOMOD' => 'Automod is niet gevonden in de sources folder. Je moet AutoMOD downloaden en de root folder kopiëren naar sources/automod, daarna hernoem je root naar automod.',
	'NO_BOARDS' => 'Je hebt geen forums.',
	'NO_DB' => 'Geen database geselecteerd.',
	'NO_IMPACT_WIN' => 'Deze instelling heeft geen impact op Windows systemen ouder dan Windows 7.',
	'NO_MODULE' => 'De module %s kan niet worden geladen.',
	'NO_PASSWORD' => 'Geen wachtwoord',
	'NO_DBPASSWD_ERR' => 'Je hebt een wachtwoord ingesteld en geen wachtwoord geselecteerd. Je kan niet beiden <strong>hebben</strong> en <strong>Niet hebben</strong> een wachtwoord',
	'NONE' => 'Geen',
	'NUM_CATS' => 'Aantal categorieën',
	'NUM_CATS_EXPLAIN' => 'Het aantal forum categorieën om aan te maken.',
	'NUM_FORUMS' => 'Aantal forums',
	'NUM_FORUMS_EXPLAIN' => 'Het aantal forums om aan te maken, ze worden gelijkmatig verspreid over de categorieën.',
	'NUM_NEW_GROUP' => 'Nieuw geregistreerd',
	'NUM_NEW_GROUP_EXPLAIN' => 'Het aantal gebruikers om in de groep nieuw geregistreerde gebruikers te plaatsen.<br />Als dit aantal groter is dan het aantal gebruikers, dan zullen alle nieuwe gebruikers komen in de nieuw geregistreerde gebruikers groep.',
	'NUM_REPLIES' => 'Aantal reacties',
	'NUM_REPLIES_EXPLAIN' => 'Aantal reacties. Elk onderwerp krijgt een willekeurig aantal tussen deze maximale en minimale waarde van reacties.',
	'NUM_TOPICS' => 'Aantal onderwerpen',
	'NUM_TOPICS_EXPLAIN' => 'Het aantal onderwerpen om aan te maken in elk forum. Elk forum krijgt een willekeurig aantal tussen deze maximale en minimale waarde.',
	'NUM_USERS' => 'Aantal gebruikers',
	'NUM_USERS_EXPLAIN' => 'Het aantal gebruikers waarmee je forum wordt bevolkt.<br />Ze krijgen de gebruikersnaam Tester_x (x is 1 tot num_users). Ze krijgen allemaal het wachtwoord "123456"',

	'ONLY_LOCAL' => 'Let op: QuickInstall is enkel bedoelt om lokaal te gebruiken.<br />Het moet niet gebruikt worden op een webserver die te benaderen is via het internet.',
	'OPTIONS' => 'Opties',
	'OPTIONS_ADVANCED' => 'Geavanceerde opties',

	'POPULATE' => 'Bevolk forum',
	'POPULATE_OPTIONS' => 'Bevolkingsopties',
	'POPULATE_MAIN_EXPLAIN' => 'Gebruikers: tester x, Wachtwoord: 123456',
	'POPULATE_EXPLAIN' => 'Bevolkt het forum met het aantal gebruikers, forums, berichten en onderwerpen die je hieronder opgeeft. Let er op hoe meergebruiker, forums, berichten en onderwerpen je wilt, het langer duurt om je forum aan te maken.<br />Al deze instellingen kunnen worden veranderd als je een forum aanmaakt.',

	'QI_ABOUT' => 'Over',
	'QI_ABOUT_ABOUT' => 'Big brother houd van je en wil dat je gelukkig bent.',
	'QI_DST' => 'Zomertijd',
	'QI_DST_EXPLAIN' => 'Wil je Zomertijd aan of uit hebben staan?',
	'QI_LANG' => 'QuickInstall taal',
	'QI_LANG_EXPLAIN' => 'De taal die QuickInstall moet gebruiken. Er moet een folder met de naam staan in language/. Deze taal zal ook gebruikt worden als standaard taal voor je forums als deze taal bestaat in sources/phpBB3/language/.',
	'QI_MAIN' => 'Hoofdpagina',
	'QI_MAIN_ABOUT' => 'Installeer een nieuw forum hier.<br /><br />“Forum database naam” is het enige veld dat je moet invullen, de rest wordt ingevuld met de standaard waardes van <em>includes/qi_config.php</em>.<br /><br />Druk op “Geavanceerde opties” voor meer instellingen.',
	'QI_MANAGE' => 'Beheer forums',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Tijdzone',
	'QI_TZ_EXPLAIN' => 'Jouw tijdzone. Het wordt de standaard tijdzone van aan te maken forums. -1, 0, 1 etc.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Doorsturen',
	'REDIRECT_EXPLAIN' => 'Standaard staat doorsturen naar nieuwe forums standaard ingesteld op ja. Dit kan worden aangepast bij het aanmaken van een forum.',
	'REDIRECT_BOARD' => 'Naar nieuwe forum doorsturen',
	'REQUIRED' => 'is vereist',
	'RESET' => 'Reset',

	'SELECT' => 'Selecteer',
	'SETTINGS' => 'Instellingen',
	'SETTINGS_FAILURE' => 'Er waren fouten, kijk in de onderstaande box.',
	'SETTINGS_SUCCESS' => 'Je instellingen zijn succesvol opgeslagen.',
	'SERVER_NAME' => 'Server naam',
	'SERVER_NAME_EXPLAIN' => 'Dit zou gewoonlijk localhost moeten zijn aangezien QuickInstall <strong>niet</strong> bedoelt is voor publieke servers.',
	'SERVER_PORT' => 'Server poort',
	'SERVER_PORT_EXPLAIN' => 'Standaard 80.',
	'SITE_DESC' => 'Omschrijving van de site',
	'SITE_DESC_EXPLAIN' => 'De standaard omschrijving voor jouw forum(s). Dit kan worden aangepast als je forums aanmaakt.',
	'SITE_NAME' => 'Naam van de site',
	'SITE_NAME_EXPLAIN' => 'De standaard site naam die wordt gebruikt voor jouw forums. Dit kan worden aangepast als je forums aanmaakt.',
	'SMTP_AUTH' => 'Verificatiemethode voor SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Alleen gebruikt als gebruikersnaam/wachtwoord is opgegeven.',
	'SMTP_DELIVERY' => 'Gebruik SMTP-server voor e-mail',
	'SMTP_DELIVERY_EXPLAIN' => 'Selecteer “Ja” als je e-mails via de opgegeven SMTP-server in plaats van met de lokale mailfunctie wilt of moet versturen.',
	'SMTP_HOST' => 'SMTP-serveradres',
	'SMTP_HOST_EXPLAIN' => 'Het adres van de SMTP-server die je wil gebruiken',
	'SMTP_PASS' => 'SMTP-wachtwoord',
	'SMTP_PASS_EXPLAIN' => 'Voer alleen een wachtwoord in als je SMTP-server dit vereist.',
	'SMTP_PORT' => 'SMTP-serverpoort',
	'SMTP_PORT_EXPLAIN' => 'Wijzig dit alleen als jouw SMTP-server gebruik maakt van een andere poort.',
	'SMTP_USER' => 'SMTP-gebruikersnaam',
	'SMTP_USER_EXPLAIN' => 'Voer alleen een gebruikersnaam in als je SMTP-server dit vereist.',
	'STAR_MANDATORY' => '* = Verplicht',
	'SUBMIT' => 'Verzenden',
	'SUBSILVER' => 'Installeer Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Als je wil selecteer je het Subsilver2 thema om te installeren, ook als je deze als standaard stijl wilt. Dit kan worden aangepast als je een forum aanmaakt.',
	'SUCCESS' => 'Succes',

	'TABLE_PREFIX' => 'Tabel prefix',
	'TABLE_PREFIX_EXPLAIN' => 'De tabel prefix dat word gebruikt voor je forums. Je kan dit aanpassen in de geavanceerde opties als je een nieuw forum aanmaakt.',
	'TEST_CAT_NAME' => 'Test categorie %d',
	'TEST_FORUM_NAME' => 'Test forum %d',
	'TEST_POST_START' => 'Test bericht %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Test onderwerp %d',

	'UNCHECK_ALL' => 'Deselecteer alles',
	'UP_TO_DATE' => 'Big brother zegt dat je up-to-date bent.',
	'UP_TO_DATE_NOT' => 'Big brother zegt dat je niet up-to-date bent.',
	'UPDATE_CHECK_FAILED' => 'Big brother’s versie controle is mislukt.',
	'UPDATE_TO' => '<a href="%1$s">Updaten naar versie %2$s.</a>',

	'YES' => 'Ja',

	'VERSION_CHECK' => 'Big brother versie controle',
	'VISIT_BOARD' => '<a href="%s">Bezoek het forum</a>',

	'WHAT' => 'Wat?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall is een script om snel phpBB te installeren. Vrij duidelijk... ;-)',
	'WHO_ELSE' => 'Wie anders?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Credits gaan naar de phpBB team, met name het ontwikkeling team dat zo een prachtig stukje software heeft gemaakt.',
		'Dank aan het phpBB.com MOD team (met name Josh, aka “A_Jelly_Doughnut”) voor AutoMOD.',
		'Dank aan Mike TUMS voor het mooie logo!',
		'Dank aan de bèta testers!',
		'Dank aan de phpBB community inclusief phpBB.com, startrekguide.com en phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Wie? Wanneer?',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall is origineel gemaakt door Igor “eviL&lt;3” Wiedler in de zomer van 2007. Het is deels herschreven door hem in maart 2008.<br />Sinds maart 2010 wordt dit project onderhouden door Jari “Tumba25” Kanerva.',
	'WHY' => 'Waarom?',
	'WHY_EXPLAIN' => 'Net als met phpBB2, als je een hoop modding doet (modificaties maken), kun je niet alle MODs in een enkele phpBB installatie maken. Het is het beste om aparte installaties te hebben. Nu is het probleem om telkens alle bestanden te moeten kopiëren en door de installatie procedure heen te gaan. Om dit proces sneller te laten verlopen is quickinstall geboren.',
));

?>