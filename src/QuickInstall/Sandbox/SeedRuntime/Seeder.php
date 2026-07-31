<?php
/**
 *
 * QuickInstall seed runtime
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

use RuntimeException;

/** Coordinates every preset through one manifest, reset, and finalization lifecycle. */
class Seeder
{
	private $context;

	public function __construct(SeedContext $context)
	{
		$this->context = $context;
	}

	public function run(string $action): void
	{
		if (!in_array($action, ['seed', 'reset', 'replace'], true))
		{
			throw new RuntimeException("Unknown seed action: $action");
		}

		$strategy = $this->strategy();
		$hasManifest = $this->context->loadManifest();
		if ($hasManifest)
		{
			$this->context->reconcileStorageFiles();
			$this->context->saveManifest();
		}

		if ($action === 'reset' || $action === 'replace')
		{
			$this->context->status("Resetting {$this->context->preset} seed data...");
			if ($hasManifest)
			{
				$this->reset();
			}
			if ($action === 'reset')
			{
				echo "Reset {$this->context->preset} seed {$this->context->seed}.\n";
				return;
			}
			$this->context->resetManifest();
			$hasManifest = false;
		}

		if ($hasManifest)
		{
			$this->context->status("{$this->context->preset} seed already exists; verifying data...");
			$strategy->verify();
			return;
		}

		$this->context->setMeta('founder_state', $this->founderState());
		$strategy->seed();
		$this->finalize($this->context->preset === 'development');
		$this->context->saveManifest();
		$strategy->verify();
	}

	private function strategy()
	{
		if ($this->context->preset === 'development')
		{
			return new DevelopmentSeeder($this->context);
		}
		return new VolumeSeeder($this->context);
	}

	private function reset(): void
	{
		$db = $this->context->db;
		$this->deleteByIds(NOTIFICATIONS_TABLE, 'notification_id', $this->context->ids('notifications'));
		$this->deleteByIds(REPORTS_TABLE, 'report_id', $this->context->ids('reports'));
		$this->deleteByIds(DRAFTS_TABLE, 'draft_id', $this->context->ids('drafts'));
		$this->deleteByIds(ATTACHMENTS_TABLE, 'attach_id', $this->context->ids('attachments'));
		$this->deleteByIds(LOG_TABLE, 'log_id', $this->context->ids('logs'));

		$messageIds = $this->context->ids('messages');
		$this->deleteByIds(PRIVMSGS_TO_TABLE, 'msg_id', $messageIds);
		$this->deleteByIds(PRIVMSGS_TABLE, 'msg_id', $messageIds);

		$topicIds = $this->context->ids('topics');
		$forumIds = array_values(array_unique(array_merge(
			$this->context->ids('forums'),
			$this->context->ids('categories')
		)));
		$founderId = $this->context->founderId();
		if ($topicIds)
		{
			$db->sql_query('DELETE FROM ' . BOOKMARKS_TABLE . '
				WHERE user_id = ' . $founderId . '
					AND ' . $db->sql_in_set('topic_id', $topicIds));
			$db->sql_query('DELETE FROM ' . TOPICS_WATCH_TABLE . '
				WHERE user_id = ' . $founderId . '
					AND ' . $db->sql_in_set('topic_id', $topicIds));
			delete_topics('topic_id', $topicIds, true, true, true);
		}
		if ($forumIds)
		{
			$db->sql_query('DELETE FROM ' . FORUMS_WATCH_TABLE . '
				WHERE user_id = ' . $founderId . '
					AND ' . $db->sql_in_set('forum_id', $forumIds));
			foreach ([ACL_GROUPS_TABLE, ACL_USERS_TABLE, FORUMS_TRACK_TABLE, FORUMS_ACCESS_TABLE] as $table)
			{
				$db->sql_query("DELETE FROM $table WHERE " . $db->sql_in_set('forum_id', $forumIds));
			}
			$db->sql_query('DELETE FROM ' . FORUMS_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forumIds));
			$newId = 1;
			recalc_nested_sets($newId, 'forum_id', FORUMS_TABLE);
		}

		$this->deleteByIds(WARNINGS_TABLE, 'warning_id', $this->context->ids('warnings'));
		$banIds = $this->context->ids('bans');
		if ($banIds)
		{
			$this->deleteByIds($this->context->banTable(), 'ban_id', $banIds);
		}
		$userIds = $this->context->ids('users');
		if ($userIds)
		{
			user_delete('remove', $userIds);
		}

		$this->context->deleteRegisteredFiles();
		$this->restoreFounderState();
		$this->syncPrivateMessageCounts();
		$this->finalize(false);
		$path = $this->context->manifestPath();
		if (is_file($path) && !unlink($path))
		{
			throw new RuntimeException("Unable to delete seed manifest: $path");
		}
	}

	private function deleteByIds(string $table, string $column, array $ids): void
	{
		if ($ids)
		{
			$this->context->db->sql_query("DELETE FROM $table WHERE " . $this->context->db->sql_in_set($column, $ids));
		}
	}

	private function finalize(bool $markUnread): void
	{
		$db = $this->context->db;
		sync('forum', '', '', false, true);
		sync('topic');
		$this->syncUserPostCounts();

		if ($markUnread)
		{
			$founderId = $this->context->founderId();
			$db->sql_query('UPDATE ' . USERS_TABLE . '
				SET user_lastmark = ' . (time() - 86400) . ',
					user_lastvisit = ' . (time() - 86400) . '
				WHERE user_id = ' . $founderId);
		}
		$this->syncConfigCounts();
		$this->context->auth->acl_clear_prefetch();
		$GLOBALS['cache']->purge();
	}

	private function syncUserPostCounts(): void
	{
		$db = $this->context->db;
		$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_posts = 0');
		$result = $db->sql_query('SELECT poster_id, COUNT(post_id) AS post_count
			FROM ' . POSTS_TABLE . '
			WHERE poster_id > 1
				AND post_postcount = 1
				AND post_visibility = ' . ITEM_APPROVED . '
			GROUP BY poster_id');
		while ($row = $db->sql_fetchrow($result))
		{
			$db->sql_query('UPDATE ' . USERS_TABLE . '
				SET user_posts = ' . (int) $row['post_count'] . '
				WHERE user_id = ' . (int) $row['poster_id']);
		}
		$db->sql_freeresult($result);
	}

	private function syncPrivateMessageCounts(): void
	{
		$db = $this->context->db;
		$userIds = array_values(array_unique(array_merge(
			[$this->context->founderId()],
			$this->context->ids('users')
		)));
		foreach ($userIds as $userId)
		{
			$result = $db->sql_query('SELECT
					SUM(CASE WHEN pm_new = 1 THEN 1 ELSE 0 END) AS new_count,
					SUM(CASE WHEN pm_unread = 1 THEN 1 ELSE 0 END) AS unread_count
				FROM ' . PRIVMSGS_TO_TABLE . '
				WHERE user_id = ' . $userId);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			$db->sql_query('UPDATE ' . USERS_TABLE . '
				SET user_new_privmsg = ' . (int) ($row['new_count'] ?? 0) . ',
					user_unread_privmsg = ' . (int) ($row['unread_count'] ?? 0) . '
				WHERE user_id = ' . $userId);
		}
	}

	private function syncConfigCounts(): void
	{
		$db = $this->context->db;
		$config = $this->context->config;
		$result = $db->sql_query('SELECT COUNT(user_id) AS total FROM ' . USERS_TABLE . ' WHERE user_type <> ' . USER_IGNORE);
		$config->set('num_users', (int) $db->sql_fetchfield('total'));
		$db->sql_freeresult($result);
		$result = $db->sql_query('SELECT COUNT(post_id) AS total FROM ' . POSTS_TABLE . ' WHERE post_visibility = ' . ITEM_APPROVED);
		$config->set('num_posts', (int) $db->sql_fetchfield('total'));
		$db->sql_freeresult($result);
		$result = $db->sql_query('SELECT COUNT(topic_id) AS total FROM ' . TOPICS_TABLE . ' WHERE topic_visibility = ' . ITEM_APPROVED);
		$config->set('num_topics', (int) $db->sql_fetchfield('total'));
		$db->sql_freeresult($result);
		$result = $db->sql_query_limit(
			'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . '
				WHERE user_type <> ' . USER_IGNORE . '
				ORDER BY user_id DESC',
			1
		);
		$newest = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if ($newest)
		{
			$config->set('newest_user_id', (int) $newest['user_id']);
			$config->set('newest_username', $newest['username']);
			$config->set('newest_user_colour', $newest['user_colour']);
		}
	}

	private function founderState(): array
	{
		$founderId = $this->context->founderId();
		$result = $this->context->db->sql_query_limit(
			'SELECT user_lastmark, user_lastvisit FROM ' . USERS_TABLE . ' WHERE user_id = ' . $founderId,
			1
		);
		$row = $this->context->db->sql_fetchrow($result) ?: [];
		$this->context->db->sql_freeresult($result);
		return [
			'user_id' => $founderId,
			'user_lastmark' => (int) ($row['user_lastmark'] ?? 0),
			'user_lastvisit' => (int) ($row['user_lastvisit'] ?? 0),
		];
	}

	private function restoreFounderState(): void
	{
		$state = $this->context->meta('founder_state', []);
		if (!$state)
		{
			return;
		}
		$this->context->db->sql_query('UPDATE ' . USERS_TABLE . '
			SET user_lastmark = ' . (int) $state['user_lastmark'] . ',
				user_lastvisit = ' . (int) $state['user_lastvisit'] . '
			WHERE user_id = ' . (int) $state['user_id']);
	}
}
