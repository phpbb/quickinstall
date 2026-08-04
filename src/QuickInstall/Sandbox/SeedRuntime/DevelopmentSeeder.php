<?php
/**
 *
 * QuickInstall development preset
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

/** Builds and verifies the comprehensive development content plan. */
class DevelopmentSeeder extends SeedPlan
{
	public function seed(): void
	{
		$this->context->status('Creating 25 development users and generated avatars...');
		$users = (new UserBuilder($this->context))->seed();
		$this->context->saveManifest();
		$this->context->status('Creating development forum scenarios...');
		$forums = (new ForumBuilder($this->context))->seed();
		$this->context->saveManifest();
		$this->context->status('Creating topic, post, pagination, and poll scenarios...');
		$content = (new ContentBuilder($this->context))->seed($users, $forums);
		$this->context->saveManifest();
		$this->context->status('Creating attachments, moderation, UCP, notification, and search states...');
		(new StateBuilder($this->context))->seed($users, $forums, $content);
	}

	public function verify(): void
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
		$this->assertChecks($checks, 'Development seed verification failed: ');
		echo "Seeded development preset {$this->context->seed}: 25 users, 12 forums, 47 topic rows, 90 posts.\n";
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

	private function existingFileCount(): int
	{
		return count(array_filter($this->context->manifest['files'] ?? [], 'is_file'));
	}
}
