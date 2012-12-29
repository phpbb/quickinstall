<?php
/**
*
* @package quickinstall
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_QUICKINSTALL'))
{
	exit;
}

class populate
{
	// Populate settings
	private $create_mod = false;
	private $create_admin = false;
	private $num_users = 0;
	private $num_new_group = 0;
	private $num_cats = 0;
	private $num_forums = 0;
	private $num_topics_min = 0;
	private $num_topics_max = 0;
	private $num_replies_min = 0;
	private $num_replies_max = 0;
	private $email_domain = '';

	// We can't have all posts posted in the same second.
	private $post_time = 0;

	// How many of each type to send to the db each run
	// Might be better to add some memory checking later.
	private $user_chunks = CHUNK_USER;
	private $post_chunks = CHUNK_POST;
	private $topic_chunks = CHUNK_TOPIC;

	/**
	 * Lorem ipsum, a placeholder for the posts.
	 */
	private $lorem_ipsum = '';

	/**
	 * $user_arr = array(
	 *   (int) $user_id => array(
	 *     'user_ud' => (int) $user_id,
	 *     'user_lastpost_time' => (int) time(),
	 *     'user_lastmark' => (int) time(),
	 *     'user_lastvisit' => (int) time(),
	 *     'user_posts' => (int) $num_posts,
	 *   ),
	 * );
	 */
	private $user_arr = array();

	/**
	 * $forum_arr = array(
	 *     'forum_id' => (int) $forum_id,
	 *     'parent_id' => (int) $cat_id,
	 *     'forum_posts' => (int) $forum_posts_cnt,
	 *     'forum_topics' => (int) $forum_topics_cnt,
	 *     'forum_topics_real' => (int) $forum_topics_real_cnt,
	 *     'forum_last_post_id' => (int) $forum_last_post_id,
	 *     'forum_last_poster_id' => (int) $forum_last_poster_id,
	 *     'forum_last_post_subject' => (string) $forum_last_post_subject,
	 *     'forum_last_post_time' => (int) $forum_last_post_time,
	 *     'forum_last_poster_name' => (string) $forum_last_poster_name,
	 * 	 ),
	 * );
	 */
	private $forum_arr = array();

	/**
	 * $topc_arr = array(
	 *   (int) $topic_id => array(
	 *     'topic_id' => (int) $topic_id,
	 *     'forum_id' => (int) $forum_id,
	 * 	 ),
	 * );
	 */
	private $topc_arr = array();

	// The default forums. To copy permissions from.
	private $def_cat_id = 0;
	private $def_forum_id = 0;

	public function populate()
	{
		global $quickinstall_path, $phpbb_root_path, $phpEx, $settings;

		// Need to include some files.
		if (!function_exists('gen_sort_selects'))
		{
			include($phpbb_root_path . 'includes/functions_content.' . $phpEx);
		}

		if (!class_exists('acp_forums'))
		{
			include($phpbb_root_path . 'includes/acp/acp_forums.' . $phpEx);
		}

		if (!function_exists('recalc_nested_sets'))
		{
			include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
		}

		if (!class_exists('acp_permissions'))
		{
			include($phpbb_root_path . 'includes/acp/acp_permissions.' . $phpEx);
		}

		include($quickinstall_path . 'includes/functions_forum_create.' . $phpEx);

		// Get the chunk sizes. Make sure they are integers and set to something.
		$this->post_chunks	= $settings->get_config('chunk_post', CHUNK_POST);
		$this->topic_chunks	= $settings->get_config('chunk_topic', CHUNK_TOPIC);
		$this->user_chunks	= $settings->get_config('chunk_user', CHUNK_USER);

		// Initiate these. $settings->get_config('', ); //
		$this->create_admin		= $settings->get_config('create_admin', false);
		$this->create_mod		= $settings->get_config('create_mod', false);
		$this->num_users		= $settings->get_config('num_users', 0);
		$this->num_new_group	= $settings->get_config('num_new_group', 0);
		$this->num_cats			= $settings->get_config('num_cats', 0);
		$this->num_forums		= $settings->get_config('num_forums', 0);
		$this->num_topics_min	= $settings->get_config('num_topics_min', 0);
		$this->num_topics_max	= $settings->get_config('num_topics_max', 0);
		$this->num_replies_min	= $settings->get_config('num_replies_min', 0);
		$this->num_replies_max	= $settings->get_config('num_replies_max', 0);

		if (!$this->num_users && !$this->num_forums)
		{
			// Nothing to do.
			return;
		}

		if ($this->num_users)
		{
			// If we have users we also need a e-mail domain.
			$this->email_domain = trim($settings->get_config('email_domain', ''));

			if (empty($this->email_domain))
			{
				trigger_error($user->lang['NEED_EMAIL_DOMAIN'], E_USER_ERROR);
			}

			// Make sure the email domain starts with a @ and is lower case.
			$this->email_domain = ((strpos($this->email_domain, '@') === false) ? '@' : '') . $this->email_domain;
			$this->email_domain = strtolower($this->email_domain);

			// Populate the users array with some initial data.
			// I'm sure there where a reason to split this up to two functions.
			// Need to have a closer look at that later. The second one might need to move.
			$this->pop_user_arr();
		}

		// There is already one forum and one category created. Let's get their data.
		$this->get_default_forums();

		// There is already one category and one forum created.
		$this->num_forums	= ($this->num_forums) ? $this->num_forums - 1 : 0;
		$this->num_cats		= ($this->num_cats) ? $this->num_cats - 1 : 0;

		// We need to create categories and forums first.
		if ($this->num_forums)
		{
			// Don't create any categories if no forums are to be created.
			$this->create_forums();
		}

		// Don't try to fool us.
		$this->num_replies_max	= ($this->num_replies_max >= $this->num_replies_min) ? $this->num_replies_max : $this->num_replies_min;
		$this->num_topics_max	= ($this->num_topics_max >= $this->num_topics_min) ? $this->num_topics_max : $this->num_topics_min;

		// And now those plesky posts.
		if ($this->num_replies_max || $this->num_topics_max)
		{
			// Estimate the number of posts created.
			// Or in reality, calculate the highest possible number and convert to seconds in the past.
			// If one of them is zero this would not be so nice.
			$replies	= ($this->num_replies_max) ? $this->num_replies_max : 1;
			$topics		= ($this->num_topics_max) ? $this->num_topics_max : 1;
			$forums		= ($this->num_forums) ? $this->num_forums : 1;

			$this->post_time	= time() - ($topics * $replies * $forums);
			$this->post_time	= ($this->post_time < 0) ? 0 : $this->post_time;

			include($quickinstall_path . 'includes/lorem_ipsum.' . $phpEx);
			$this->lorem_ipsum = $lorem_ipsum;
			unset($lorem_impsum);

			$this->fill_forums();
		}

		if ($this->num_users)
		{
			$this->save_users();

			if ($this->create_mod || $this->create_admin)
			{
				$this->create_management();
			}
		}
	}

	/**
	 * Make the first two users a admin and a global moderator.
	 */
	private function create_management()
	{
		global $db, $phpbb_root_path, $phpEx, $settings;

		// Don't do anything if there is not enough users.
		$users_needed = 0;
		$users_needed = ($this->create_mod) ? $users_needed + 1 : $users_needed;
		$users_needed = ($this->create_admin) ? $users_needed + 1 : $users_needed;

		if (sizeof($this->user_arr) < $users_needed)
		{
			return;
		}

		$admin_group = $mod_group = 0;

		// Get group id for admins and moderators.
		$sql = 'SELECT group_id, group_name
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'ADMINISTRATORS'
				OR group_name = 'GLOBAL_MODERATORS'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['group_name'] == 'ADMINISTRATORS')
			{
				$admin_group = (int) $row['group_id'];
			}
			else if ($row['group_name'] == 'GLOBAL_MODERATORS')
			{
				$mod_group = (int) $row['group_id'];
			}
		}
		$db->sql_freeresult($result);

		if (file_exists("{$phpbb_root_path}language/" . $settings->get_config('default_lang') . "/common.$phpEx"))
		{
			include("{$phpbb_root_path}language/" . $settings->get_config('default_lang') . "/common.$phpEx");
		}
		else if (file_exists("{$phpbb_root_path}language/en/common.$phpEx"))
		{
			include("{$phpbb_root_path}language/en/common.$phpEx");
		}
		else
		{
			$lang['G_ADMINISTRATORS'] = $lang['G_GLOBAL_MODERATORS'] = '';
		}

		if (!empty($admin_group) && $this->create_admin)
		{
			reset($this->user_arr);
			$user = current($this->user_arr);
			if (!empty($user['user_id']))
			{
				group_user_add($admin_group, $user['user_id'], false, $lang['G_ADMINISTRATORS'], true, 0);
			}
		}

		if (!empty($mod_group) && $this->create_mod)
		{
			next($this->user_arr);

			$user = current($this->user_arr);
			if (!empty($user['user_id']))
			{
				group_user_add($mod_group, $user['user_id'], false,  $lang['G_GLOBAL_MODERATORS'], true, 1);
			}
		}
	}

	/**
	 * Create topics and posts in them.
	 */
	private function fill_forums()
	{
		global $db, $user;

		// Statistics
		$topic_cnt = $post_cnt = 0;

		// There is at least one topic with one post already created.
		$sql = 'SELECT t.topic_id AS t_topic_id, p.post_id FROM ' . TOPICS_TABLE . ' t, ' . POSTS_TABLE . ' p
			ORDER BY t.topic_id DESC, p.post_id DESC';
		$result		= $db->sql_query_limit($sql, 1);
		$row		= $db->sql_fetchrow($result);
		$topic_id	= (int) $row['t_topic_id'];
		$post_id	= (int) $row['post_id'];
		$db->sql_freeresult($result);

		// Put topics and posts in their arrays so they can be sent to the database when the limit is reached.
		$sql_topics = $sql_posts = array();

		// Get the min and max for mt_rand.
		$ary	= end($this->user_arr);
		$mt_max	= (int) key($this->user_arr);
		$ary	= reset($this->user_arr);
		$mt_min	= (int) key($this->user_arr);

		// Flags for BBCodes.
		$flags = 7;
		foreach ($this->forum_arr as &$forum)
		{
			// How many topics in this forum?
			$topics = ($this->num_topics_min == $this->num_topics_max) ? $this->num_topics_max : mt_rand($this->num_topics_min, $this->num_topics_max);

			for ($i = 0; $i < $topics; $i++)
			{
				// Increase this here so we get the number for the topic title.
				$topic_cnt++;

				$topic_arr = array(
					'topic_id'		=> (int) ++$topic_id,
					'forum_id'		=> (int) $forum['forum_id'],
					'topic_title'	=> sprintf($user->lang['TEST_TOPIC_TITLE'], $topic_cnt),
					'topic_replies'			=> 0,
					'topic_replies_real'	=> 0,
				);

				$forum['forum_topics']++;
				$forum['forum_topics_real']++;

				$replies = ($this->num_replies_min == $this->num_replies_max) ? $this->num_replies_max : mt_rand($this->num_replies_min, $this->num_replies_max);
				// The first topic post also needs to be posted.
				$replies++;

				// Generate the posts.
				for ($j = 0; $j < $replies; $j++)
				{
					$post_cnt++;

					$poster_id	= mt_rand($mt_min, $mt_max);
					$poster_arr	= $this->user_arr[$poster_id];
					$post_time	= $this->post_time++;
					$post_text	= sprintf($user->lang['TEST_POST_START'], $post_cnt) . "\n" . $this->lorem_ipsum;
					$subject	= (($j > 0) ? 'Re: ' : '') . $topic_arr['topic_title'];

					$bbcode_uid = $bbcode_bitfield = '';
					generate_text_for_storage($post_text, $bbcode_uid, $bbcode_bitfield, $flags, TRUE, TRUE, TRUE);

					$sql_posts[] = array(
						'post_id'			=> ++$post_id,
						'topic_id'			=> $topic_id,
						'forum_id'			=> $forum['forum_id'],
						'poster_id'			=> $poster_arr['user_id'],
						'post_time'			=> $post_time,
						'post_username'		=> $poster_arr['username'],
						'post_subject'		=> $subject,
						'post_text'			=> $post_text,
						'post_checksum'		=> md5($post_text),
						'bbcode_bitfield'	=> $bbcode_bitfield,
						'bbcode_uid'		=> $bbcode_uid,
					);

					if ($j == 0)
					{
						// Put some first post info to the topic array.
						$topic_arr['topic_first_post_id']	= $post_id;
						$topic_arr['topic_first_poster_name']	= $poster_arr['username'];
						$topic_arr['topic_time']		= $post_time;
						$topic_arr['topic_poster']		= $poster_arr['user_id'];
					}
					else
					{
						$topic_arr['topic_replies']++;
						$topic_arr['topic_replies_real']++;
					}

					$forum['forum_posts']++;
					$forum['forum_last_post_id']		= $post_id;
					$forum['forum_last_poster_id']		= $poster_arr['user_id'];
					$forum['forum_last_post_subject']	= $subject;
					$forum['forum_last_post_time']		= $post_time;
					$forum['forum_last_poster_name']	= $poster_arr['username'];

					$this->user_arr[$poster_arr['user_id']]['user_posts']++;
					$this->user_arr[$poster_arr['user_id']]['user_lastpost_time'] = $post_time;
					$this->user_arr[$poster_arr['user_id']]['user_lastmark'] = $post_time;

					if (sizeof($sql_posts) >= $this->post_chunks)
					{
						// Save the array to the posts table
						$db->sql_multi_insert(POSTS_TABLE, $sql_posts);
						unset($sql_posts);
						$sql_posts = array();
					}
				}

				$topic_arr['topic_last_post_id']		= $post_id;
				$topic_arr['topic_last_poster_id']		= $poster_arr['user_id'];
				$topic_arr['topic_last_poster_name']	= $poster_arr['username'];
				$topic_arr['topic_last_post_subject']	= $subject;
				$topic_arr['topic_last_post_time']		= $post_time;

				$sql_topics[] = $topic_arr;

				if (sizeof($sql_topics) >= $this->topic_chunks)
				{
					// Save the array to the topics table
					$db->sql_multi_insert(TOPICS_TABLE, $sql_topics);
					unset($sql_topics);
					$sql_topics = array();
				}
			}

			$sql_ary = array(
				'forum_posts'				=> $forum['forum_posts'],
				'forum_topics'				=> $forum['forum_topics'],
				'forum_topics_real'			=> $forum['forum_topics_real'],
				'forum_last_post_id'		=> $forum['forum_last_post_id'],
				'forum_last_poster_id'		=> $forum['forum_last_poster_id'],
				'forum_last_post_subject'	=> $forum['forum_last_post_subject'],
				'forum_last_post_time'		=> $forum['forum_last_post_time'],
				'forum_last_poster_name'	=> $forum['forum_last_poster_name'],
				'forum_last_poster_colour'	=> '',
			);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary);
			$sql .= ' WHERE forum_id = ' . (int) $forum['forum_id'];
			$db->sql_query($sql);
		}

		if (sizeof($sql_posts))
		{
			// Save the array to the posts table
			$db->sql_multi_insert(POSTS_TABLE, $sql_posts);
			unset($sql_posts);
		}

		if (sizeof($sql_topics))
		{
			// Save the array to the topics table
			$db->sql_multi_insert(TOPICS_TABLE, $sql_topics);
			unset($sql_topics);
			$sql_topics = array();
		}

		// phpBB installs the forum with one topic and one post.
		set_config('num_topics', $topic_cnt + 1);
		set_config('num_posts', $post_cnt + 1);

		$db->update_sequence(TOPICS_TABLE . '_seq', $topic_cnt + 1);
		$db->update_sequence(POSTS_TABLE . '_seq', $post_cnt + 1);
	}

	/**
	 * Create our forums and populate the forums array.
	 * I think we can use phpBB default functions for this.
	 * Hope nobody is trying to
	 */
	private function create_forums()
	{
		$acp_forums = new acp_forums();

		$parent_arr = array();

		// The first category to
		$parent_arr[] = $this->def_cat_id;

		for ($i = 0; $i < $this->num_cats; $i++)
		{
			// Create catergories and fill a array with parent ids.
			$parent_arr[] = $this->_create_forums(FORUM_CAT, $i + 1, $acp_forums);
		}

		$parent_size = sizeof($parent_arr);

		// If we have more than one cat, let's start with the second.
		$parent_cnt = ($parent_size > 1) ? 1 : 0;
		for ($i = 0; $i < $this->num_forums; $i++)
		{
			$this->_create_forums(FORUM_POST, $i + 1, $acp_forums, $parent_arr[$parent_cnt++]);

			if ($parent_cnt >= $parent_size)
			{
				$parent_cnt = 0;
			}
		}
	}

	/**
	 * The function actually creating the forums
	 */
	private function _create_forums($forum_type, $cnt, $acp_forums, $parent_id = 0)
	{
		global $user, $auth;

		$forum_name = ($forum_type == FORUM_CAT) ? sprintf($user->lang['TEST_CAT_NAME'], $cnt) : sprintf($user->lang['TEST_FORUM_NAME'], $cnt);
		$forum_desc = ($forum_type == FORUM_CAT) ? sprintf($user->lang['TEST_FORUM_NAME'], $cnt) : '';

		// Setting up the data to be used
		$forum_data = array(
			'parent_id'					=> $parent_id,
			'forum_type'				=> $forum_type,
			'forum_status'				=> ITEM_UNLOCKED,
			'forum_parents'				=> '',
			'forum_options'				=> 0,
			'forum_name'				=> utf8_normalize_nfc($forum_name),
			'forum_link'				=> '',
			'forum_link_track'			=> false,
			'forum_desc'				=> utf8_normalize_nfc($forum_desc),
			'forum_desc_uid'			=> '',
			'forum_desc_options'		=> 7,
			'forum_desc_bitfield'		=> '',
			'forum_rules'				=> '',
			'forum_rules_uid'			=> '',
			'forum_rules_options'		=> 7,
			'forum_rules_bitfield'		=> '',
			'forum_rules_link'			=> '',
			'forum_image'				=> '',
			'forum_style'				=> 0,
			'forum_password'			=> '',
			'forum_password_confirm'	=> '',
			'display_subforum_list'		=> true,
			'display_on_index'			=> true,
			'forum_topics_per_page'		=> 0,
			'enable_indexing'			=> true,
			'enable_icons'				=> false,
			'enable_prune'				=> false,
			'enable_post_review'		=> true,
			'enable_quick_reply'		=> false,
			'prune_days'				=> 7,
			'prune_viewed'				=> 7,
			'prune_freq'				=> 1,
			'prune_old_polls'			=> false,
			'prune_announce'			=> false,
			'prune_sticky'				=> false,
			'forum_password_unset'		=> false,
			'show_active'				=> 1,
		);

		// The description should not need this, but who knows what people will come up with.
		generate_text_for_storage($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options'], false, false, false);

		// Create that thing.
		$errors = $acp_forums->update_forum_data($forum_data);

		if (sizeof($errors))
		{
			trigger_error(implode('<br />', $errors));
		}

		// Copy the permissions from our default forums
		copy_forum_permissions($this->def_forum_id, $forum_data['forum_id']);
		$auth->acl_clear_prefetch();

		if ($forum_type == FORUM_POST)
		{
			// A normal forum. There is no link type forums installed with phpBB.
			$this->forum_arr[$forum_data['forum_id']] = array(
				'forum_id'					=> $forum_data['forum_id'],
				'parent_id'					=> $forum_data['parent_id'],
				'forum_posts'				=> 0,
				'forum_topics'				=> 0,
				'forum_topics_real'			=> 0,
				'forum_last_post_id'		=> 0,
				'forum_last_poster_id'		=> 0,
				'forum_last_post_subject'	=> '',
				'forum_last_post_time'		=> 0,
				'forum_last_poster_name'	=> '',
			);
		}

		return($forum_data['forum_id']);
	}

	/**
	 * Creates users and puts them in the right groups.
	 * Also populates the users array.
	 */
	private function save_users()
	{
		global $db, $config, $settings;

		// Hash the password.
		$password = phpbb_hash('123456');

		$registered_group = $newly_registered_group = 0;
		// Get the group id for registered users and newly registered.
		$sql = 'SELECT group_id, group_name FROM ' . GROUPS_TABLE . '
			WHERE group_name = \'REGISTERED\'
			OR group_name = \'NEWLY_REGISTERED\'';
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['group_name'] == 'REGISTERED')
			{
				$registered_group = (int) $row['group_id'];
			}
			else
			{
				$newly_registered_group = (int) $row['group_id'];
			}
		}
		$db->sql_freeresult($result);

		$s_chunks = ($this->num_users > $this->user_chunks) ? true : false;
		$end = $this->num_users + 1;
		$chunk_cnt = 0;
		$sql_ary = array();

		foreach ($this->user_arr as $user)
		{
			$email = $user['username_clean'] . $this->email_domain;
			$sql_ary[] = array(
				'user_id'				=> $user['user_id'],
				'username'				=> $user['username'],
				'username_clean'		=> $user['username_clean'],
				'user_lastpost_time'	=> $user['user_lastpost_time'],
				'user_lastmark'			=> $user['user_lastmark'],
				'user_posts'			=> $user['user_posts'],
				'user_password'			=> $password,
				'user_pass_convert'		=> 0,
				'user_email'			=> $email,
				'user_email_hash'		=> phpbb_email_hash($email),
				'group_id'				=> $registered_group,
				'user_type'				=> USER_NORMAL,
				'user_permissions'		=> '',
				'user_timezone'			=> $settings->get_config('qi_tz', 0),
				'user_lang'				=> $settings->get_config('qi_lang'),
				'user_dst'				=> $settings->get_config('qi_dst', 0),
				'user_form_salt'		=> unique_id(),
				'user_style'			=> (int) $config['default_style'],
				'user_regdate'			=> $user['user_regdate'],
				'user_passchg'			=> $user['user_passchg'],
				'user_options'			=> 230271,
				'user_full_folder'		=> PRIVMSGS_NO_BOX,
				'user_notify_type'		=> NOTIFY_EMAIL,

				'user_sig'			=> '',
				'user_occ'			=> '',
				'user_interests'	=> '',
			);

			$chunk_cnt++;
			if ($s_chunks && $chunk_cnt >= $this->user_chunks)
			{
				// throw the array to the users table
				$db->sql_multi_insert(USERS_TABLE, $sql_ary);
				unset($sql_ary);
				$sql_ary = array();
				$chunk_cnt = 0;
			}
		}
		// If there are any remaining users we need to throw them in to.
		if (!empty($sql_ary))
		{
			$db->sql_multi_insert(USERS_TABLE, $sql_ary);
		}
		unset($sql_ary);

		// Put them in groups.
		$chunk_cnt = $newly_registered = $skip = 0;

		// Don't add the first users to the newly registered group if a moderator and/or an admin is needed.
		$skip = ($this->create_mod) ? $skip + 1 : $skip;
		$skip = ($this->create_admin) ? $skip + 1 : $skip;

		// First the registered group.
		foreach ($this->user_arr as $user)
		{
			$sql_ary[] = array(
				'user_id'		=> (int) $user['user_id'],
				'group_id'		=> (int) $registered_group,
				'group_leader'	=> 0, // No group leaders.
				'user_pending'	=> 0, // User is not pending.
			);

			if ($newly_registered < $this->num_new_group && $skip < 1)
			{
				$sql_ary[] = array(
					'user_id'		=> (int) $user['user_id'],
					'group_id'		=> (int) $newly_registered_group,
					'group_leader'	=> 0, // No group leaders.
					'user_pending'	=> 0, // User is not pending.
				);

				$newly_registered++;
			}

			$skip--;

			if ($s_chunks && $chunk_cnt >= $this->user_chunks)
			{
				// throw the array to the users table
				$db->sql_multi_insert(USER_GROUP_TABLE, $sql_ary);
				unset($sql_ary);
				$sql_ary = array();
				$chunk_cnt = 0;
			}
		}
		$db->sql_multi_insert(USER_GROUP_TABLE, $sql_ary);

		// Get the last user
		$user = end($this->user_arr);
		set_config('newest_user_id', $user['user_id']);
		set_config('newest_username', $user['username']);
		set_config('newest_user_colour', '');

		// phpBB installs the forum with one user.
		set_config('num_users', $this->num_users + 1);
	}

	/**
	 * Populates the category array and the forums array with the default forums.
	 * Needed for posts and topics.
	 */
	private function get_default_forums()
	{
		global $db;

		// We are the only ones messing with this database so far.
		// So the latest user_id + 1 should be the user id for the first test user.
		$sql = 'SELECT forum_id, parent_id, forum_type, forum_posts, forum_topics, forum_topics_real, forum_last_post_id, forum_last_poster_id, forum_last_post_subject, forum_last_post_time, forum_last_poster_name FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['forum_type'] == FORUM_CAT)
			{
				$this->def_cat_id = (int) $row['forum_id'];
			}
			else
			{
				// A normal forum. There is no link type forums installed with phpBB.
				$this->forum_arr[$row['forum_id']] = array(
					'forum_id'					=> $row['forum_id'],
					'parent_id'					=> $row['parent_id'],
					'forum_posts'				=> $row['forum_posts'],
					'forum_topics'				=> $row['forum_topics'],
					'forum_topics_real'			=> $row['forum_topics_real'],
					'forum_last_post_id'		=> $row['forum_last_post_id'],
					'forum_last_poster_id'		=> $row['forum_last_poster_id'],
					'forum_last_post_subject'	=> $row['forum_last_post_subject'],
					'forum_last_post_time'		=> $row['forum_last_post_time'],
					'forum_last_poster_name'	=> $row['forum_last_poster_name'],
				);

				$this->def_forum_id = (int) $row['forum_id'];
			}
		}
	}

	/**
	 * Populates the users array and fills with some default data.
	 * Needed for posts and topics.
	 */
	private function pop_user_arr()
	{
		global $db;

		// We are the only ones messing with this database so far.
		// So the last user_id + 1 should be the user id for the first user.
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			ORDER BY user_id DESC';
		$result = $db->sql_query_limit($sql, 1);
		$first_user_id = (int) $db->sql_fetchfield('user_id') + 1;
		$db->sql_freeresult($result);
		$last_user_id = $first_user_id + $this->num_users - 1;

		// Do some fancy math so we get one new user per minute.
		$reg_time = time() - ($this->num_users * 60);

		$cnt = 1;
		for ($i = $first_user_id; $i <= $last_user_id; $i++)
		{
			$this->user_arr[$i] = array(
				'user_id'			=> $i,
				'username'			=> 'tester_' . $cnt,
				'username_clean'	=> 'tester_' . $cnt,
				'user_lastpost_time'	=> 0,
				'user_lastmark'		=> 0,
				'user_lastvisit'	=> 0,
				'user_posts'		=> 0,
				'user_regdate'		=> $reg_time,
				'user_passchg'		=> $reg_time,
			);

			$reg_time += 60;
			$cnt++;
		}
	}
}
