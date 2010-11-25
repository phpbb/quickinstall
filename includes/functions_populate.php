<?php
/**
*
* @package quickinstall
* @version $Id$
* @copyright (c) 2010 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
	private $num_users = 0;
	private $num_new_group = 0;
	private $num_cats = 0;
	private $num_forums = 0;
	private $num_topics_min = 0;
	private $num_topics_max = 0;
	private $num_replies_min = 0;
	private $num_replies_max = 0;
	private $email_domain = '';

	// How many of each type to send to the db each run
	// Might be better to add some memory checking later.
	private $chunks = 5000;

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
	 * Not sure if this really is needed.
	 * Would save memory to remove it...
	 * $cat_arr = array(
	 *   (int) $cat_id => array(
	 *     'cat_id' => (int) $cat_id,
	 *     'forum_posts' => (int) $forum_posts_cnt,
	 *     'forum_topics' => (int) $forum_topics_cnt,
	 *   ),
	 * );
	 */
	private $cat_arr = array();

	/**
	 * $forum_arr = array(
	 *   (int) $forum_id => array(
	 *     'forum_id' => (int) $forum_id,
	 *     'parent_id' => (int) $cat_id,
	 *     'forum_posts' => (int) $forum_posts_cnt,
	 *     'forum_topics' => (int) $forum_topics_cnt,
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
	 *
	 * $topc_arr = array(
	 *   (int) $topic_id => array(
	 *     'topic_id' => (int) $topic_id,
	 *     'forum_id' => (int) $forum_id,
	 * 	 ),
	 * );
	 */
	private $topc_arr = array();

	// The default forums. To copy permissions from.
	private $def_cat_id = 1;
	private $def_forum_id = 2;

	public function populate($data)
	{
		global $db, $user, $auth, $cache;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

		// Initiate this thing.
		$this->create_mod = (!empty($data['create_mod'])) ? true : false;
		$this->num_users = (!empty($data['num_users'])) ? (int) $data['num_users'] : 0;
		$this->num_new_group = (!empty($data['num_new_group'])) ? (int) $data['num_new_group'] : 0;
		$this->num_cats = (!empty($data['num_cats'])) ? (int) $data['num_cats'] : 0;
		$this->num_forums = (!empty($data['num_forums'])) ? (int) $data['num_forums'] : 0;
		$this->num_topics_min = (!empty($data['num_topics_min'])) ? (int) $data['num_topics_min'] : 0;
		$this->num_topics_max = (!empty($data['num_topics_max'])) ? (int) $data['num_topics_max'] : 0;
		$this->num_replies_min = (!empty($data['num_replies_min'])) ? (int) $data['num_replies_min'] : 0;
		$this->num_replies_max = (!empty($data['num_replies_max'])) ? (int) $data['num_replies_max'] : 0;

		// Populate the users array with some initial data.
		$this->pop_user_arr();

		// There is already one forum and one category created. Let's get their data.
		$this->get_default_forums();

		// There is already one category and one forum created.
		$this->num_forums = ($this->num_forums) ? $this->num_forums - 1 : 0;
		$this->num_cats = ($this->num_cats) ? $this->num_cats - 1 : 0;

		// We need to create categories and forums first.
		if ($this->num_forums)
		{
			// Don't create any categories if no forums are to be created.
			$this->create_forums();
		}

		// And now those plesky posts.


		if ($this->num_users)
		{
			// Make sure the email domain starts with a @ and is lower case.
			$this->email_domain = ((strpos($data['email_domain'], '@') === false) ? '@' : '') . $data['email_domain'];
			$this->email_domain = strtolower($this->email_domain);

			$this->create_users();
		}

//		var_dump($this->cat_arr, $this->forum_arr);
	}

	/**
	 * Create our forums and populate the forums array.
	 * I think we can use phpBB default functions for this.
	 * Hope nobody is trying to
	 */
	private function create_forums()
	{
		global $db, $user, $auth, $cache;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

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

		$acp_forums = new acp_forums();

		for ($i = 0; $i < $this->num_cats; $i++)
		{
			$this->_create_forums(FORUM_CAT, $i + 1, $acp_forums);
		}

		foreach ($this->cat_arr as $key => $value)
		{
			$parent_arr[] = $key;
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
		global $db, $user, $auth, $cache; //, $acp_forums;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

		$forum_name = ($forum_type == FORUM_CAT) ? sprintf($user->lang['TEST_CAT_NAME'], $cnt) : sprintf($user->lang['TEST_FORUM_NAME'], $cnt);
		$forum_desc = ($forum_type == FORUM_CAT) ? sprintf($user->lang['TEST_FORUM_NAME'], $cnt) : '';

		// Setting up the data to be used
		$forum_data = array(
			'parent_id'				=> $parent_id,
			'forum_type'			=> $forum_type,
			'forum_status'			=> ITEM_UNLOCKED,
			'forum_parents'			=> '',
			'forum_options'			=> 0,
			'forum_name'			=> utf8_normalize_nfc($forum_name),
			'forum_link'			=> '',
			'forum_link_track'		=> false,
			'forum_desc'			=> utf8_normalize_nfc($forum_desc),
			'forum_desc_uid'		=> '',
			'forum_desc_options'	=> 7,
			'forum_desc_bitfield'	=> '',
			'forum_rules'			=> '',
			'forum_rules_uid'		=> '',
			'forum_rules_options'	=> 7,
			'forum_rules_bitfield'	=> '',
			'forum_rules_link'		=> '',
			'forum_image'			=> '',
			'forum_style'			=> 0,
			'forum_password'		=> '',
			'forum_password_confirm'=> '',
			'display_subforum_list'	=> true,
			'display_on_index'		=> true,
			'forum_topics_per_page'	=> 0,
			'enable_indexing'		=> true,
			'enable_icons'			=> false,
			'enable_prune'			=> false,
			'enable_post_review'	=> true,
			'enable_quick_reply'	=> false,
			'prune_days'			=> 7,
			'prune_viewed'			=> 7,
			'prune_freq'			=> 1,
			'prune_old_polls'		=> false,
			'prune_announce'		=> false,
			'prune_sticky'			=> false,
			'forum_password_unset'	=> false,
			'show_active'			=> 1,
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
		copy_forum_permissions($this->def_cat_id, $forum_data['forum_id']);
		$auth->acl_clear_prefetch();

		if ($forum_type == FORUM_CAT)
		{
			// This is a category
			$this->cat_arr[$forum_data['forum_id']] = array(
				'forum_id' => $forum_data['forum_id'],

				// These two are filled for the default cat when installing phpBB.
				// But not when posting...
				'forum_posts' => 0,
				'forum_topics' => 0,
			);
		}
		else
		{
			// A normal forum. There is no link type forums installed with phpBB.
			$this->forum_arr[$forum_data['forum_id']] = array(
				'forum_id' => $forum_data['forum_id'],
				'parent_id' => $forum_data['parent_id'],
				'forum_posts' => 0,
				'forum_topics' => 0,
				'forum_last_post_id' => 0,
				'forum_last_poster_id' => 0,
				'forum_last_post_subject' => '',
				'forum_last_post_time' => 0,
				'forum_last_poster_name' => '',
			);
		}
	}

	/**
	 * Creates users and put's them in the right groups.
	 * Also populates the users array.
	 */
	private function create_users()
	{
		global $db, $user, $auth, $cache;
		global $quickinstall_path, $phpbb_root_path, $phpEx, $config, $qi_config;

		// We are the only ones messing with this database so far.
		// So the latest user_id + 1 should be the user id for the first user.
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			ORDER BY user_id DESC';
		$result = $db->sql_query_limit($sql, 1);
		$first_user_id = (int) $db->sql_fetchfield('user_id') + 1;
		$db->sql_freeresult($result);
		$last_user_id = $first_user_id + $this->num_users - 1;

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

		$s_chunks = ($this->num_users > $this->chunks) ? true : false;
		$end = $this->num_users + 1;
		$new_cnt = $chunk_cnt = 0;
		$sql_ary = array();
		for ($i = 1; $i < $end; $i++)
		{
			$email = 'tester_' . $i . $this->email_domain;

			$sql_ary[] = array(
				'username'					=> 'tester_' . $i,
				'username_clean'		=> 'tester_' . $i,
				'user_password'			=> $password,
				'user_pass_convert'	=> 0,
				'user_email'				=> $email,
				'user_email_hash'		=> phpbb_email_hash($email),
				'group_id'					=> $registered_group,
				'user_type'					=> USER_NORMAL,
				'user_permissions'	=> '',
				'user_timezone'			=> $qi_config['qi_tz'],
				'user_lang'					=> $qi_config['qi_lang'],
				'user_dst'					=> (int) $qi_config['qi_dst'],
				'user_form_salt'		=> unique_id(),
				'user_style'				=> (int) $config['default_style'],
				'user_regdate'			=> time(),
				'user_passchg'			=> time(),
				'user_options'			=> 230271,
				'user_full_folder'	=> PRIVMSGS_NO_BOX,
				'user_notify_type'	=> NOTIFY_EMAIL,
			);

			$chunk_cnt++;
			if ($s_chunks && $chunk_cnt >= $this->chunks)
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
	}

	/**
	 * Populates the category array and the forums array with the default forums.
	 * Needed for posts and topics.
	 */
	private function get_default_forums()
	{
		global $db;

		// We are the only ones messing with this database so far.
		// So the latest user_id + 1 should be the user id for the first user.
		$sql = 'SELECT forum_id, parent_id, forum_type, forum_posts, forum_topics, forum_last_post_id, forum_last_poster_id, forum_last_post_subject, forum_last_post_time, forum_last_poster_name FROM ' . FORUMS_TABLE;
		$result = $db->sql_query($sql);

		while ($row = $db->sql_fetchrow($result))
		{
			if ($row['forum_type'] == FORUM_CAT)
			{
				// This is a category
				$this->cat_arr[$row['forum_id']] = array(
					'forum_id' => $row['forum_id'],

					// These two are filled for the default cat when installing phpBB.
					// But not when posting...
					'forum_posts' => $row['forum_posts'],
					'forum_topics' => $row['forum_topics'],
				);

				$this->def_cat_id = (int) $row['forum_id'];
			}
			else
			{
				// A normal forum. There is no link type forums installed with phpBB.
				$this->forum_arr[$row['forum_id']] = array(
					'forum_id' => $row['forum_id'],
					'parent_id' => $row['parent_id'],
					'forum_posts' => $row['forum_posts'],
					'forum_topics' => $row['forum_topics'],
					'forum_last_post_id' => $row['forum_last_post_id'],
					'forum_last_poster_id' => $row['forum_last_poster_id'],
					'forum_last_post_subject' => $row['forum_last_post_subject'],
					'forum_last_post_time' => $row['forum_last_post_time'],
					'forum_last_poster_name' => $row['forum_last_poster_name'],
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
		// So the latest user_id + 1 should be the user id for the first user.
		$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
			ORDER BY user_id DESC';
		$result = $db->sql_query_limit($sql, 1);
		$first_user_id = (int) $db->sql_fetchfield('user_id') + 1;
		$db->sql_freeresult($result);
		$last_user_id = $first_user_id + $this->num_users - 1;

		for ($i = $first_user_id; $i <= $last_user_id; $i++)
		{
			$this->user_arr[$i] = array(
				'user_ud' => $i,
				'user_lastpost_time' => 0,
				'user_lastmark' => 0,
				'user_lastvisit' => 0,
				'user_posts' => 0,
			);
		}
	}
}
