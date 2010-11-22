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
	private $num_topics = 0;
	private $num_replies = 0;

	/**
	 * $user_arr = array(
	 *   (int) $user_id => array(
	 *     'user_ud' => (int) $user_id,
	 *     'username' => (string) $username,
	 *     'e-mail' => (string) $email,
	 *     'new_group' => (bool) $in_newly_registered_users_group,
	 *   ),
	 * );
	 */
	private $user_arr = array();

	/**
	 * $cat_arr = array(
	 *   (int) $cat_id => array(
	 *     'cat_id' => (int) $cat_id,
	 *   ),
	 * );
	 */
	private $cat_arr = array();

	/**
	 * $forum_arr = array(
	 *   (int) $forum_id => array(
	 *     'forum_id' => (int) $forum_id,
	 *     'parent_id' => (int) $cat_id,
	 *     'left_id' => (int) $left_id,
	 *     'right_id' => (int) $right_id,
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
		$this->num_topics = (!empty($data['num_topics'])) ? (int) $data['num_topics'] : 0;
		$this->num_replies = (!empty($data['num_replies'])) ? (int) $data['num_replies'] : 0;

		if ($this->num_users)
		{
			$this->create_users();
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

		// Hash the password.
		$password = phpbb_hash('123456');

		// Get the group id for registered users and newly registered.
		$registered_group = $newly_registered_group = 0;
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


	}
}
