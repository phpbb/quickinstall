<?php
/**
*
* qi [french]
* translated by PhpBB-fr.com <http://www.phpbb-fr.com/>
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2007, 2008 eviL3
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
	'ABOUT_QUICKINSTALL' => 'A propos de phpBB3 QuickInstall',
	'ADMIN_EMAIL' => 'Adresse e-mail de l’administrateur',
	'ADMIN_EMAIL_EXPLAIN' => 'Adresse e-mail de l’administrateur à utiliser pour vos forums',
	'ADMIN_NAME' => 'Nom d’utilisateur de l’administrateur',
	'ADMIN_NAME_EXPLAIN' => 'Le nom d’utilisateur par défaut à utiliser sur vos forums. Celui-ci peut être modifié une fois que les forums sont créés.',
	'ADMIN_PASS' => 'Mot de passe administrateur',
	'ADMIN_PASS_EXPLAIN' => 'Le mot de passe administrateur par défaut à utiliser sur vos forums. Celui-ci peut être modifié une fois que les forums sont créés.',
	'ALT_ENV' => 'Autre environnement',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Définir l’installation d’AutoMOD à oui par défaut. Ceci peut être modifié lorsque vous créez un forum.',
	'AUTOMOD_INSTALL' => 'Installation d’AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Retourner à la page principale</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Retourner à la page d’administration</a>',
	'BOARD_CREATED' => 'Forum créé!',
	'BOARD_DBNAME' => 'Base de données du forum et nom du répertoire',
	'BOARD_DESC' => 'Description du forum',
	'BOARD_EMAIL' => 'Adresse e-mail du forum',
	'BOARD_EMAIL_EXPLAIN' => 'Adresse e-mail de l’expéditeur pour vos forums.',
	'BOARD_NAME' => 'Nom du forum',
	'BOARDS_DELETED' => 'Les forums ont été supprimés.',
	'BOARDS_DELETED_TITLE' => 'Forums supprimés',
	'BOARDS_DIR' => 'Répertoire des forums',
	'BOARDS_DIR_EXPLAIN' => 'Le répertoire où vos forums seront créés. PHP doit avoir les permissions d’écriture dans ce répertoire.',
	'BOARDS_LIST' => 'Liste des forums',
	'BOARDS_NOT_WRITABLE' => 'Le répertoire des forums n’est pas accessible en écriture.',

	'CACHE_NOT_WRITABLE' => 'Le répertoire de cache n’est pas accessible en écriture.',
	'CHANGELOG' => 'Changelog',
	'CHECK_ALL' => 'Cocher tout',
	'CONFIG_EMPTY' => 'Le tableau de configuration était vide. Cela mérite bien un rapport d’erreur.',
	'CONFIG_NOT_WRITABLE' => 'Le fichier qi_config.php n’est pas accessible en écriture.',
	'COOKIE_DOMAIN' => 'Domaine du cookie',
	'COOKIE_DOMAIN_EXPLAIN' => 'Ceci devrait normalement être localhost.',
	'COOKIE_SECURE' => 'Cookie sécurisé',
	'COOKIE_SECURE_EXPLAIN' => 'Si votre serveur fonctionne par l’intermédiaire du protocole SSL, activez cette option sinon laissez-la désactivée. Si vous activez cette option alors que votre serveur ne fonctionne pas par l’intermédiaire du protocole SSL, des erreurs se produiront lors des redirections.',

	'DB_EXISTS' => 'The database %s already exists.',
	'DB_PREFIX' => 'Préfixe de base de données',
	'DB_PREFIX_EXPLAIN' => 'Ceci est ajouté avant tous les noms de base de données afin d’éviter de supprimer des bases de données qui ne sont pas utilisées par QuickInstall.',
	'DBHOST' => 'Serveur de base de données',
	'DBHOST_EXPLAIN' => 'Généralement localhost.',
	'DBMS' => 'SGBD',
	'DBMS_EXPLAIN' => 'Votre système de gestion de base de données. Si vous n’êtes pas certain, choisissez MySQL.',
	'DBPASSWD' => 'Mot de passe de la base de données',
	'DBPASSWD_EXPLAIN' => 'Le mot de passe utilisateur de votre base de données.',
	'DBPORT' => 'Port de la base de données',
	'DBPORT_EXPLAIN' => 'Peut généralement être laissé vide.',
	'DBUSER' => 'Utilisateur de la base de données',
	'DBUSER_EXPLAIN' => 'Votre utilisateur de la base de données. Ceci doit être un utilisateur ayant les permissions de créer de nouvelles bases de données.',
	'DEFAULT' => 'Par défaut',
	'DEFAULT_ENV' => 'Environnement par défaut (dernier phpBB)',
	'DEFAULT_LANG' => 'Langue par défaut',
	'DEFAULT_LANG_EXPLAIN' => 'Cette langue sera utilisée pour les forums créés.',
	'DELETE' => 'Supprimer',
	'DELETE_FILES_IF_EXIST' => 'Supprimer les fichiers si ils existent',
	'DIR_EXISTS' => 'Le répertoire %s existe déjà.',
	'DISABLED' => 'Désactivé',
	'DROP_DB_IF_EXISTS' => 'Supprimer la base de données si elle existe',

	'EMAIL_ENABLE' => 'Activer l’envoi d’e-mail',
	'EMAIL_ENABLE_EXPLAIN' => 'Activer l’envoi d’e-mail via le forum. Pour un forum de test en local ceci devrait normalement être désactivé, à moins que vous ne testiez les e-mails.',
	'ENABLED' => 'Activé',

	'GENERAL_ERROR' => 'Erreur générale',

	'IN_SETTINGS' => 'Gérez vos paramètres QuickInstall.',
	'INCLUDE_MODS' => 'Inclure des MODs',
	'INCLUDE_MODS_EXPLAIN' => 'Sélectionnez les répertoires depuis le répertoire sources/mods/ dans cette liste, ces fichiers seront ensuite copiés dans votre nouveau répertoire racine de votre forum, ceci écrasera également les anciens fichiers (vous pouvez donc avoir ici des forums prémodés). Si vous sélectionnez “Aucun”, ceci ne sera pas utilisé (parce qu’il est pénible de déselectionner plusieurs éléments).',
	'INSTALL_BOARD' => 'Installer un forum',
	'INSTALL_QI' => 'Installer QuickInstall',

	'LICENSE' => 'Licence?',
	'LICENSE_EXPLAIN' => 'Ce script est réalisé sous les termes de la licence <a href="license.txt">GNU General Public License version 2</a>. Principalement parce que celui-ci utilise de larges portions de code phpBB, qui est également réalisé sous les termes de cette licence, et qu’il requiert des modifications pour l’utiliser également. Mais aussi parce qu’il s’agit d’une excellente licence qui garde gratuits les logiciels gratuits.',

	'MAKE_WRITABLE' => 'Rendre les fichiers éditables par tout le monde',
	'MAKE_WRITABLE_EXPLAIN' => 'Rendre les fichiers, config.php, et les répertoires éditables par tout le monde par défaut. Ceci peut être modifié lorsque vous créez un forum.',
	'MANAGE_BOARDS' => 'Administrer les forums',
	'MIGHT_TAKE_LONG' => '<strong>Veuillez noter:</strong> La création du forum peut prendre un certain temps, peut-être une minute ou plus, ne soumettez <strong>pas</strong> le formulaire deux fois.',

	'NEED_WRITABLE' => 'QuickInstall requiert que les répertoires des forums et du cache soit accessibles en écriture à tout moment. <br />Le fichier qi_config.php doit seulement être accessible en écriture au moment de l’installation de QuickInstall.',
	'NO' => 'Non',
	'NO_ALT_ENV' => 'L’environnement alternatif spécifié n’existe pas.',
	'NO_BOARDS' => 'Vous ne possédez aucun forum.',
	'NO_DB' => 'Aucune base de données sélectionnée.',
	'NO_MODULE' => 'Le module %s ne peut pas être chargé.',
	'NO_PASSWORD' => 'Aucun mot de passe',
	'NO_DBPASSWD_ERR' => 'Vous avez défini un mot de passe mais vous avez coché la case Aucun mot de passe. Vous ne pouvez pas à la fois <strong>avoir</strong> et <strong>ne pas avoir</strong> un mot de passe',
	'NONE' => 'Aucun',

	'ONLY_LOCAL' => 'Veuillez noter: QuickInstall est uniquement destiné à être utilisé localement.<br />Il ne doit pas être utilisé sur un serveur web accessible depuis internet.',
	'OPTIONS' => 'Options',
	'OPTIONS_ADVANCED' => 'Options avancées',

	'POPULATE' => 'Peupler le forum',
	'POPULATE_MAIN_EXPLAIN' => 'Utilisateurs: tester x, Mot de passe: 123456',
	'POPULATE_EXPLAIN' => 'Peuple le forum avec 5 utilisateurs (tester 1 - 5), quelques sujets et quelques messages. Les utilisateurs auront le mot de passe 123456 et l’adresse e-mail username@vos-forums-domaine. Ceci peut être modifié lorsque vous créez un forum.',

	'QI_ABOUT' => 'A propos',
	'QI_ABOUT_ABOUT' => 'Big brother vous aime et veut vous rendre heureux.',
	'QI_DST' => 'Heure d’été',
	'QI_DST_EXPLAIN' => 'Voulez-vous activer ou désactiver l’heure d’été?',
	'QI_LANG' => 'Langue de QuickInstall',
	'QI_LANG_EXPLAIN' => 'La langue qui sera utilisée par QuickInstall. Un répertoire avec cette langue doit être présent dans le répertoire language/. Cette langue sera également utilisée comme langue par défaut pour vos forums si celle-ci existe dans le répertoire sources/phpBB3/language/.',
	'QI_MAIN' => 'Page principale',
	'QI_MAIN_ABOUT' => 'Installer un nouveau forum ici.<br /><br />“Le nom de la base de données du forum” est le seul champs que vous devez compléter, les autres seront complétés avec les valeurs par défaut du fichier <em>includes/qi_config.php</em>.<br /><br />Cliquez sur “Options avancées” pour des paramètres supplémentaires.',
	'QI_MANAGE' => 'Administrer les forums',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Fuseau horaire',
	'QI_TZ_EXPLAIN' => 'Votre fuseau horaire. Il sera utilisé comme fuseau horaire par défaut pour les forums créés. -1, 0, 1 etc.',
	'QUICKINSTALL' => 'phpBB QuickInstall',

	'REDIRECT' => 'Rediriger',
	'REDIRECT_EXPLAIN' => 'Défini le paramètre Rediriger vers les nouveaux forums à oui par défaut. Ceci peut être modifié lorsque vous créez un forum.',
	'REDIRECT_BOARD' => 'Rediriger vers le nouveau forum',
	'REQUIRED' => 'est requis',
	'RESET' => 'Réinitialiser',

	'SELECT' => 'Sélectionner',
	'SETTINGS' => 'Paramètres',
	'SETTINGS_FAILURE' => 'Des erreurs sont survenues, consultez le cadre ci-dessous.',
	'SETTINGS_SUCCESS' => 'Vos paramètres ont été enregistrés.',
	'SERVER_NAME' => 'Nom du serveur',
	'SERVER_NAME_EXPLAIN' => 'Ceci devrait normalement être localhost puisque QuickInstall n’est <strong>pas</strong> destiné à des serveurs publiques.',
	'SERVER_PORT' => 'Port du serveur',
	'SERVER_PORT_EXPLAIN' => 'Généralement 80.',
	'SITE_DESC' => 'Description du site',
	'SITE_DESC_EXPLAIN' => 'La description par défaut de votre/vos forum(s). Ceci peut être modifié lorsque les forums sont créés.',
	'SITE_NAME' => 'Nom du site',
	'SITE_NAME_EXPLAIN' => 'Le nom par défaut du site qui sera utilisé pour vos forums. Ceci peut être modifié lorsque les forums sont créés.',
	'SMTP_AUTH' => 'Méthode d’authentification SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Seulement utilisé si un nom d’utilisateur et un mot de passe sont renseignés.',
	'SMTP_DELIVERY' => 'Utiliser un serveur SMTP pour l’envoi des e-mails',
	'SMTP_DELIVERY_EXPLAIN' => 'Sélectionnez “Oui” si vous voulez ou si vous devez envoyer des e-mails via un serveur au lieu d’utiliser la fonction locale mail.',
	'SMTP_HOST' => 'Adresse du serveur SMTP',
	'SMTP_HOST_EXPLAIN' => 'L’adresse du serveur SMTP que vous voulez utiliser.',
	'SMTP_PASS' => 'Mot de passe SMTP',
	'SMTP_PASS_EXPLAIN' => 'N’indiquez un mot de passe que si votre serveur SMTP le nécessite.',
	'SMTP_PORT' => 'Port du serveur SMTP',
	'SMTP_PORT_EXPLAIN' => 'Ne modifiez ceci que si vous savez que votre serveur SMTP est sur un port différent.',
	'SMTP_USER' => 'Nom d’utilisateur SMTP',
	'SMTP_USER_EXPLAIN' => 'N’entrez un nom d’utilisateur que si votre serveur SMTP en nécessite un.',
	'STAR_MANDATORY' => '* = obligatoire',
	'SUBMIT' => 'Envoyer',
	'SUBSILVER' => 'Installer Subsilver2',
	'SUBSILVER_EXPLAIN' => 'Indiquez si vous voulez que le thème Subsilver2 soit installé et si vous voulez qu’il soit le thème par défaut Ceci peut être modifié lorsque vous créez un forum.',
	'SUCCESS' => 'Réussi',

	'TABLE_PREFIX' => 'Préfixe de table',
	'TABLE_PREFIX_EXPLAIN' => 'Le préfixe de table qui sera utilisé pour vos forums. Vous pouvez modifier ceci dans les options avancés lorsque vous créez des nouveax forums.',

	'UNCHECK_ALL' => 'Décocher tout',
	'UP_TO_DATE' => 'Big brother vous annonce que vous êtes à jour.',
	'UP_TO_DATE_NOT' => 'Big brother vous annonce que vous n’êtes pas à jour.',
	'UPDATE_CHECK_FAILED' => 'La vérification de la version de Big brother a échouée.',
	'UPDATE_TO' => '<a href="%1$s">Mettre à jour vers la version %2$s.</a>',

	'YES' => 'Oui',

	'VERSION_CHECK' => 'Vérification de la version de Big brother',
	'VISIT_BOARD' => '<a href="%s">Visiter le forum</a>',

	'WHAT' => 'Quoi?',
	'WHAT_EXPLAIN' => 'phpBB3 QuickInstall est un script permettant d’installer rapidement phpBB. C’est assez évident...  ;-)',
	'WHO_ELSE' => 'Qui d’autre?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Les crédits reviennent à l’équipe phpBB, en particulier à l’équipe de développement qui a créé un tel logiciel.',
		'Merci à l’équipe de MOD de phpBB.com (particulièrement Josh, aka “A_Jelly_Doughnut”) pour AutoMOD, qui est inclu dans ce paquet.',
		'Merci à Mike TUMS pour le beau logo!',
		'Merci aux beta testeurs!',
		'Merci à la communauté phpBB y compris phpBB.com, startrekguide.com et phpBBModders.net!',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Qui? Quand?',
	'WHO_WHEN_EXPLAIN' => 'Initiallement phpBB3 QuickInstall a été créé par Igor “eviL&lt;3” Wiedler en été 2007. Il a été réécrit partiellement par lui-même en mars 2008.<br />Depuis mars 2010, ce projet est maintenu par Jari “Tumba25” Kanerva.',
	'WHY' => 'Pourquoi?',
	'WHY_EXPLAIN' => 'Tout comme avec phpBB2, si vous faites beaucoup de modding (création de modifications), vous ne pouvez pas placer tous les MODs dans une seule installation de phpBB. C’est donc mieux d’avoir plusieurs installations séparées. Maintenant le problème est qu’il est fastidieux de copier les fichiers et de passer par le processus d’installation à chaque fois. Quickinstall est né afin d’accélérer ce processus.',
));

?>