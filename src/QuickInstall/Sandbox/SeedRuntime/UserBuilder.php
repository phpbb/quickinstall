<?php
/**
 *
 * QuickInstall development fixtures
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

use RuntimeException;

class UserBuilder extends BaseBuilder
{
	public function seed(): array
	{
		$db = $this->context->db;
		$passwords = $this->context->container->get('passwords.manager');
		$registered = $this->requiredGroup('REGISTERED');
		$administrators = $this->requiredGroup('ADMINISTRATORS');
		$moderators = $this->requiredGroup('GLOBAL_MODERATORS');
		$users = [];

		for ($index = 1; $index <= 25; $index++)
		{
			$username = sprintf('qi_dev_%d_user_%02d', $this->context->seed, $index);
			$result = $db->sql_query_limit(
				'SELECT user_id FROM ' . USERS_TABLE . "
					WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'",
				1
			);
			$userId = (int) $db->sql_fetchfield('user_id');
			$db->sql_freeresult($result);

			if (!$userId)
			{
				$userId = (int) user_add([
					'username' => $username,
					'user_password' => $passwords->hash(SeedContext::PASSWORD),
					'user_email' => sprintf('%s@example.test', $username),
					'group_id' => $registered,
					'user_type' => USER_NORMAL,
					'user_ip' => '127.0.0.1',
					'user_lang' => 'en',
				]);
			}
			if (!$userId)
			{
				throw new RuntimeException("Unable to create development user: $username");
			}

			$targetGroup = $index <= 2 ? $administrators : ($index <= 4 ? $moderators : $registered);
			group_user_add($targetGroup, [$userId], false, false, true);
			$this->applySignature($userId, $username);
			$this->applyAvatar($userId, $index);
			$this->context->addId('users', $userId);
			$users[$index] = $userId;
		}

		$this->markInactive($users[24]);
		$this->markBanned($users[25]);
		$this->addWarnings([$users[5], $users[6]]);

		$roles = [
			'admin' => $users[1],
			'admin_2' => $users[2],
			'moderator' => $users[3],
			'moderator_2' => $users[4],
			'regular' => $users[5],
			'regular_2' => $users[6],
			'regular_3' => $users[7],
			'inactive' => $users[24],
			'banned' => $users[25],
			'posters' => array_values(array_slice($users, 0, 23, true)),
		];
		$this->context->setMeta('users', $roles);
		return $roles;
	}

	private function requiredGroup(string $name): int
	{
		$id = $this->context->groupId($name);
		if (!$id)
		{
			throw new RuntimeException("Required phpBB group is unavailable: $name");
		}
		return $id;
	}

	private function applySignature(int $userId, string $username): void
	{
		$db = $this->context->db;
		$signature = sprintf('[b]%s[/b] — sample signature with [i]formatted text[/i].', $username);
		$uid = $bitfield = '';
		$options = 7;
		generate_text_for_storage($signature, $uid, $bitfield, $options, true, true, true);
		$db->sql_query('UPDATE ' . USERS_TABLE . "
			SET user_sig = '" . $db->sql_escape($signature) . "',
				user_sig_bbcode_uid = '" . $db->sql_escape($uid) . "',
				user_sig_bbcode_bitfield = '" . $db->sql_escape($bitfield) . "'
			WHERE user_id = $userId");
	}

	private function applyAvatar(int $userId, int $index): void
	{
		$db = $this->context->db;
		$avatarPath = $this->context->storageDirectory(
			'avatar',
			(string) ($this->context->config['avatar_path'] ?? 'images/avatars/upload')
		);
		$directory = $this->context->root . $avatarPath;
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory))
		{
			throw new RuntimeException("Unable to create avatar directory: $directory");
		}

		$salt = (string) ($this->context->config['avatar_salt'] ?? '');
		$filename = $salt . '_' . $userId . '.png';
		$path = $directory . '/' . $filename;
		$this->context->writeFile(
			'avatar',
			$filename,
			$path,
			AssetFactory::png(80, 80, $this->context->seed * 100 + $index)
		);

		$avatar = sprintf('%d_qidev%d.png', $userId, $this->context->seed);
		$db->sql_query('UPDATE ' . USERS_TABLE . "
			SET user_avatar = '" . $db->sql_escape($avatar) . "',
				user_avatar_type = 'avatar.driver.upload',
				user_avatar_width = 80,
				user_avatar_height = 80
			WHERE user_id = $userId");
	}

	private function markInactive(int $userId): void
	{
		$this->context->db->sql_query('UPDATE ' . USERS_TABLE . '
			SET user_type = ' . USER_INACTIVE . ',
				user_inactive_reason = ' . INACTIVE_MANUAL . ',
				user_inactive_time = ' . time() . '
			WHERE user_id = ' . $userId);
	}

	private function markBanned(int $userId): void
	{
		$db = $this->context->db;
		$table = $this->context->banTable();
		$result = $db->sql_query_limit('SELECT ban_id FROM ' . $table . ' WHERE ban_userid = ' . $userId, 1);
		$banId = (int) $db->sql_fetchfield('ban_id');
		$db->sql_freeresult($result);
		if (!$banId)
		{
			$row = [
				'ban_userid' => $userId,
				'ban_start' => time(),
				'ban_end' => 0,
				'ban_reason' => 'Repeatedly ignored the community guidelines.',
			];
			if ($this->context->usesModernBans())
			{
				$row += [
					'ban_mode' => 'user',
					'ban_item' => (string) $userId,
					'ban_reason_display' => 'Please review the community guidelines before returning.',
				];
			}
			else
			{
				$row += [
					'ban_ip' => '',
					'ban_email' => '',
					'ban_exclude' => 0,
					'ban_give_reason' => 'Please review the community guidelines before returning.',
				];
			}
			$db->sql_query('INSERT INTO ' . $table . ' ' . $db->sql_build_array('INSERT', $row));
			$banId = (int) $db->sql_nextid();
		}
		$this->context->addId('bans', $banId);
	}

	private function addWarnings(array $userIds): void
	{
		$db = $this->context->db;
		foreach ($userIds as $userId)
		{
			$result = $db->sql_query_limit(
				'SELECT warning_id FROM ' . WARNINGS_TABLE . ' WHERE user_id = ' . (int) $userId . ' AND post_id = 0',
				1
			);
			$warningId = (int) $db->sql_fetchfield('warning_id');
			$db->sql_freeresult($result);
			if (!$warningId)
			{
				$db->sql_query('INSERT INTO ' . WARNINGS_TABLE . ' ' . $db->sql_build_array('INSERT', [
					'user_id' => (int) $userId,
					'post_id' => 0,
					'log_id' => 0,
					'warning_time' => time(),
				]));
				$warningId = (int) $db->sql_nextid();
				$db->sql_query('UPDATE ' . USERS_TABLE . '
					SET user_warnings = user_warnings + 1,
						user_last_warning = ' . time() . '
					WHERE user_id = ' . (int) $userId);
			}
			$this->context->addId('warnings', $warningId);
		}
	}
}
