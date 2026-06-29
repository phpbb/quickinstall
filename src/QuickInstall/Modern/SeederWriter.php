<?php

namespace QuickInstall\Modern;

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

$presets = [
	'tiny' => ['users' => 3, 'topics' => 2, 'replies' => 2],
	'extension-dev' => ['users' => 10, 'topics' => 5, 'replies' => 4],
	'load-test' => ['users' => 50, 'topics' => 25, 'replies' => 10],
];

if (!isset($presets[$preset]))
{
	fwrite(STDERR, "Unknown seed preset: $preset\n");
	exit(1);
}

$phpbb_root_path = '/var/www/html/';
$phpEx = 'php';
define('IN_PHPBB', true);

require_once $phpbb_root_path . 'common.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_user.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_content.' . $phpEx;
require_once $phpbb_root_path . 'includes/functions_posting.' . $phpEx;

$user->session_begin();
$auth->acl($user->data);
$user->setup();

$admin_id = 2;
$user->data['user_id'] = $admin_id;
$user->data['username'] = 'admin';
$user->data['is_registered'] = true;

$forum_id = qi_seed_first_postable_forum($db);
if (!$forum_id)
{
	fwrite(STDERR, "No postable forum found\n");
	exit(1);
}

$counts = $presets[$preset];
mt_srand($seed);

$created_users = qi_seed_users($db, $phpbb_container, $counts['users'], $seed);
$created_topics = qi_seed_posts($forum_id, $counts['topics'], $counts['replies'], $seed);

echo "Seeded preset $preset: $created_users users, $created_topics topics\n";

function qi_seed_first_postable_forum($db): int
{
	$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . ' WHERE forum_type = ' . FORUM_POST . ' ORDER BY forum_id ASC';
	$result = $db->sql_query_limit($sql, 1);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);

	return $row ? (int) $row['forum_id'] : 0;
}

function qi_seed_users($db, $phpbb_container, int $count, int $seed): int
{
	$created = 0;
	$passwords = $phpbb_container->get('passwords.manager');

	for ($i = 1; $i <= $count; $i++)
	{
		$username = sprintf('qi_user_%d_%02d', $seed, $i);
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . " WHERE username_clean = '" . $db->sql_escape(utf8_clean_string($username)) . "'";
		$result = $db->sql_query_limit($sql, 1);
		$exists = (bool) $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($exists)
		{
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
			$created++;
		}
	}

	return $created;
}

function qi_seed_posts(int $forum_id, int $topics, int $replies, int $seed): int
{
	$created = 0;

	for ($topic = 1; $topic <= $topics; $topic++)
	{
		$subject = sprintf('QI seeded topic %d-%02d', $seed, $topic);
		$data = qi_seed_post_data($forum_id, 0, $subject, sprintf("Seeded topic body %d.%02d\n\nUseful test content for extension development.", $seed, $topic));
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
			$reply_subject = 'Re: ' . $subject;
			$reply_data = qi_seed_post_data($forum_id, $topic_id, $subject, sprintf('Seeded reply %d for topic %d.', $reply, $topic));
			$reply_poll = [];
			submit_post('reply', $reply_subject, '', POST_NORMAL, $reply_poll, $reply_data, true, true);
		}
	}

	return $created;
}

function qi_seed_post_data(int $forum_id, int $topic_id, string $topic_title, string $message): array
{
	$uid = $bitfield = '';
	$options = 0;
	generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);

	return [
		'forum_id' => $forum_id,
		'topic_id' => $topic_id,
		'icon_id' => 0,
		'topic_title' => $topic_title,
		'topic_time_limit' => 0,
		'poster_id' => 2,
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
