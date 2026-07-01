<?php

namespace QuickInstall\Sandbox;

class SeederWriter
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function write(string $name): string
	{
		$path = $this->project->runtimePath($name) . '/seed.php';
		if (file_put_contents($path, $this->script()) === false)
		{
			throw new \RuntimeException("Unable to write $path");
		}

		return $path;
	}

	private function script(): string
	{
		return <<<'PHP'
<?php

$preset = $argv[1] ?? 'extension-dev';
$seed = (int) ($argv[2] ?? 1);
$action = $argv[3] ?? 'seed';

$presets = [
	'tiny' => ['users' => 3, 'categories' => 1, 'forums_per_category' => 2, 'topics' => 2, 'replies' => 2],
	'extension-dev' => ['users' => 10, 'categories' => 2, 'forums_per_category' => 3, 'topics' => 25, 'replies' => 10],
	'load-test' => ['users' => 100, 'categories' => 4, 'forums_per_category' => 5, 'topics' => 100, 'replies' => 20],
	'random' => ['users' => 100, 'categories' => 4, 'forums_per_category' => 5, 'topics' => 100, 'replies' => 20, 'randomize' => true],
];

if (!isset($presets[$preset]))
{
	fwrite(STDERR, "Unknown seed preset: $preset\n");
	exit(1);
}

if (!in_array($action, ['seed', 'reset', 'replace'], true))
{
	fwrite(STDERR, "Unknown seed action: $action\n");
	exit(1);
}

$phpbb_root_path = '/var/www/html/';
$phpEx = 'php';
define('IN_PHPBB', true);

require_once $phpbb_root_path . 'common.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_content.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_posting.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_admin.' . $phpEx;

$user->session_begin();
$auth->acl($user->data);
$user->setup();

$admin_id = 2;
$user->data['user_id'] = $admin_id;
$user->data['username'] = 'admin';
$user->data['is_registered'] = true;

mt_srand($seed);
$counts = qi_seed_resolve_counts($presets[$preset]);

if ($action === 'reset' || $action === 'replace')
{
	$reset = qi_seed_reset($db, $seed);
	echo "Reset seed $seed: {$reset['topics']} topics, {$reset['forums']} forums, {$reset['users']} users\n";
	if ($action === 'reset')
	{
		qi_seed_sync_user_post_counts($db);
		exit(0);
	}
}

$users = qi_seed_users($db, $phpbb_container, $counts['users'], $seed);
$forums = qi_seed_forums($db, $counts['categories'], $counts['forums_per_category'], $seed);
if (!$forums)
{
	$forum_id = qi_seed_first_postable_forum($db);
	$forums = $forum_id ? [$forum_id] : [];
}

if (!$forums)
{
	fwrite(STDERR, "No postable forum found\n");
	exit(1);
}

$created_topics = qi_seed_posts($forums, $users, $counts['topics'], $counts['replies'], $seed);
qi_seed_mark_posts_counted($db, $seed);
qi_seed_sync_user_post_counts($db);

echo "Seeded preset $preset: " . count($users) . " users available, " . count($forums) . " forums available, $created_topics topics\n";

function qi_seed_reset($db, int $seed): array
{
	$topic_ids = qi_seed_ids_by_like($db, TOPICS_TABLE, 'topic_id', 'topic_title', sprintf('QI seeded topic %d-', $seed) . '%');
	$forum_ids = qi_seed_ids_by_like($db, FORUMS_TABLE, 'forum_id', 'forum_name', sprintf('QI seed %d ', $seed) . '%');
	$user_ids = qi_seed_ids_by_like($db, USERS_TABLE, 'user_id', 'username', sprintf('qi_user_%d_', $seed) . '%');

	if ($topic_ids)
	{
		delete_topics('topic_id', $topic_ids, true, true, true);
	}

	if ($forum_ids)
	{
		$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));
		$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));
		$db->sql_query('DELETE FROM ' . FORUMS_TRACK_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));
		$db->sql_query('DELETE FROM ' . FORUMS_WATCH_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));
		$db->sql_query('DELETE FROM ' . FORUMS_ACCESS_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));
		$db->sql_query('DELETE FROM ' . FORUMS_TABLE . ' WHERE ' . $db->sql_in_set('forum_id', $forum_ids));

		$new_id = 1;
		recalc_nested_sets($new_id, 'forum_id', FORUMS_TABLE);
	}

	if ($user_ids)
	{
		user_delete('remove', $user_ids);
	}

	qi_seed_sync_user_post_counts($db);
	qi_seed_clear_caches();

	return [
		'topics' => count($topic_ids),
		'forums' => count($forum_ids),
		'users' => count($user_ids),
	];
}

function qi_seed_ids_by_like($db, string $table, string $id_column, string $text_column, string $pattern): array
{
	$sql = "SELECT $id_column
		FROM $table
		WHERE $text_column LIKE '" . $db->sql_escape($pattern) . "'";
	$result = $db->sql_query($sql);
	$ids = [];
	while ($row = $db->sql_fetchrow($result))
	{
		$ids[] = (int) $row[$id_column];
	}
	$db->sql_freeresult($result);

	return $ids;
}

function qi_seed_sync_user_post_counts($db): void
{
	$db->sql_query('UPDATE ' . USERS_TABLE . '
		SET user_posts = 0');

	$sql = 'SELECT poster_id, COUNT(post_id) AS post_count
		FROM ' . POSTS_TABLE . '
		WHERE poster_id > 1
			AND post_postcount = 1
			AND post_visibility = ' . ITEM_APPROVED . '
		GROUP BY poster_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . '
			SET user_posts = ' . (int) $row['post_count'] . '
			WHERE user_id = ' . (int) $row['poster_id']);
	}
	$db->sql_freeresult($result);
}

function qi_seed_mark_posts_counted($db, int $seed): void
{
	$pattern = sprintf('%%QI seeded topic %d-%%', $seed);
	$db->sql_query('UPDATE ' . POSTS_TABLE . '
		SET post_postcount = 1
		WHERE post_subject LIKE \'' . $db->sql_escape($pattern) . '\'');
}

function qi_seed_resolve_counts(array $preset): array
{
	if (empty($preset['randomize']))
	{
		return $preset;
	}

	return [
		'users' => mt_rand(1, $preset['users']),
		'categories' => mt_rand(1, $preset['categories']),
		'forums_per_category' => mt_rand(1, $preset['forums_per_category']),
		'topics' => mt_rand(1, $preset['topics']),
		'replies' => mt_rand(0, $preset['replies']),
	];
}

function qi_seed_first_postable_forum($db): int
{
	$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_type = ' . FORUM_POST . ' ORDER BY forum_id ASC';
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	return $row ? (int) $row['forum_id'] : 0;
}

function qi_seed_users($db, $phpbb_container, int $count, int $seed): array
{
	$users = [];
	$passwords = $phpbb_container->get('passwords.manager');

	for ($i = 1; $i <= $count; $i++)
	{
		$username = sprintf('qi_user_%d_%02d', $seed, $i);
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $db->sql_query_limit($sql, 1);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{
			$users[] = [
				'user_id' => (int) $row['user_id'],
				'username' => $username,
				'user_colour' => '',
			];
			continue;
		}

		$user_id = user_add([
			'username' => $username,
			'user_password' => $passwords->hash('password'),
			'user_email' => sprintf('%s@example.test', $username),
			'group_id' => 2,
			'user_type' => USER_NORMAL,
			'user_ip' => '127.0.0.1',
			'user_lang' => 'en',
		]);

		if ($user_id !== false)
		{
			$users[] = [
				'user_id' => (int) $user_id,
				'username' => $username,
				'user_colour' => '',
			];
		}
	}

	return $users ?: [[
		'user_id' => 2,
		'username' => 'admin',
		'user_colour' => 'AA0000',
	]];
}

function qi_seed_forums($db, int $categories, int $forums_per_category, int $seed): array
{
	$forum_ids = [];
	$category_ids = [];
	$permission_source = qi_seed_first_postable_forum($db);

	for ($category = 1; $category <= $categories; $category++)
	{
		$category_name = sprintf('QI seed %d category %02d', $seed, $category);
		$category_id = qi_seed_forum_id_by_name($db, $category_name);
		if (!$category_id)
		{
			$category_id = qi_seed_insert_forum($db, [
				'parent_id' => 0,
				'forum_type' => FORUM_CAT,
				'forum_name' => $category_name,
				'forum_desc' => sprintf('Generated category for seed %d.', $seed),
			]);
		}
		$category_ids[] = $category_id;

		for ($forum = 1; $forum <= $forums_per_category; $forum++)
		{
			$forum_name = sprintf('QI seed %d forum %02d-%02d', $seed, $category, $forum);
			$forum_id = qi_seed_forum_id_by_name($db, $forum_name);
			if (!$forum_id)
			{
				$forum_id = qi_seed_insert_forum($db, [
					'parent_id' => $category_id,
					'forum_type' => FORUM_POST,
					'forum_name' => $forum_name,
					'forum_desc' => sprintf('Generated forum %02d in category %02d.', $forum, $category),
				]);
			}

			$forum_ids[] = $forum_id;
		}
	}

	$new_id = 1;
	recalc_nested_sets($new_id, 'forum_id', FORUMS_TABLE);

	$forum_ids = array_values(array_unique(array_map('intval', $forum_ids)));
	$permission_targets = array_values(array_unique(array_merge(array_map('intval', $category_ids), $forum_ids)));
	if ($permission_source && $permission_targets)
	{
		qi_seed_copy_forum_permissions($db, $permission_source, $permission_targets);
	}

	qi_seed_clear_caches();

	return $forum_ids;
}

function qi_seed_copy_forum_permissions($db, int $source_forum_id, array $target_forum_ids): void
{
	foreach ($target_forum_ids as $target_forum_id)
	{
		$target_forum_id = (int) $target_forum_id;
		if ($target_forum_id === $source_forum_id)
		{
			continue;
		}

		$db->sql_query('DELETE FROM ' . ACL_GROUPS_TABLE . ' WHERE forum_id = ' . $target_forum_id);
		$db->sql_query('DELETE FROM ' . ACL_USERS_TABLE . ' WHERE forum_id = ' . $target_forum_id);

		qi_seed_copy_acl_rows($db, ACL_GROUPS_TABLE, $source_forum_id, $target_forum_id);
		qi_seed_copy_acl_rows($db, ACL_USERS_TABLE, $source_forum_id, $target_forum_id);
	}
}

function qi_seed_copy_acl_rows($db, string $table, int $source_forum_id, int $target_forum_id): void
{
	$result = $db->sql_query('SELECT * FROM ' . $table . ' WHERE forum_id = ' . $source_forum_id);
	while ($row = $db->sql_fetchrow($result))
	{
		$row['forum_id'] = $target_forum_id;
		$db->sql_query('INSERT INTO ' . $table . ' ' . $db->sql_build_array('INSERT', $row));
	}
	$db->sql_freeresult($result);
}

function qi_seed_clear_caches(): void
{
	global $auth, $cache;

	if (is_object($auth) && method_exists($auth, 'acl_clear_prefetch'))
	{
		$auth->acl_clear_prefetch();
	}

	if (is_object($cache) && method_exists($cache, 'purge'))
	{
		$cache->purge();
	}
}

function qi_seed_forum_id_by_name($db, string $name): int
{
	$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . " WHERE forum_name = '" . $db->sql_escape($name) . "'";
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	return $row ? (int) $row['forum_id'] : 0;
}

function qi_seed_insert_forum($db, array $data): int
{
	$desc = $data['forum_desc'];
	$desc_uid = $desc_bitfield = '';
	$desc_options = 7;
	generate_text_for_storage($desc, $desc_uid, $desc_bitfield, $desc_options, true, true, true);

	$sql_ary = [
		'parent_id' => (int) $data['parent_id'],
		'left_id' => 0,
		'right_id' => 0,
		'forum_parents' => '',
		'forum_name' => $data['forum_name'],
		'forum_desc' => $desc,
		'forum_desc_bitfield' => $desc_bitfield,
		'forum_desc_options' => $desc_options,
		'forum_desc_uid' => $desc_uid,
		'forum_link' => '',
		'forum_password' => '',
		'forum_image' => '',
		'forum_rules' => '',
		'forum_rules_link' => '',
		'forum_rules_bitfield' => '',
		'forum_rules_options' => 7,
		'forum_rules_uid' => '',
		'forum_type' => (int) $data['forum_type'],
		'forum_status' => ITEM_UNLOCKED,
		'forum_flags' => 48,
		'display_on_index' => 1,
		'enable_indexing' => 1,
		'enable_icons' => 1,
		'display_subforum_list' => 1,
	];

	$db->sql_query('INSERT INTO ' . FORUMS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
	return (int) $db->sql_nextid();
}

function qi_seed_posts(array $forum_ids, array $authors, int $topics, int $replies, int $seed): int
{
	$created = 0;
	$existing = qi_seed_existing_topic_count($seed);

	for ($topic = $existing + 1; $topic <= $topics; $topic++)
	{
		$forum_id = $forum_ids[array_rand($forum_ids)];
		$author = $authors[array_rand($authors)];
		$topic_id_hint = qi_seed_next_topic_id();
		$subject = sprintf('QI seeded topic %d-%02d', $seed, $topic_id_hint);

		qi_seed_set_author($author);
		$data = qi_seed_post_data($forum_id, 0, $subject, sprintf("Seeded topic body %d.%02d\n\nUseful test content for extension development.", $seed, $topic_id_hint));
		$poll = [];
		submit_post('post', $subject, '', POST_NORMAL, $poll, $data, true, true);
		$created++;

		$topic_id = (int) ($data['topic_id'] ?? 0);
		if (!$topic_id)
		{
			continue;
		}

		for ($reply = 1; $reply <= $replies; $reply++)
		{
			$reply_author = $authors[array_rand($authors)];
			$reply_subject = 'Re: ' . $subject;
			qi_seed_set_author($reply_author);
			$reply_data = qi_seed_post_data($forum_id, $topic_id, $subject, sprintf('Seeded reply %d for topic %d.', $reply, $topic_id_hint));
			$reply_poll = [];
			submit_post('reply', $reply_subject, '', POST_NORMAL, $reply_poll, $reply_data, true, true);
		}
	}

	return $created;
}

function qi_seed_existing_topic_count(int $seed): int
{
	global $db;

	$sql = 'SELECT COUNT(topic_id) AS topic_count
		FROM ' . TOPICS_TABLE . "
		WHERE topic_title LIKE '" . $db->sql_escape(sprintf('QI seeded topic %d-', $seed)) . "%'";
	$result = $db->sql_query($sql);
	$count = (int) $db->sql_fetchfield('topic_count');
	$db->sql_freeresult($result);

	return $count;
}

function qi_seed_next_topic_id(): int
{
	global $db;

	$sql = 'SELECT MAX(topic_id) AS max_topic_id
		FROM ' . TOPICS_TABLE;
	$result = $db->sql_query($sql);
	$next_id = (int) $db->sql_fetchfield('max_topic_id') + 1;
	$db->sql_freeresult($result);

	return max(1, $next_id);
}

function qi_seed_set_author(array $author): void
{
	global $user;

	$user->data['user_id'] = (int) $author['user_id'];
	$user->data['username'] = $author['username'];
	$user->data['username_clean'] = utf8_clean_string($author['username']);
	$user->data['user_colour'] = $author['user_colour'] ?? '';
	$user->data['is_registered'] = true;
}

function qi_seed_post_data(int $forum_id, int $topic_id, string $topic_title, string $message): array
{
	global $user;

	$uid = $bitfield = '';
	$options = 0;
	generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);

	return [
		'forum_id' => $forum_id,
		'topic_id' => $topic_id,
		'icon_id' => 0,
		'topic_title' => $topic_title,
		'topic_time_limit' => 0,
		'poster_id' => (int) $user->data['user_id'],
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
		'forum_name' => '',
		'enable_indexing' => true,
		'force_approved_state' => true,
	];
}
PHP;
	}
}
