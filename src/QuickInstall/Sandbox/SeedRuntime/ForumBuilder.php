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

class ForumBuilder extends BaseBuilder
{
	public function seed(): array
	{
		$db = $this->context->db;
		if (!class_exists('acp_forums'))
		{
			require_once $this->context->root . 'includes/acp/acp_forums.' . $this->context->phpEx;
		}

		$defaultForum = $this->firstForum(FORUM_POST);
		if (!$defaultForum)
		{
			throw new RuntimeException('Development preset requires an existing postable forum.');
		}

		$acp = new \acp_forums();
		$forums = ['news' => $defaultForum];
		$forums['lobby'] = $this->create([
			'parent_id' => 0,
			'forum_type' => FORUM_POST,
			'forum_name' => $this->context->prefix . 'Lobby',
			'forum_desc' => 'A regular forum with topics, replies, rules, and a mix of post types.',
		], $acp, $defaultForum, 'forums');

		$categories = [
			'Forum states' => [
				'read' => ['Read forum', 'This forum is marked as read for the board founder.'],
				'unread' => ['Unread forum', 'This forum contains unread posts for the board founder.'],
			],
			'Nested forums' => [
				'parent' => ['Parent forum', 'This forum contains two child forums.'],
			],
			'Locked forums' => [
				'locked_forum' => ['Locked forum', 'You can read this forum, but you cannot start topics or post replies.', 'status' => ITEM_LOCKED],
			],
			'Special forums' => [
				'link' => ['Link forum', 'Opening this forum takes you to phpBB.com.', 'type' => FORUM_LINK, 'link' => 'https://www.phpbb.com/'],
				'password' => ['Password-protected forum', 'This forum requires a password. Password: password', 'password' => SeedContext::PASSWORD],
				'private' => ['Private forum', 'Only members with the right permissions can see this forum.'],
				'empty' => ['Empty forum', 'This forum intentionally contains no topics.'],
				'icon' => ['Forum with icon', 'This forum displays a generated image beside its name.', 'image' => $this->forumIcon()],
			],
		];
		$categoryDescriptions = [
			'Forum states' => 'Forums showing read and unread states.',
			'Nested forums' => 'A parent forum with two child forums.',
			'Locked forums' => 'A forum where new topics and replies are disabled.',
			'Special forums' => 'Forums with links, passwords, permissions, icons, or no topics.',
		];

		foreach ($categories as $categoryName => $definitions)
		{
			$categoryId = $this->create([
				'parent_id' => 0,
				'forum_type' => FORUM_CAT,
				'forum_name' => $this->context->prefix . $categoryName,
				'forum_desc' => $categoryDescriptions[$categoryName],
			], $acp, $defaultForum, 'categories');

			foreach ($definitions as $key => $definition)
			{
				$data = [
					'parent_id' => $categoryId,
					'forum_type' => $definition['type'] ?? FORUM_POST,
					'forum_name' => $this->context->prefix . $definition[0],
					'forum_desc' => $definition[1],
				];
				if (isset($definition['status']))
				{
					$data['forum_status'] = $definition['status'];
				}
				if (isset($definition['link']))
				{
					$data['forum_link'] = $definition['link'];
				}
				if (isset($definition['password']))
				{
					$data['forum_password'] = $definition['password'];
					$data['forum_password_confirm'] = $definition['password'];
				}
				if (isset($definition['image']))
				{
					$data['forum_image'] = $definition['image'];
				}
				$forums[$key] = $this->create($data, $acp, $defaultForum, 'forums');
			}
		}

		$forums['child_a'] = $this->create([
			'parent_id' => $forums['parent'],
			'forum_type' => FORUM_POST,
			'forum_name' => $this->context->prefix . 'Child subforum A',
			'forum_desc' => 'The first child forum inside the parent forum.',
		], $acp, $defaultForum, 'forums');
		$forums['child_b'] = $this->create([
			'parent_id' => $forums['parent'],
			'forum_type' => FORUM_POST,
			'forum_name' => $this->context->prefix . 'Child subforum B',
			'forum_desc' => 'The second child forum inside the parent forum.',
		], $acp, $defaultForum, 'forums');

		$this->makePrivate($forums['private']);
		$this->addRules([$forums['lobby'], $forums['read'], $forums['unread']]);
		$this->markRead($forums['read']);
		$this->context->setMeta('forums', $forums);
		$this->context->auth->acl_clear_prefetch();
		return $forums;
	}

	public function seedVolume(int $categoryCount, int $forumsPerCategory): array
	{
		if (!class_exists('acp_forums'))
		{
			require_once $this->context->root . 'includes/acp/acp_forums.' . $this->context->phpEx;
		}
		$permissionSource = $this->firstForum(FORUM_POST);
		if (!$permissionSource)
		{
			throw new RuntimeException('Seed preset requires an existing postable forum.');
		}

		$acp = new \acp_forums();
		$forums = [];
		for ($category = 1; $category <= $categoryCount; $category++)
		{
			$categoryId = $this->create([
				'parent_id' => 0,
				'forum_type' => FORUM_CAT,
				'forum_name' => sprintf('%sCategory %02d', $this->context->prefix, $category),
				'forum_desc' => sprintf('Category %d generated by the %s preset.', $category, $this->context->preset),
			], $acp, $permissionSource, 'categories');

			for ($forum = 1; $forum <= $forumsPerCategory; $forum++)
			{
				$forums[] = $this->create([
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

	private function firstForum(int $type): int
	{
		$result = $this->context->db->sql_query_limit(
			'SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_type = ' . $type . ' ORDER BY forum_id ASC',
			1
		);
		$id = (int) $this->context->db->sql_fetchfield('forum_id');
		$this->context->db->sql_freeresult($result);
		return $id;
	}

	private function create(array $data, \acp_forums $acp, int $permissionSource, string $manifestType): int
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

	private function forumIcon(): string
	{
		$relative = 'images/qi-development-' . $this->context->seed . '-forum.png';
		$path = $this->context->root . $relative;
		if (file_put_contents($path, AssetFactory::png(96, 96, $this->context->seed + 9000), LOCK_EX) === false)
		{
			throw new RuntimeException("Unable to write generated forum icon: $path");
		}
		$this->context->addFile($path);
		return $relative;
	}

	private function makePrivate(int $forumId): void
	{
		$groupIds = array_filter([
			$this->context->groupId('GUESTS'),
			$this->context->groupId('REGISTERED'),
			$this->context->groupId('NEWLY_REGISTERED'),
		]);
		if ($groupIds)
		{
			$this->context->db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . '
				WHERE forum_id = ' . $forumId . '
					AND ' . $this->context->db->sql_in_set('group_id', $groupIds));
		}
	}

	private function addRules(array $forumIds): void
	{
		$db = $this->context->db;
		$text = '[b]Sample forum rules[/b] Be kind, stay on topic, and read the [url=https://www.phpbb.com/rules/]full rules[/url] before posting.';
		$uid = $bitfield = '';
		$options = 7;
		generate_text_for_storage($text, $uid, $bitfield, $options, true, true, true);
		foreach ($forumIds as $forumId)
		{
			$db->sql_query('UPDATE ' . FORUMS_TABLE . "
				SET forum_rules = '" . $db->sql_escape($text) . "',
					forum_rules_uid = '" . $db->sql_escape($uid) . "',
					forum_rules_bitfield = '" . $db->sql_escape($bitfield) . "',
					forum_rules_options = $options
				WHERE forum_id = " . (int) $forumId);
		}
	}

	private function markRead(int $forumId): void
	{
		$db = $this->context->db;
		$founderId = $this->context->founderId();
		$db->sql_query('DELETE FROM ' . FORUMS_TRACK_TABLE . '
			WHERE user_id = ' . $founderId . ' AND forum_id = ' . $forumId);
		$db->sql_query('INSERT INTO ' . FORUMS_TRACK_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'user_id' => $founderId,
			'forum_id' => $forumId,
			'mark_time' => time() + 3600,
		]));
	}
}
