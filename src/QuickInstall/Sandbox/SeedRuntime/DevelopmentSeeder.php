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

class DevelopmentSeeder
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
			throw new RuntimeException("Unknown development seed action: $action");
		}

		$hasManifest = $this->context->loadManifest();
		if ($hasManifest)
		{
			$this->context->reconcileStorageFiles();
			$this->context->saveManifest();
		}
		if ($action === 'reset' || $action === 'replace')
		{
			if (!$hasManifest)
			{
				$this->discoverExistingFixtures();
				$this->context->reconcileStorageFiles();
			}
			$this->context->status('Resetting development fixtures...');
			$this->reset();
			if ($action === 'reset')
			{
				echo "Reset development seed {$this->context->seed}.\n";
				return;
			}
			$hasManifest = false;
		}

		if ($hasManifest)
		{
			$this->context->status('Development seed already exists; verifying fixtures...');
			$this->verify();
			return;
		}

		$this->context->setMeta('founder_state', $this->founderState());
		$this->context->status('Creating 25 development users and generated avatars...');
		$users = (new UserBuilder($this->context))->seed();
		$this->context->status('Creating development forum scenarios...');
		$forums = (new ForumBuilder($this->context))->seed();
		$this->context->status('Creating topic, post, pagination, and poll scenarios...');
		$content = (new ContentBuilder($this->context))->seed($users, $forums);
		$this->context->status('Creating attachments, moderation, UCP, notification, and search states...');
		(new StateBuilder($this->context))->seed($users, $forums, $content);
		$this->finalize();
		$this->context->saveManifest();
		$this->verify();
	}

	private function reset(): void
	{
		$db = $this->context->db;
		$this->deleteByIds(NOTIFICATIONS_TABLE, 'notification_id', $this->context->ids('notifications'));
		$this->deleteByIds(REPORTS_TABLE, 'report_id', $this->context->ids('reports'));
		$this->deleteByIds(DRAFTS_TABLE, 'draft_id', $this->context->ids('drafts'));
		$this->deleteByIds(ATTACHMENTS_TABLE, 'attach_id', $this->context->ids('attachments'));

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
		$this->deleteByIds($this->context->banTable(), 'ban_id', $this->context->ids('bans'));
		$userIds = $this->context->ids('users');
		if ($userIds)
		{
			user_delete('remove', $userIds);
		}

		$this->context->deleteRegisteredFiles();
		$this->restoreFounderState();
		$this->syncUserPostCounts();
		$this->syncPrivateMessageCounts();
		$this->finalize(false);
		$path = $this->context->manifestPath();
		if (is_file($path) && !unlink($path))
		{
			throw new RuntimeException("Unable to delete development seed manifest: $path");
		}
	}

	private function deleteByIds(string $table, string $column, array $ids): void
	{
		if ($ids)
		{
			$this->context->db->sql_query("DELETE FROM $table WHERE " . $this->context->db->sql_in_set($column, $ids));
		}
	}

	private function finalize(bool $markUnread = true): void
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

	private function verify(): void
	{
		$users = $this->context->meta('users', []);
		$forums = $this->context->meta('forums', []);
		$content = $this->context->meta('content', []);
		$messages = $this->context->meta('messages', []);
		$ucp = $this->context->meta('ucp', []);
		$restrictedGroups = array_filter([
			$this->context->groupId('GUESTS'),
			$this->context->groupId('REGISTERED'),
			$this->context->groupId('NEWLY_REGISTERED'),
		]);
		$checks = [
			'25 users' => $this->existingIdCount(USERS_TABLE, 'user_id', $this->context->ids('users')) === 25,
			'25 avatars' => $this->fieldCount(USERS_TABLE, 'user_id', $this->context->ids('users'), "user_avatar <> ''") === 25,
			'25 signatures' => $this->fieldCount(USERS_TABLE, 'user_id', $this->context->ids('users'), "user_sig <> ''") === 25,
			'inactive user state' => isset($users['inactive']) && $this->queryCount(USERS_TABLE, 'user_id = ' . (int) $users['inactive'] . ' AND user_type = ' . USER_INACTIVE) === 1,
			'4 categories' => $this->existingIdCount(FORUMS_TABLE, 'forum_id', $this->context->ids('categories')) === 4,
			'12 forums' => $this->existingIdCount(FORUMS_TABLE, 'forum_id', $this->context->ids('forums')) === 12,
			'password forum uses standard credential' => isset($forums['password']) && $this->forumPasswordMatches((int) $forums['password']),
			'private forum ACL' => isset($forums['private']) && (!$restrictedGroups || $this->queryCount(ACL_GROUPS_TABLE, 'forum_id = ' . (int) $forums['private'] . ' AND ' . $this->context->db->sql_in_set('group_id', $restrictedGroups)) === 0),
			'empty forum state' => isset($forums['empty']) && $this->queryCount(TOPICS_TABLE, 'forum_id = ' . (int) $forums['empty']) === 0,
			'nested subforums' => isset($forums['parent'], $forums['child_a'], $forums['child_b']) && $this->queryCount(FORUMS_TABLE, 'parent_id = ' . (int) $forums['parent'] . ' AND ' . $this->context->db->sql_in_set('forum_id', [(int) $forums['child_a'], (int) $forums['child_b']])) === 2,
			'47 topic rows' => $this->existingIdCount(TOPICS_TABLE, 'topic_id', $this->context->ids('topics')) === 47,
			'90 posts' => $this->existingIdCount(POSTS_TABLE, 'post_id', $this->context->ids('posts')) === 90,
			'unapproved post' => $this->fieldCount(POSTS_TABLE, 'post_id', $this->context->ids('unapproved_posts'), 'post_visibility = ' . ITEM_UNAPPROVED) === 1,
			'2 polls' => $this->fieldCount(TOPICS_TABLE, 'topic_id', $this->context->ids('topics'), "poll_title <> ''") === 2,
			'6 poll votes' => isset($content['voted_poll']['topic_id']) && $this->queryCount(POLL_VOTES_TABLE, 'topic_id = ' . (int) $content['voted_poll']['topic_id']) === 6,
			'2 attachments' => $this->existingIdCount(ATTACHMENTS_TABLE, 'attach_id', $this->context->ids('attachments')) === 2,
			'2 open reports' => $this->existingIdCount(REPORTS_TABLE, 'report_id', $this->context->ids('reports')) === 2,
			'9 private messages' => $this->existingIdCount(PRIVMSGS_TABLE, 'msg_id', $this->context->ids('messages')) === 9,
			'read private message' => isset($messages['read']) && $this->queryCount(PRIVMSGS_TO_TABLE, 'msg_id = ' . (int) $messages['read'] . ' AND user_id = ' . $this->context->founderId() . ' AND pm_unread = 0 AND folder_id = ' . PRIVMSGS_INBOX) === 1,
			'sent private message' => isset($messages['sent']) && $this->queryCount(PRIVMSGS_TO_TABLE, 'msg_id = ' . (int) $messages['sent'] . ' AND user_id = ' . $this->context->founderId() . ' AND folder_id = ' . PRIVMSGS_SENTBOX) === 1,
			'3 unread notifications' => $this->fieldCount(NOTIFICATIONS_TABLE, 'notification_id', $this->context->ids('notifications'), 'notification_read = 0') === 3,
			'2 warnings' => $this->existingIdCount(WARNINGS_TABLE, 'warning_id', $this->context->ids('warnings')) === 2,
			'1 active ban' => $this->existingIdCount($this->context->banTable(), 'ban_id', $this->context->ids('bans')) === 1,
			'1 saved draft' => $this->existingIdCount(DRAFTS_TABLE, 'draft_id', $this->context->ids('drafts')) === 1,
			'topic bookmark' => isset($ucp['topic_id'], $ucp['user_id']) && $this->queryCount(BOOKMARKS_TABLE, 'topic_id = ' . (int) $ucp['topic_id'] . ' AND user_id = ' . (int) $ucp['user_id']) === 1,
			'topic watch' => isset($ucp['topic_id'], $ucp['user_id']) && $this->queryCount(TOPICS_WATCH_TABLE, 'topic_id = ' . (int) $ucp['topic_id'] . ' AND user_id = ' . (int) $ucp['user_id']) === 1,
			'forum watch' => isset($ucp['forum_id'], $ucp['user_id']) && $this->queryCount(FORUMS_WATCH_TABLE, 'forum_id = ' . (int) $ucp['forum_id'] . ' AND user_id = ' . (int) $ucp['user_id']) === 1,
			'generated files' => $this->existingFileCount() === 28,
			'generated storage files' => !$this->context->usesStorage()
				|| ($this->context->storageFileCount() === 27 && $this->context->storageFilesExist()),
			'search backend indexed' => (string) $this->context->meta('search_backend', '') !== '',
			'locked topic' => $this->topicStateCount('topic_status', ITEM_LOCKED) >= 1,
			'moved redirect' => $this->topicStateCount('topic_status', ITEM_MOVED) === 1,
			'sticky topic' => $this->topicStateCount('topic_type', POST_STICKY) >= 1,
			'announcement topic' => $this->topicStateCount('topic_type', POST_ANNOUNCE) >= 1,
			'global announcement' => $this->topicStateCount('topic_type', POST_GLOBAL) >= 1,
		];
		$failed = [];
		foreach ($checks as $label => $passed)
		{
			echo ($passed ? '[OK] ' : '[FAIL] ') . $label . "\n";
			if (!$passed)
			{
				$failed[] = $label;
			}
		}
		if ($failed)
		{
			throw new RuntimeException('Development fixture verification failed: ' . implode(', ', $failed));
		}
		echo "Seeded development preset {$this->context->seed}: 25 users, 12 forums, 47 topic rows, 90 posts.\n";
	}

	private function existingIdCount(string $table, string $column, array $ids): int
	{
		if (!$ids)
		{
			return 0;
		}
		$result = $this->context->db->sql_query("SELECT COUNT($column) AS total FROM $table WHERE "
			. $this->context->db->sql_in_set($column, $ids));
		$count = (int) $this->context->db->sql_fetchfield('total');
		$this->context->db->sql_freeresult($result);
		return $count;
	}

	private function fieldCount(string $table, string $column, array $ids, string $condition): int
	{
		if (!$ids)
		{
			return 0;
		}
		$result = $this->context->db->sql_query("SELECT COUNT($column) AS total FROM $table WHERE "
			. $this->context->db->sql_in_set($column, $ids) . " AND $condition");
		$count = (int) $this->context->db->sql_fetchfield('total');
		$this->context->db->sql_freeresult($result);
		return $count;
	}

	private function topicStateCount(string $column, int $value): int
	{
		return $this->fieldCount(TOPICS_TABLE, 'topic_id', $this->context->ids('topics'), "$column = $value");
	}

	private function forumPasswordMatches(int $forumId): bool
	{
		$result = $this->context->db->sql_query_limit(
			'SELECT forum_password FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $forumId,
			1
		);
		$password = (string) $this->context->db->sql_fetchfield('forum_password');
		$this->context->db->sql_freeresult($result);
		return $password !== ''
			&& $this->context->container->get('passwords.manager')->check(SeedContext::PASSWORD, $password);
	}

	private function queryCount(string $table, string $where): int
	{
		$result = $this->context->db->sql_query("SELECT COUNT(*) AS total FROM $table WHERE $where");
		$count = (int) $this->context->db->sql_fetchfield('total');
		$this->context->db->sql_freeresult($result);
		return $count;
	}

	private function existingFileCount(): int
	{
		return count(array_filter($this->context->manifest['files'] ?? [], 'is_file'));
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

	private function discoverExistingFixtures(): void
	{
		$db = $this->context->db;
		$userPattern = sprintf('qi_dev_%d_user_%%', $this->context->seed);
		$this->discoverIds('users', USERS_TABLE, 'user_id', "username LIKE '" . $db->sql_escape($userPattern) . "'");
		$this->discoverIds('categories', FORUMS_TABLE, 'forum_id', "forum_name LIKE '" . $db->sql_escape($this->context->prefix . '%') . "' AND forum_type = " . FORUM_CAT);
		$this->discoverIds('forums', FORUMS_TABLE, 'forum_id', "forum_name LIKE '" . $db->sql_escape($this->context->prefix . '%') . "' AND forum_type <> " . FORUM_CAT);
		$this->discoverIds('topics', TOPICS_TABLE, 'topic_id', "topic_title LIKE '" . $db->sql_escape($this->context->prefix . '%') . "'");
		$topicIds = $this->context->ids('topics');
		if ($topicIds)
		{
			$this->discoverIds('posts', POSTS_TABLE, 'post_id', $db->sql_in_set('topic_id', $topicIds));
		}
		$messagePattern = $this->context->prefix . '%';
		$this->discoverIds('messages', PRIVMSGS_TABLE, 'msg_id', "message_subject LIKE '" . $db->sql_escape($messagePattern) . "'");
		$this->discoverIds('drafts', DRAFTS_TABLE, 'draft_id', "draft_subject LIKE '" . $db->sql_escape($messagePattern) . "'");
		$this->discoverIds('attachments', ATTACHMENTS_TABLE, 'attach_id', "physical_filename LIKE '"
			. $db->sql_escape('qi_dev_' . $this->context->seed . '_%') . "'");

		$userIds = $this->context->ids('users');
		if ($userIds)
		{
			$this->discoverIds('warnings', WARNINGS_TABLE, 'warning_id', $db->sql_in_set('user_id', $userIds));
			$this->discoverIds('bans', $this->context->banTable(), 'ban_id', $db->sql_in_set('ban_userid', $userIds));
		}
		$postIds = $this->context->ids('posts');
		$messageIds = $this->context->ids('messages');
		$reportClauses = [];
		if ($postIds)
		{
			$reportClauses[] = $db->sql_in_set('post_id', $postIds);
		}
		if ($messageIds)
		{
			$reportClauses[] = $db->sql_in_set('pm_id', $messageIds);
		}
		if ($reportClauses)
		{
			$this->discoverIds('reports', REPORTS_TABLE, 'report_id', '(' . implode(' OR ', $reportClauses) . ')');
		}
		$itemIds = array_values(array_unique(array_merge($postIds, $messageIds)));
		if ($itemIds)
		{
			$this->discoverIds('notifications', NOTIFICATIONS_TABLE, 'notification_id', $db->sql_in_set('item_id', $itemIds));
		}
		$this->discoverFiles();
	}

	private function discoverIds(string $type, string $table, string $column, string $where): void
	{
		$result = $this->context->db->sql_query("SELECT $column FROM $table WHERE $where");
		while ($row = $this->context->db->sql_fetchrow($result))
		{
			$this->context->addId($type, (int) $row[$column]);
		}
		$this->context->db->sql_freeresult($result);
	}

	private function discoverFiles(): void
	{
		$salt = (string) ($this->context->config['avatar_salt'] ?? '');
		$avatarPath = trim((string) ($this->context->config['avatar_path'] ?? 'images/avatars/upload'), '/');
		foreach ($this->context->ids('users') as $userId)
		{
			$path = $this->context->root . $avatarPath . '/' . $salt . '_' . $userId . '.png';
			if (is_file($path))
			{
				$this->context->addFile($path);
			}
		}
		$icon = $this->context->root . 'images/qi-development-' . $this->context->seed . '-forum.png';
		if (is_file($icon))
		{
			$this->context->addFile($icon);
		}
		foreach (glob($this->context->root . 'files/qi_dev_' . $this->context->seed . '_*') ?: [] as $path)
		{
			$this->context->addFile($path);
		}
	}
}
