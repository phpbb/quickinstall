<?php
/**
*
* qi [Danish]
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
	'ABOUT_QUICKINSTALL' => 'Om phpBB3 QuickInstall',
	'ADMIN_EMAIL' => 'Administrators email',
	'ADMIN_EMAIL_EXPLAIN' => 'Den admin-email som anvendes på dine boards',
	'ADMIN_NAME' => 'Administrators brugernavn',
	'ADMIN_NAME_EXPLAIN' => 'Standardadministrator på dine boards. Kan ændres individuelt for hvert board når det oprettes.',
	'ADMIN_PASS' => 'Administrators kodeord',
	'ADMIN_PASS_EXPLAIN' => 'Administrators kodeord på alle dine boards. Kan ændres invividuelt for hvert board når det oprettes.',
	'ALT_ENV' => 'Alternativ installation',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Vælg at installere AutoMod som standard. Installationen kan fravælges når nyt board oprettes.',
	'AUTOMOD_INSTALL' => 'Installer AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Tilbage til hovedside</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Tilbage til ACP</a>',
	'BOARD_CREATED' => 'Board oprettet!',
	'BOARD_DBNAME' => 'Boardets database og mappenavn',
	'BOARD_DESC' => 'Boardbeskrivelse',
	'BOARD_EMAIL' => 'Board email',
	'BOARD_EMAIL_EXPLAIN' => 'Afsenderemail for dine oprettede board.',
	'BOARD_NAME' => 'Boardnavn',
	'BOARDS_DELETED' => 'Boards blev slettet.',
	'BOARDS_DELETED_TITLE' => 'Slettede boards',
	'BOARDS_DIR' => 'Mappe til boards',
	'BOARDS_DIR_EXPLAIN' => 'Den mappe hvor dine boards vil blive oprettet. PHP behøver skrivetilladelser til denne.',
	'BOARDS_LIST' => 'Liste over boards',
	'BOARDS_NOT_WRITABLE' => 'Board-mappen er ikke skrivbar.',

	'CACHE_NOT_WRITABLE' => 'Cache-mappen er ikke skrivbar.',
	'CHANGELOG' => 'Ændringlog',
	'CHECK_ALL' => 'Kontroller alle',
	'CONFIG_EMPTY' => 'config array var tom. Problemet er formentlig værd at indlevere en bugrapport om.',
	'CONFIG_NOT_WRITABLE' => 'Filen qi_config.php er ikke skrivbar.',
	'COOKIE_DOMAIN' => 'Cookiedomæne',
	'COOKIE_DOMAIN_EXPLAIN' => 'Skal typisk være localhost.',
	'COOKIE_SECURE' => 'Sikker cookie',
	'COOKIE_SECURE_EXPLAIN' => 'Sættes kun aktiv hvis din server kommunikerer via SSL. Sættes indstillingen aktiv på et domæne, der ikke kører SSL, vil det resultere i serverfejl ved viderestillinger.',
	'CREATE_ADMIN' => 'Opret administrator',
	'CREATE_ADMIN_EXPLAIN' => 'Vælg ja, hvis du ønsker at oprette en administrator, som ikke er grundlægger. Brugernavn vil være tester_1.',
	'CREATE_MOD' => 'Opret redaktør',
	'CREATE_MOD_EXPLAIN' => 'Vælg ja, hvis du ønsker at oprette en  global redaktør. Brugernavn vil være tester_1, eller tester_2 hvis du også har valgt at oprette en administrator.',

	'DB_EXISTS' => 'Databasen %s eksisterer allerede.',
	'DB_PREFIX' => 'Databasepræfiks',
	'DB_PREFIX_EXPLAIN' => 'Indsættes foran alle databasenavne for at undgå at overskrive databaser som ikke benyttes af QuickInstall.',
	'DBHOST' => 'Databaseserver',
	'DBHOST_EXPLAIN' => 'Normalt localhost.',
	'DBMS' => 'DBMS',
	'DBMS_EXPLAIN' => 'Dit databasesystem. Er du usikker, vælges MySQL.',
	'DBPASSWD' => 'Kodeord til database',
	'DBPASSWD_EXPLAIN' => 'Databasebrugers kodeord',
	'DBPORT' => 'Databaseport',
	'DBPORT_EXPLAIN' => 'Skal normalt ikke udfyldes.',
	'DBUSER' => 'Databasebruger',
	'DBUSER_EXPLAIN' => 'Denne bruger skal have tilladelser til at oprette nye databaser på serveren.',
	'DEFAULT' => 'standard',
	'DEFAULT_ENV' => 'Standardinstallation (seneste phpBB)',
	'DEFAULT_LANG' => 'Standardsprog',
	'DEFAULT_LANG_EXPLAIN' => 'Dette sprog vil blive anvendt i de oprettede boards.',
	'DELETE' => 'Slet',
	'DELETE_FILES_IF_EXIST' => 'Slet filer, hvis de eksisterer',
	'DIR_EXISTS' => 'Mappen %s eksisterer allerede.',
	'DISABLED' => 'Inaktiv',
	'DROP_DB_IF_EXISTS' => 'Slet database hvis den eksisterer',

	'EMAIL_DOMAIN' => 'Emaildomæne',
	'EMAIL_DOMAIN_EXPLAIN' => 'Det emaildomæne der anvendes af testere. Deres emailadresse vil være tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE' => 'Aktiver email',
	'EMAIL_ENABLE_EXPLAIN' => 'Aktiver emails for hele boardet. For et lokalt testboard vil indstillingen normalt være inaktiv, med mindre du vil teste emails.',
	'ENABLED' => 'Aktiveret',

	'GENERAL_ERROR' => 'Generel Fejl',

	'IN_SETTINGS' => 'Indstillinger for QuickInstall.',
	'INCLUDE_MODS' => 'Inkluder MODs',
	'INCLUDE_MODS_EXPLAIN' => 'Vælg mapper fra sources/mods/-mappen i denne liste. Disse filer vil blive kopieret til dit nye boards rod, hvorved eksisterende og gamle filer overskrives (du kan altså have  premodded boards her for eksempel). Vælger du “Ingen”, kopieres intet (fordi det er besværligt at fravælge elementer).',
	'INSTALL_BOARD' => 'Installer et board',
	'INSTALL_QI' => 'Installer QuickInstall',
	'IS_NOT_VALID' => 'Det er ikke gyldigt.',

	'LICENSE' => 'License?',
	'LICENSE_EXPLAIN' => 'Dette script er frigivet under betingelserne i <a href="license.txt">GNU General Public License version 2</a>. Primært fordi der anvendes en stor del af koden fra phpBB, som også er frigivet under denne license. Men også for det er en fin licens, som gør at softwaren er gratis :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installeret med phpBB Quickinstall version %s</strong>',

	'MAKE_WRITABLE' => 'Gør filer skrivbare',
	'MAKE_WRITABLE_EXPLAIN' => 'Indstiller filer, config.php og mapper til som standard at være skrivbare for alle. Indstillingen kan ændres når du opretter et nyt board.',
	'MANAGE_BOARDS' => 'Administrer boards',
	'MAX' => 'Max',
	'MIGHT_TAKE_LONG' => '<strong>Bemærk at</strong> oprettelse af boardet kan tage nogen tid, op til et minut eller mere. Så klik <strong>ikke</strong> på udfør flere gange.',
	'MIN' => 'Min',

	'NEED_EMAIL_DOMAIN' => 'Der behøves et email domæne for at oprette testbrugere',
	'NEED_WRITABLE' => 'For at QuickInstall skal fungerer skal board- og cache-mapper være skrivbare.<br />Filen qi_config.php behøver kun at være skrivbar under installationen af QuickInstall.',
	'NO' => 'Nej',
	'NO_ALT_ENV' => 'Angivne alternative installation eksisterer ikke.',
	'NO_AUTOMOD' => 'AutoMOD er ikke tilstede i sources-mappen. Du skal downloade AutoMOD og koipere rod-mappen til sources/automod, og omdøbe root ti automod.',
	'NO_BOARDS' => 'Ingen boards installeret.',
	'NO_DB' => 'Ingen database valgt.',
	'NO_IMPACT_WIN' => 'Indstillingen har ingen indflydelse på Windows-systemer ældre end Win7.',
	'NO_MODULE' => 'Modulet %s kunne ikke loades.',
	'NO_PASSWORD' => 'Intet kodeord',
	'NO_DBPASSWD_ERR' => 'Du har angivet et databasekodeord og valgt intet kodeord. Du kan ikke både <strong>have</strong> og <strong>ikke have</strong> et kodeord',
	'NONE' => 'Ingen',
	'NUM_CATS' => 'Antal kategorier',
	'NUM_CATS_EXPLAIN' => 'Det antal boardkategorier der skal oprettes.',
	'NUM_FORUMS' => 'Antal fora',
	'NUM_FORUMS_EXPLAIN' => 'Det antal fora der skal oprettes. Det fordeles ligeligt i de oprettede kategorier.',
	'NUM_NEW_GROUP' => 'Nye brugere',
	'NUM_NEW_GROUP_EXPLAIN' => 'Det antal brugere der skal tilknyttes gruppen Nye brugere.<br />Er antallet højere end det samlede antal brugere, tilknyttes alle brugere denne gruppe.',
	'NUM_REPLIES' => 'Antal svar',
	'NUM_REPLIES_EXPLAIN' => 'Antallet af besvarelser. Hvert emne modtager et vilkårligt antal indenfor angivne værdier af min og max.',
	'NUM_TOPICS' => 'Antal emner',
	'NUM_TOPICS_EXPLAIN' => 'Antallet af emner i hvert forum. Der oprette et vilkårligt antal emner indenfor angivne værdier af min og max.',
	'NUM_USERS' => 'Antal brugere',
	'NUM_USERS_EXPLAIN' => 'Det antal brugere der skal befolke dit nye board.<br />Brugere tildeles brugernavnet Tester_x (hvor x er lig med num_users). Alle tildeles kodeordet "123456"',

	'ONLY_LOCAL' => 'Bemærk at QuickInstall kun er udviklet til lokalt brug.<br />Quickinstall bør ikke installeres og anvendes på en server tilgængelig via Internet.',
	'OPTIONS' => 'Indstillinger',
	'OPTIONS_ADVANCED' => 'Avancerede instillinger',

	'POPULATE' => 'Befolk dit board',
	'POPULATE_OPTIONS' => 'Muligheder for udfyldning',
	'POPULATE_MAIN_EXPLAIN' => 'Brugere: tester x, Password: 123456',
	'POPULATE_EXPLAIN' => 'Udfylder boardet med det antal brugere, fora, emner og indlæg du angiver herunder. Bemærk at desto flere brugere, fora, emner og indlæg du angiver, jo længere tid vil oprettelse tage.<br />Alle disse indstillinger kan ændres når et board oprettes.',

	'QI_ABOUT' => 'Om',
	'QI_ABOUT_ABOUT' => 'Big brother holder af dig og ønsker du skal være glad.',
	'QI_DST' => 'Sommertid',
	'QI_DST_EXPLAIN' => 'Bestem om sommertid skal være aktiv.',
	'QI_LANG' => 'QuickInstall sprog',
	'QI_LANG_EXPLAIN' => 'Sprogvalg for QuickInstall. Der skal være en mappe med det navn tilstede i language/. Sprogindstillingen vil også blive anvendt som standardsprog for dine boards, hvis sprogpakken er tilstede i sources/phpBB3/language/.',
	'QI_MAIN' => 'Hovedside',
	'QI_MAIN_ABOUT' => 'Installer et nyt board her.<br /><br />“Boardets databasennavn” er det eneste felt du behøver at udfylde. Øvrige felter udfyldes med standardværdier, som hentes i filen <em>includes/qi_config.php</em>.<br /><br />Klik på “Avancerde instillinger” for flere muligheder.',
	'QI_MANAGE' => 'Administration af boards',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Tidszone',
	'QI_TZ_EXPLAIN' => 'Indstillingen vil gælde for oprettede boards. Angiv -1, 0, 1 osv.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Viderestil',
	'REDIRECT_EXPLAIN' => 'Valg ja, for at viderestille til nye boards som standard. Kan fravælges når nye boards oprettes.',
	'REDIRECT_BOARD' => 'Viderestil til nyt board',
	'REQUIRED' => 'krævet',
	'RESET' => 'Nulstil',

	'SELECT' => 'Vælg',
	'SETTINGS' => 'Indstillinger',
	'SETTINGS_FAILURE' => 'Der opstod fejl, se i boksen herunder.',
	'SETTINGS_SUCCESS' => 'Dine indstillinger blev gemt.',
	'SERVER_NAME' => 'Servernavn',
	'SERVER_NAME_EXPLAIN' => 'Vil typisk være localhost, da QuickInstall <strong>ikke</strong> er beregnet for offentlige servere.',
	'SERVER_PORT' => 'Serverport',
	'SERVER_PORT_EXPLAIN' => 'Normalt 80.',
	'SITE_DESC' => 'Boardbeskrivelse',
	'SITE_DESC_EXPLAIN' => 'Standardbeskrivelse for dine boards. Kan ændres når boards oprettes.',
	'SITE_NAME' => 'Sidenavn',
	'SITE_NAME_EXPLAIN' => 'Standardnavn for dine boards. Kan ændres når boards oprettes.',
	'SMTP_AUTH' => 'Autentifikationsmetode for SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Anvendes kun hvis brugernavn og kodeord er angivet.',
	'SMTP_DELIVERY' => 'Anvend SMTP-server til email',
	'SMTP_DELIVERY_EXPLAIN' => 'Vælg “Ja”, hvis du ønsker eller har brug for at sende email via en navngiven server, i stedet for den lokale mailfunktion.',
	'SMTP_HOST' => 'Adresse på SMTP-server',
	'SMTP_HOST_EXPLAIN' => 'Adressen på den server du ønsker at anvende',
	'SMTP_PASS' => 'SMTP-kodeord',
	'SMTP_PASS_EXPLAIN' => 'Angiv kun et kodeord, hvis SMTP-serveren kræver det.',
	'SMTP_PORT' => 'SMTP-serverport',
	'SMTP_PORT_EXPLAIN' => 'Skift kun denne, hvis du ved, at din SMTP-server benytter en anden port.',
	'SMTP_USER' => 'SMTP-brugernavn',
	'SMTP_USER_EXPLAIN' => 'Angiv kun et brugernavn, hvis din SMTP-server kræver det.',
	'STAR_MANDATORY' => '* = obligatorisk',
	'SUBMIT' => 'Udfør',
	'SUBSILVER' => 'Installer Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Bestem om Subsilver2 skal installeres og være standardtypografi. Det kan ændres når du opretter et board.',
	'SUCCESS' => 'Succes',

	'TABLE_PREFIX' => 'Tabelpræfiks',
	'TABLE_PREFIX_EXPLAIN' => 'Det tabelpræfiks som skal anvendes for dine boards. Du kan ændre dette under avancerede indstillinger, når nye boards oprettes.',
	'TEST_CAT_NAME' => 'Testkategori %d',
	'TEST_FORUM_NAME' => 'Testforum %d',
	'TEST_POST_START' => 'Testindlæg %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Testemne %d',

	'UNCHECK_ALL' => 'Fravælg alle',
	'UP_TO_DATE' => 'Big brother fortæller at du er up to date.',
	'UP_TO_DATE_NOT' => 'Big brother fortæller at du ikke er up to date.',
	'UPDATE_CHECK_FAILED' => 'Big brother’s versionkontrol fejlede.',
	'UPDATE_TO' => '<a href="%1$s">Updater til version %2$s.</a>',

	'YES' => 'Ja',

	'VERSION_CHECK' => 'Big brother versionkontrol',
	'VISIT_BOARD' => '<a href="%s">Besøg boardet</a>',

	'WHAT' => 'Hvad?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall er et script beregnet til hurtig installallation af phpBB. Ret indlysende.. ;-)',
	'WHO_ELSE' => 'Hvem ellers?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Credits til phpBB-teamet, specielt udviklerteamet, som skabte sådan et vidunderligt stykke software.',
		'Tak til phpBB.com MOD team (specielt Josh, aka “A_Jelly_Doughnut”) for AutoMOD, som er inkluderet i denne pakke.',
		'Tak til Mike TUMS for det flotte logo!',
		'Tak til betatesterne!',
		'Tak til phpBB-communitiet, phpBB.com, startrekguide.com og phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Hvem? Hvornår?',
	'WHO_WHEN_EXPLAIN' => 'phpBB3 QuickInstall blev udviklet af Igor “eviL&lt;3” Wiedler i sommeren 2007. Igor omskrev softwaren delvist i marts 2008.<br />Siden marts 2010 vedligeholdes projektet af Jari “Tumba25” Kanerva.',
	'WHY' => 'Hvorfor?',
	'WHY_EXPLAIN' => 'Hvis du eksperimenterer meget med modding (eller udvikler Mods), er det, som i phpBB2, ikke praktisk at indeholde alle MODs i en enkelt phpBB-installation. Det giver det bedste overblik at have separate installationer til hvert MOD. Det er imidlertid tidskrævende og for besværligt at skulle hele operationen igennem med filkopiering og installation for hvert eksperiment. QuickInstall blev skabt for at forenkle denne process.',
));

?>