<?php
/**
*
* This file is part of French QuickInstall translation.
* Copyright (c) 2010 Maël Soucaze.
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; version 2 of the License.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License along
* with this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*
* qi [French]
*
* @package   quickinstall
* @author    Maël Soucaze <maelsoucaze@gmail.com> (Maël Soucaze) http://mael.soucaze.com/
* @copyright (c) 2007, 2008 eviL3
* @copyright (c) 2010 Jari Kanerva (tumba25)
* @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License
* @version   $Id$
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
	'ABOUT_QUICKINSTALL' => 'À propos de QuickInstall',
	'ADMIN_EMAIL' => 'Adresse e-mail de l’administrateur',
	'ADMIN_EMAIL_EXPLAIN' => 'Adresse e-mail de l’administrateur à utiliser sur vos forums',
	'ADMIN_NAME' => 'Nom d’utilisateur de l’administrateur',
	'ADMIN_NAME_EXPLAIN' => 'Le nom d’utilisateur de l’administrateur à utiliser par défaut sur vos forums. Celui-ci peut être modifié lors de la création des forums.',
	'ADMIN_PASS' => 'Mot de passe de l’administrateur',
	'ADMIN_PASS_EXPLAIN' => 'Le mot de passe de l’administrateur à utiliser par défaut sur vos forums. Celui-ci peut être modifié lors de la création des forums.',
	'ALT_ENV' => 'Alterner d’environnement',
	'AUTOMOD' => 'AutoMOD',
	'AUTOMOD_EXPLAIN' => 'Régler l’installation d’AutoMOD sur “Oui” par défaut. Ceci peut être modifié lorsque vous créez un forum.',
	'AUTOMOD_INSTALL' => 'Installer AutoMOD',

	'BACK_TO_MAIN' => '<a href="%s">Retourner à la page principale</a>',
	'BACK_TO_MANAGE' => '<a href="%s">Retourner à la page de gestion</a>',
	'BOARD_CREATED' => 'Forum créé avec succès !',
	'BOARD_DBNAME' => 'Nom du répertoire et de la base de données du forum',
	'BOARD_DESC' => 'Description du forum',
	'BOARD_EMAIL' => 'Adresse e-mail du forum',
	'BOARD_EMAIL_EXPLAIN' => 'Adresse e-mail de l’expéditeur sur vos forums.',
	'BOARD_NAME' => 'Nom du forum',
	'BOARDS_DELETED' => 'Les forums ont été supprimés avec succès.',
	'BOARDS_DELETED_TITLE' => 'Forums supprimés',
	'BOARDS_DIR' => 'Répertoire des forums',
	'BOARDS_DIR_EXPLAIN' => 'Le répertoire dans lequel vos forums seront créés. PHP a besoin de détenir les permissions d’écriture dans ce répertoire.',
	'BOARDS_LIST' => 'Liste de forums',
	'BOARDS_NOT_WRITABLE' => 'Le répertoire des forums ne peut pas être écrit.',

	'CACHE_NOT_WRITABLE' => 'Le répertoire du cache ne peut pas être écrit.',
	'CHANGELOG' => 'Historique des modifications',
	'CHECK_ALL' => 'Tout cocher',
	'CONFIG_EMPTY' => 'Le tableau de configuration était vide. Cela vaux probablement la peine de signaler le bogue.',
	'CONFIG_NOT_WRITABLE' => 'Le fichier qi_config.php ne peut pas être écrit.',
	'COOKIE_DOMAIN' => 'Domaine du cookie',
	'COOKIE_DOMAIN_EXPLAIN' => 'Cela correspond généralement à localhost.',
	'COOKIE_SECURE' => 'Cookie sécurisé',
	'COOKIE_SECURE_EXPLAIN' => 'Si votre serveur fonctionne par l’intermédiaire d’SSL, activez ceci, sinon laissez cela vide afin que cette fonctionnalité soit désactivée. Si ceci est activé alors que votre serveur ne fonctionne pas par l’intermédiaire d’SSL, votre serveur sera victime d’erreurs durant les redirections.',
	'CREATE_ADMIN' => 'Créer un administrateur',
	'CREATE_ADMIN_EXPLAIN' => 'Réglez ce réglage sur “Oui” si vous souhaitez créer un administrateur qui ne sera pas fondateur. Il portera le nom de tester_1.',
	'CREATE_MOD' => 'Créer un modérateur',
	'CREATE_MOD_EXPLAIN' => 'Réglez ce réglage sur “Oui” si vous souhaitez créer un modérateur global. Il portera le nom de tester_1 (ou tester_2 si un administrateur a déjà été créé).',

	'DB_EXISTS' => 'La base de données %s existe déjà.',
	'DB_PREFIX' => 'Préfixe de la base de données',
	'DB_PREFIX_EXPLAIN' => 'Ceci est ajouté devant tous les noms de la base de données afin d’éviter d’écraser les bases de données qui ne sont pas utilisées par QuickInstall.',
	'DBHOST' => 'Serveur de la base de données',
	'DBHOST_EXPLAIN' => 'Généralement localhost.',
	'DBMS' => 'SGBD',
	'DBMS_EXPLAIN' => 'Votre système de base de données. Si vous n’êtes pas certain de ce que vous devez mettre, réglez-le sur MySQL',
	'DBPASSWD' => 'Mot de passe de la base de données',
	'DBPASSWD_EXPLAIN' => 'Le mot de passe de l’utilisateur de la base de données',
	'DBPORT' => 'Port de la base de données',
	'DBPORT_EXPLAIN' => 'Peut généralement être laissé vide.',
	'DBUSER' => 'Utilisateur de la base de données',
	'DBUSER_EXPLAIN' => 'L’utilisateur de votre base de données. Cela doit être un utilisateur abilité à créer de nouvelles bases de données.',
	'DEFAULT' => 'défaut',
	'DEFAULT_ENV' => 'Environnement par défaut (dernier phpBB)',
	'DEFAULT_LANG' => 'Langue par défaut',
	'DEFAULT_LANG_EXPLAIN' => 'Cette langue sera utilisée sur les forums créés.',
	'DELETE' => 'Supprimer',
	'DELETE_FILES_IF_EXIST' => 'Supprimer les fichiers s’ils existent',
	'DIR_EXISTS' => 'Le répertoire %s existe déjà.',
	'DISABLED' => 'Désactivé',
	'DROP_DB_IF_EXISTS' => 'Supprimer la base de données si elle existe',

	'EMAIL_DOMAIN' => 'Domaine de l’e-mail',
	'EMAIL_DOMAIN_EXPLAIN' => 'Le domaine de l’e-mail à utiliser pour les testeurs. Leur adresse e-mail sera tester_x@&lt;domain.com&gt;.',
	'EMAIL_ENABLE' => 'Activer les e-mails',
	'EMAIL_ENABLE_EXPLAIN' => 'Activer l’envoi et la réception d’e-mails sur le forum. S’il s’agit d’un test en local, il n’est pas nécessaire de les activer, à moins que vous ne souhaitiez tester les e-mails.',
	'ENABLED' => 'Activé',

	'GENERAL_ERROR' => 'Erreur générale',

	'IN_SETTINGS' => 'Gérer vos réglages de QuickInstall.',
	'INCLUDE_MODS' => 'Inclure des MODs',
	'INCLUDE_MODS_EXPLAIN' => 'Sélectionnez dans cette liste les répertoires depuis le répertoire sources/mods/, ces fichiers seront copiés dans votre nouveau répertoire racine de votre forum, ce qui écrasera également les anciens fichiers (vous pouvez donc utiliser ici des forums prémodifiés par exemple). Si vous sélectionnez “Aucun”, cette fonctionnalité ne sera pas utilisée (parce qu’il est pénible de désélectionner plusieurs éléments).',
	'INSTALL_BOARD' => 'Installer un forum',
	'INSTALL_QI' => 'Installer QuickInstall',
	'IS_NOT_VALID' => 'N’est pas correct.',

	'LICENSE' => 'Licence ?',
	'LICENSE_EXPLAIN' => 'Ce script est sous <a href="license.txt">Licence Publique Générale GNU version 2</a>. C’est principalement dû au fait qu’il utilise une grande partie du code de phpBB, qui est également sous cette licence obligeant que toute modification effectuée hérite également de la même licence. Mais aussi parce qu’il s’agit d’une superbe licence qui fait qu’un logiciel libre soit et continue de rester libre :).',
	'LOG_INSTALL_INSTALLED_QI'	=> '<strong>Installé par Quickinstall %s</strong>',

	// To translators: Lorem Ipsum is a dummy place holder string. Do not translate this string.
	'LOREM_IPSUM' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',

	'MAKE_WRITABLE' => 'Rendre les fichiers inscriptibles',
	'MAKE_WRITABLE_EXPLAIN' => 'Rendre les fichiers, config.php et les répertoires inscriptibles par défaut. Ceci peut être modifié lorsque vous créez un forum.',
	'MANAGE_BOARDS' => 'Gérer les forums',
	'MAX' => 'Max',
	'MIGHT_TAKE_LONG' => '<strong>Veuillez noter que :</strong> la création du forum peut prendre un certain temps, souvent une minute ou un peu plus, faites donc attention à ne <strong>pas</strong> envoyer le formulaire deux fois.',
	'MIN' => 'Min',

	'NEED_EMAIL_DOMAIN' => 'Un domaine de l’e-mail est obligatoire afin que vous puissiez créer des utilisateurs de test',
	'NEED_WRITABLE' => 'QuickInstall a besoin que les forums et que les répertoires caches soient inscriptibles de manière permanente.<br />Seul le fichier qi_config.php a besoin d’être inscriptible afin de pouvoir installer QuickInstall.',
	'NO' => 'Non',
	'NO_ALT_ENV' => 'L’environnement alternatif que vous avez spécifié n’existe pas.',
	'NO_AUTOMOD' => 'AutoMOD est introuvable dans le répertoire des sources. Vous devez télécharger AutoMOD, copier le répertoire racine dans le répertoire sources/automod, puis renommer “root” en “automod”.',
	'NO_BOARDS' => 'Vous n’avez aucun forum.',
	'NO_DB' => 'Aucune base de données n’a été sélectionnée.',
	'NO_IMPACT_WIN' => 'Ce réglage n’a aucun impact sur les systèmes Windows antérieurs à Windows 7.',
	'NO_MODULE' => 'Le module %s ne peut pas être chargé.',
	'NO_PASSWORD' => 'Aucun mot de passe',
	'NO_DBPASSWD_ERR' => 'Vous avez réglé un mot de passe de la base de données et ne sélectionnant aucun mot de passe. Vous ne pouvez pas <strong>avoir</strong> et <strong>ne pas avoir</strong> de mot de passe',
	'NONE' => 'Aucun',
	'NUM_CATS' => 'Nombre de catégories',
	'NUM_CATS_EXPLAIN' => 'Le nombre de catégories à créer.',
	'NUM_FORUMS' => 'Nombre de forums',
	'NUM_FORUMS_EXPLAIN' => 'Le nombre de forums à créer. Ils seront répandus équitablement en fonction des catégories créées.',
	'NUM_NEW_GROUP' => 'Nouvellement inscrits',
	'NUM_NEW_GROUP_EXPLAIN' => 'Le nombre d’utilisateurs à placer dans le groupe des utilisateurs nouvellement inscrits.<br />Si ce nombre est plus important que le nombre d’utilisateurs au total, tous les nouveaux utilisateurs seront placés dans le groupe des utilisateurs nouvellement inscrits.',
	'NUM_REPLIES' => 'Nombre de réponses',
	'NUM_REPLIES_EXPLAIN' => 'Le nombre de réponses. Chaque sujet recevra un nombre aléatoire de réponses en fonction des valeurs maximales et minimales que vous avez saisi.',
	'NUM_TOPICS' => 'Nombre de sujets',
	'NUM_TOPICS_EXPLAIN' => 'Le nombre de sujets à créer dans chaque forum. Chaque forum contiendra un nombre aléatoire de sujets en fonction des valeurs maximales et minimales que vous avez saisi.',
	'NUM_USERS' => 'Nombre d’utilisateurs',
	'NUM_USERS_EXPLAIN' => 'Le nombre d’utilisateurs à inscrire sur votre nouveau forum.<br />Ils porteront le nom d’utilisateurs Tester_x (x correspondant à un nombre) et leur mot de passe sera “123456”.',

	'ONLY_LOCAL' => 'Veuillez notez que : QuickInstall n’est recommandé que pour une utilisation en local.<br />Il ne devrait pas être utilisé sur un serveur accessible par Internet.',
	'OPTIONS' => 'Options',
	'OPTIONS_ADVANCED' => 'Options avancées',

	'POPULATE' => 'Peupler le forum',
	'POPULATE_OPTIONS' => 'Options de population',
	'POPULATE_MAIN_EXPLAIN' => 'Noms d’utilisateurs : Tester_x. Mots de passe : 123456',
	'POPULATE_EXPLAIN' => 'Peupler le forum avec le nombre d’utilisateurs, de catégories, de forums, de sujets et de messages que vous avez spécifié ci-dessous. Plus ces nombres sont élevés, plus le temps de création du forum sera important.<br />Tous ces réglages peuvent être modifiés lorsque vous créez un forum.',

	'QI_ABOUT' => 'À propos',
	'QI_ABOUT_ABOUT' => 'Big Brother vous aime et souhaite vous rendre heureux.',
	'QI_DST' => 'Heure d’été',
	'QI_DST_EXPLAIN' => 'Souhaitez-vous activer ou désactiver l’heure d’été ?',
	'QI_LANG' => 'Langue de QuickInstall',
	'QI_LANG_EXPLAIN' => 'La langue que QuickInstall devrait utiliser. Elle doit être présente dans le répertoire language/. Cette langue doit également être celle utilisée par défaut sur vos forums, si cette langue existe dans sources/phpBB3/language/.',
	'QI_MAIN' => 'Page principale',
	'QI_MAIN_ABOUT' => 'Installez ici un nouveau forum..<br /><br />“Nom de la base de données du forum” est le seul champ que vous avez à remplir, les autres seront remplis en utilisant les valeurs par défaut contenues dans <em>includes/qi_config.php</em>.<br /><br />Cliquez sur “Options avancées” afin d’obtenir davantage de réglages.',
	'QI_MANAGE' => 'Gérer les forums',
	'QI_MANAGE_ABOUT' => 'o_O',
	'QI_TZ' => 'Fuseau horaire',
	'QI_TZ_EXPLAIN' => 'Votre fuseau horaire. Il doit être le fuseau horaire par défaut des forums créés. -1, 0, 1, etc.',
	'QUICKINSTALL' => 'QuickInstall',

	'REDIRECT' => 'Rediriger',
	'REDIRECT_EXPLAIN' => 'Active par défaut une redirection vers les nouveaux forums. Cela peut être modifié lorsque vous créez un forum.',
	'REDIRECT_BOARD' => 'Rediriger vers le nouveau forum',
	'REQUIRED' => 'est obligatoire',
	'RESET' => 'Réinitialiser',

	'SELECT' => 'Sélectionner',
	'SETTINGS' => 'Réglages',
	'SETTINGS_FAILURE' => 'Des erreurs sont survenues, consultez la fenêtre ci-dessous.',
	'SETTINGS_SUCCESS' => 'Vos réglages ont été sauvegardés avec succès.',
	'SERVER_NAME' => 'Nom du serveur',
	'SERVER_NAME_EXPLAIN' => 'Cela devrait généralement être localhost depuis que QuickInstall n’est <strong>plus</strong> destiné à être utilisé sur des serveurs publics.',
	'SERVER_PORT' => 'Port du serveur',
	'SERVER_PORT_EXPLAIN' => 'Généralement 80.',
	'SITE_DESC' => 'Description du site',
	'SITE_DESC_EXPLAIN' => 'La description par défaut de votre ou de vos forum(s). Cela peut être modifié lorsque les forums sont créés.',
	'SITE_NAME' => 'Nom du forum',
	'SITE_NAME_EXPLAIN' => 'Le nom du site par défaut de votre ou de vos forum(s). Cela peut être modifié lorsque les forums sont créés.',
	'SMTP_AUTH' => 'Méthode d’authentification pour SMTP',
	'SMTP_AUTH_EXPLAIN' => 'Ne doit être utilisé que si un nom d’utilisateur et un mot de passe ont été renseignés.',
	'SMTP_DELIVERY' => 'Utiliser le serveur SMTP pour l’envoi d’e-mails',
	'SMTP_DELIVERY_EXPLAIN' => 'Sélectionnez “Oui” si vous souhaitez envoyer des e-mails par l’intermédiaire d’un serveur nommé au lieu de la fonction locale d’e-mails.',
	'SMTP_HOST' => 'Adresse du serveur SMTP',
	'SMTP_HOST_EXPLAIN' => 'L’adresse du serveur SMTP que vous souhaitez utiliser',
	'SMTP_PASS' => 'Mot de passe SMTP',
	'SMTP_PASS_EXPLAIN' => 'Ne saisissez un mot de passe que si votre serveur SMTP le demande.',
	'SMTP_PORT' => 'Port du serveur SMTP',
	'SMTP_PORT_EXPLAIN' => 'Ne modifiez cela que si votre serveur SMTP utilise un port différent et que vous le connaissez.',
	'SMTP_USER' => 'Nom d’utilisateur SMTP',
	'SMTP_USER_EXPLAIN' => 'Ne saisissez un nom d’utlisateur que si votre serveur SMTP le demande.',
	'STAR_MANDATORY' => '* = obligatoire',
	'SUBMIT' => 'Envoyer',
	'SUBSILVER' => 'Installer subsilver2',
	'SUBSILVER_EXPLAIN' => 'Sélectionnez ceci si vous souhaitez que le thème subsilver2 soit installé et qu’il soit le style par défaut. Cela peut être modifié lorsque vous créez un forum.',
	'SUCCESS' => 'Succès',

	'TABLE_PREFIX' => 'Préfixe de table',
	'TABLE_PREFIX_EXPLAIN' => 'Le préfixe de table qui sera utilisé pour vos forums. Vous pouvez modifier cela dans les options avancées lorsque vous créez de nouveaux forums.',
	'TEST_CAT_NAME' => 'Catégorie de test %d',
	'TEST_FORUM_NAME' => 'Forum de test %d',
	'TEST_POST_START' => 'Message de test %d', // This will be on the first line in each post and then filled with lorem ipsum.
	'TEST_TOPIC_TITLE' => 'Sujet de test %d',

	'UNCHECK_ALL' => 'Tout désélectionner',
	'UP_TO_DATE' => 'Big Brother vous annonce que vous êtes à jour.',
	'UP_TO_DATE_NOT' => 'Big Brother vous annonce que vous n’êtes pas à jour.',
	'UPDATE_CHECK_FAILED' => 'La vérification de version de Big Brother a échoué.',
	'UPDATE_TO' => '<a href="%1$s">Mettre à jour vers la version %2$s.</a>',

	'YES' => 'Oui',

	'VERSION_CHECK' => 'Vérification de version de Big Brother',
	'VISIT_BOARD' => '<a href="%s">Visiter le forum</a>',

	'WHAT' => 'Qu’est-ce ?',
	'WHAT_EXPLAIN' => 'QuickInstall est un script permettant d’installer rapidement phpBB. C’est assez évident… ;-)',
	'WHO_ELSE' => 'Qui d’autre ?',
	'WHO_ELSE_EXPLAIN' => '<ul><li>' . implode('</li><li>', array(
		'Les crédits reviennent à l’équipe de phpBB, plus particulièrement à l’équipe de développement qui a réussi à créer ce logiciel d’excellente qualité.',
		'Merci à l’équipe des MODs de phpBB.com (plus spécialement à Josh, également connu sous le nom de “A_Jelly_Doughnut”) pour AutoMOD, qui est intégré à cette archive.',
		'Merci à Mike TUMS pour le superbe logo !',
		'Merci aux bêta testeurs !',
		'Merci à la communauté de phpBB, dont phpBB.com, startrekguide.com et phpBBModders.net !',
	)) . '</li></ul>',
	'WHO_WHEN' => 'Qui ? Quand ?',
	'WHO_WHEN_EXPLAIN' => 'QuickInstall a été originalement créé lors de l’été 2007 par Igor Wiedler, également connu sous le nom de “eviL&lt;3”. Il a été en partie réécrit par lui-même en mars 2008.<br />Depuis mars 2010, ce projet est maintenu par Jari Kanerva, également connu sous le nom de “Tumba25”.',
	'WHY' => 'Pourquoi ?',
	'WHY_EXPLAIN' => 'Tout comme avec phpBB2, si vous apportez de nombreuses modifications à votre forum, vous ne pourrez pas installer tous les MODs dans une seule installation de phpBB. Il est donc plus judicieux d’avoir des installations séparées, mais il est délicat de copier les fichiers et d’exécuter le processus d’installation à chaque fois. QuickInstall est donc né en ayant pour but d’accélérer ce processus.',
));

?>