<?php
/**
 *
 * QuickInstall volume presets
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

use RuntimeException;

/** Builds count-based tiny, extension-dev, load-test, and random content plans. */
class StandardSeeder
{
	private $context;

	public function __construct(SeedContext $context)
	{
		$this->context = $context;
	}

	public function seed(): void
	{
		$counts = $this->resolveCounts();
		$this->context->setMeta('counts', $counts);

		$this->context->status('Creating ' . $counts['users'] . ' ' . $this->context->preset . ' ' . $this->plural((int) $counts['users'], 'user', 'users') . '...');
		$users = $this->seedUsers($counts['users'], $counts['groups']);
		$this->context->saveManifest();
		$forumCount = (int) $counts['categories'] * (int) $counts['forums_per_category'];
		$this->context->status('Creating ' . $counts['categories'] . ' '
			. $this->plural((int) $counts['categories'], 'category', 'categories') . " and $forumCount "
			. $this->plural($forumCount, 'forum', 'forums') . '...');
		$forums = $this->seedForums($counts['categories'], $counts['forums_per_category']);
		$this->context->saveManifest();
		$this->context->status('Creating ' . $counts['topics'] . ' '
			. $this->plural((int) $counts['topics'], 'topic', 'topics') . ' with ' . $counts['replies'] . ' '
			. $this->plural((int) $counts['replies'], 'reply', 'replies') . ' each...');
		$this->seedPosts($forums, $users, $counts['topics'], $counts['replies']);
	}

	public function verify(): void
	{
		$counts = $this->context->meta('counts', []);
		if (!$counts)
		{
			throw new RuntimeException("{$this->context->preset} seed manifest has no resolved counts.");
		}

		$expectedForums = (int) $counts['categories'] * (int) $counts['forums_per_category'];
		$expectedPosts = (int) $counts['topics'] * ((int) $counts['replies'] + 1);
		$checks = [
			$counts['users'] . ' ' . $this->plural((int) $counts['users'], 'user', 'users') => $this->existingIdCount(USERS_TABLE, 'user_id', $this->context->ids('users')) === (int) $counts['users'],
			$counts['categories'] . ' ' . $this->plural((int) $counts['categories'], 'category', 'categories') => $this->existingIdCount(FORUMS_TABLE, 'forum_id', $this->context->ids('categories')) === (int) $counts['categories'],
			$expectedForums . ' ' . $this->plural($expectedForums, 'forum', 'forums') => $this->existingIdCount(FORUMS_TABLE, 'forum_id', $this->context->ids('forums')) === $expectedForums,
			$counts['topics'] . ' ' . $this->plural((int) $counts['topics'], 'topic', 'topics') => $this->existingIdCount(TOPICS_TABLE, 'topic_id', $this->context->ids('topics')) === (int) $counts['topics'],
			$expectedPosts . ' ' . $this->plural($expectedPosts, 'post', 'posts') => $this->existingIdCount(POSTS_TABLE, 'post_id', $this->context->ids('posts')) === $expectedPosts,
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
			throw new RuntimeException("{$this->context->preset} seed verification failed: " . implode(', ', $failed));
		}

		echo "Seeded {$this->context->preset} preset {$this->context->seed}: {$counts['users']} "
			. $this->plural((int) $counts['users'], 'user', 'users') . ", {$counts['categories']} "
			. $this->plural((int) $counts['categories'], 'category', 'categories') . ", $expectedForums "
			. $this->plural($expectedForums, 'forum', 'forums') . ", {$counts['topics']} "
			. $this->plural((int) $counts['topics'], 'topic', 'topics') . ", $expectedPosts "
			. $this->plural($expectedPosts, 'post', 'posts') . ".\n";
	}

	private function resolveCounts(): array
	{
		$presets = [
			'tiny' => ['users' => 3, 'categories' => 1, 'forums_per_category' => 2, 'topics' => 2, 'replies' => 2, 'groups' => false],
			'extension-dev' => ['users' => 10, 'categories' => 2, 'forums_per_category' => 3, 'topics' => 25, 'replies' => 10, 'groups' => true],
			'load-test' => ['users' => 100, 'categories' => 4, 'forums_per_category' => 5, 'topics' => 100, 'replies' => 20, 'groups' => true],
			'random' => ['users' => 100, 'categories' => 4, 'forums_per_category' => 5, 'topics' => 100, 'replies' => 20, 'groups' => true, 'randomize' => true],
		];
		if (!isset($presets[$this->context->preset]))
		{
			throw new RuntimeException("Unsupported volume preset: {$this->context->preset}");
		}

		$preset = $presets[$this->context->preset];
		if (empty($preset['randomize']))
		{
			return $preset;
		}

		mt_srand($this->context->seed);
		$users = mt_rand(1, $preset['users']);
		return [
			'users' => $users,
			'categories' => mt_rand(1, $preset['categories']),
			'forums_per_category' => mt_rand(1, $preset['forums_per_category']),
			'topics' => mt_rand(1, $preset['topics']),
			'replies' => mt_rand(0, $preset['replies']),
			'groups' => $users >= 5,
		];
	}

	private function seedUsers(int $count, bool $groups): array
	{
		$db = $this->context->db;
		$passwords = $this->context->container->get('passwords.manager');
		$registered = $this->context->groupId('REGISTERED');
		if (!$registered)
		{
			throw new RuntimeException('Required phpBB group is unavailable: REGISTERED');
		}

		$users = [];
		$slug = str_replace('-', '_', $this->context->preset);
		for ($index = 1; $index <= $count; $index++)
		{
			$username = sprintf('qi_%s_%d_user_%03d', $slug, $this->context->seed, $index);
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
				throw new RuntimeException("Unable to create seed user: $username");
			}
			$this->context->addId('users', $userId);
			$users[] = $userId;
		}

		$posters = $users;
		if ($groups && count($users) >= 5)
		{
			$moderators = array_slice($users, 0, 2);
			$newlyRegistered = array_slice($users, -2);
			$moderatorGroup = $this->context->groupId('GLOBAL_MODERATORS');
			$newUserGroup = $this->context->groupId('NEWLY_REGISTERED');
			if ($moderatorGroup)
			{
				group_user_add($moderatorGroup, $moderators, false, false, true);
			}
			if ($newUserGroup)
			{
				group_user_add($newUserGroup, $newlyRegistered);
			}
			$excluded = array_fill_keys($newlyRegistered, true);
			$posters = array_values(array_filter($users, static function ($userId) use ($excluded) {
				return !isset($excluded[$userId]);
			}));
		}

		return $posters ?: $users;
	}

	private function seedForums(int $categoryCount, int $forumsPerCategory): array
	{
		if (!class_exists('acp_forums'))
		{
			require_once $this->context->root . 'includes/acp/acp_forums.' . $this->context->phpEx;
		}
		$permissionSource = $this->firstPostableForum();
		if (!$permissionSource)
		{
			throw new RuntimeException('Seed preset requires an existing postable forum.');
		}

		$acp = new \acp_forums();
		$forums = [];
		for ($category = 1; $category <= $categoryCount; $category++)
		{
			$categoryId = $this->createForum([
				'parent_id' => 0,
				'forum_type' => FORUM_CAT,
				'forum_name' => sprintf('%sCategory %02d', $this->context->prefix, $category),
				'forum_desc' => sprintf('Category %d generated by the %s preset.', $category, $this->context->preset),
			], $acp, $permissionSource, 'categories');

			for ($forum = 1; $forum <= $forumsPerCategory; $forum++)
			{
				$forums[] = $this->createForum([
					'parent_id' => $categoryId,
					'forum_type' => FORUM_POST,
					'forum_name' => sprintf('%sForum %02d-%02d', $this->context->prefix, $category, $forum),
					'forum_desc' => sprintf('Forum %d in category %d generated by the %s preset.', $forum, $category, $this->context->preset),
				], $acp, $permissionSource, 'forums');
			}
		}
		$this->context->auth->acl_clear_prefetch();
		return $forums;
	}

	private function createForum(array $data, \acp_forums $acp, int $permissionSource, string $manifestType): int
	{
		$db = $this->context->db;
		$result = $db->sql_query_limit(
			'SELECT forum_id FROM ' . FORUMS_TABLE . "
				WHERE forum_name = '" . $db->sql_escape($data['forum_name']) . "'",
			1
		);
		$id = (int) $db->sql_fetchfield('forum_id');
		$db->sql_freeresult($result);
		if (!$id)
		{
			$forumData = array_merge([
				'parent_id' => 0,
				'forum_type' => FORUM_POST,
				'forum_status' => ITEM_UNLOCKED,
				'forum_parents' => '',
				'forum_options' => 0,
				'forum_name' => '',
				'forum_link' => '',
				'forum_link_track' => false,
				'forum_desc' => '',
				'forum_desc_uid' => '',
				'forum_desc_options' => 7,
				'forum_desc_bitfield' => '',
				'forum_rules' => '',
				'forum_rules_uid' => '',
				'forum_rules_options' => 7,
				'forum_rules_bitfield' => '',
				'forum_rules_link' => '',
				'forum_image' => '',
				'forum_style' => 0,
				'forum_password' => '',
				'forum_password_confirm' => '',
				'display_subforum_list' => true,
				'display_on_index' => true,
				'forum_topics_per_page' => 0,
				'enable_indexing' => true,
				'enable_icons' => true,
				'enable_prune' => false,
				'enable_post_review' => true,
				'enable_quick_reply' => true,
				'prune_days' => 7,
				'prune_viewed' => 7,
				'prune_freq' => 1,
				'prune_old_polls' => false,
				'prune_announce' => false,
				'prune_sticky' => false,
				'forum_password_unset' => false,
				'show_active' => 1,
			], $data);
			generate_text_for_storage(
				$forumData['forum_desc'],
				$forumData['forum_desc_uid'],
				$forumData['forum_desc_bitfield'],
				$forumData['forum_desc_options'],
				false,
				false,
				false
			);
			$errors = $acp->update_forum_data($forumData);
			if ($errors)
			{
				throw new RuntimeException('Unable to create seed forum: ' . implode('; ', $errors));
			}
			$id = (int) $forumData['forum_id'];
			copy_forum_permissions($permissionSource, $id);
		}
		$this->context->addId($manifestType, $id);
		return $id;
	}

	private function seedPosts(array $forums, array $users, int $topicCount, int $replyCount): void
	{
		if (!$forums || !$users)
		{
			throw new RuntimeException('Seed preset requires forums and posting users.');
		}

		for ($topic = 1; $topic <= $topicCount; $topic++)
		{
			$forumId = $forums[($topic - 1) % count($forums)];
			$userId = $users[($topic - 1) % count($users)];
			$label = sprintf('Topic %03d', $topic);
			$subject = $this->context->prefix . $label;
			$this->context->switchUser($userId);
			$data = $this->postData(
				$forumId,
				0,
				$subject,
				sprintf('This is topic %d generated by the %s preset.', $topic, $this->context->preset)
			);
			$poll = [];
			submit_post('post', $subject, $this->context->user->data['username'], POST_NORMAL, $poll, $data);
			$topicId = (int) ($data['topic_id'] ?? 0);
			$postId = (int) ($data['post_id'] ?? 0);
			if (!$topicId || !$postId)
			{
				throw new RuntimeException("Unable to create seed topic: $subject");
			}
			$this->context->addId('topics', $topicId);
			$this->context->addId('posts', $postId);

			for ($reply = 1; $reply <= $replyCount; $reply++)
			{
				$replyUserId = $users[($topic + $reply - 1) % count($users)];
				$replySubject = sprintf('%sRe: %s — reply %02d', $this->context->prefix, $label, $reply);
				$this->context->switchUser($replyUserId);
				$replyData = $this->postData(
					$forumId,
					$topicId,
					$replySubject,
					sprintf('This is reply %d of %d in topic %d.', $reply, $replyCount, $topic)
				);
				submit_post('reply', $replySubject, $this->context->user->data['username'], POST_NORMAL, $poll, $replyData);
				$replyId = (int) ($replyData['post_id'] ?? 0);
				if (!$replyId)
				{
					throw new RuntimeException("Unable to create seed reply: $replySubject");
				}
				$this->context->addId('posts', $replyId);
			}

			if ($topic % 10 === 0)
			{
				$this->context->saveManifest();
				$GLOBALS['cache']->purge();
				gc_collect_cycles();
			}
		}
		$this->context->restoreUser();
	}

	private function postData(int $forumId, int $topicId, string $subject, string $message): array
	{
		$uid = $bitfield = '';
		$options = 7;
		generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);
		$result = $this->context->db->sql_query_limit(
			'SELECT forum_name FROM ' . FORUMS_TABLE . ' WHERE forum_id = ' . $forumId,
			1
		);
		$forumName = (string) $this->context->db->sql_fetchfield('forum_name');
		$this->context->db->sql_freeresult($result);

		return [
			'forum_id' => $forumId,
			'topic_id' => $topicId,
			'icon_id' => 0,
			'topic_title' => $subject,
			'topic_time_limit' => 0,
			'poster_id' => (int) $this->context->user->data['user_id'],
			'enable_bbcode' => true,
			'enable_smilies' => true,
			'enable_urls' => true,
			'enable_sig' => true,
			'message' => $message,
			'message_md5' => md5($message),
			'bbcode_bitfield' => $bitfield,
			'bbcode_uid' => $uid,
			'post_edit_locked' => 0,
			'notify_set' => false,
			'notify' => false,
			'post_time' => time(),
			'forum_name' => $forumName,
			'enable_indexing' => true,
			'force_approved_state' => true,
		];
	}

	private function firstPostableForum(): int
	{
		$result = $this->context->db->sql_query_limit(
			'SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_type = ' . FORUM_POST . ' ORDER BY forum_id ASC',
			1
		);
		$id = (int) $this->context->db->sql_fetchfield('forum_id');
		$this->context->db->sql_freeresult($result);
		return $id;
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

	private function plural(int $count, string $singular, string $plural): string
	{
		return $count === 1 ? $singular : $plural;
	}
}
