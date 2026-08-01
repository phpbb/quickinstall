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

class StateBuilder extends BaseBuilder
{
	public function seed(array $users, array $forums, array $content): void
	{
		$this->seedAttachments($content['bbcode']['post_id']);
		$this->reportPost($content['bbcode_reply']['post_id'], $users['regular']);
		$messageIds = $this->seedPrivateMessages($users);
		$this->seedUcpState($forums['lobby'], $content['normal']['topic_id']);
		$this->seedNotifications($users, $forums, $content, $messageIds);
		$this->refreshSearchIndex();
		$this->context->restoreUser();
	}

	private function seedAttachments(int $postId): void
	{
		$db = $this->context->db;
		$result = $db->sql_query_limit(
			'SELECT topic_id, poster_id FROM ' . POSTS_TABLE . ' WHERE post_id = ' . $postId,
			1
		);
		$post = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if (!$post)
		{
			throw new RuntimeException('Attachment target post is unavailable.');
		}

		$definitions = [
			[
				'real' => sprintf('sample-image-%d.png', $this->context->seed),
				'extension' => 'png',
				'mime' => 'image/png',
				'comment' => 'Sample PNG image attachment.',
				'content' => AssetFactory::png(640, 360, $this->context->seed + 12000),
			],
			[
				'real' => sprintf('sample-archive-%d.zip', $this->context->seed),
				'extension' => 'zip',
				'mime' => 'application/zip',
				'comment' => 'Sample ZIP archive attachment.',
				'content' => AssetFactory::zip($this->context->seed),
			],
		];

		$directory = $this->context->root . $this->context->storageDirectory(
			'attachment',
			(string) ($this->context->config['upload_path'] ?? 'files')
		) . '/';
		if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory))
		{
			throw new RuntimeException("Unable to create attachment directory: $directory");
		}

		foreach ($definitions as $index => $definition)
		{
			$physical = sprintf('qi_dev_%d_%s', $this->context->seed, hash('sha256', $definition['real']));
			$result = $db->sql_query_limit(
				'SELECT attach_id FROM ' . ATTACHMENTS_TABLE . "
					WHERE physical_filename = '" . $db->sql_escape($physical) . "'",
				1
			);
			$attachmentId = (int) $db->sql_fetchfield('attach_id');
			$db->sql_freeresult($result);
			$path = $directory . $physical;
			$this->context->writeFile('attachment', $physical, $path, $definition['content']);

			if (!$attachmentId)
			{
				$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
					'post_msg_id' => $postId,
					'topic_id' => (int) $post['topic_id'],
					'in_message' => 0,
					'poster_id' => (int) $post['poster_id'],
					'is_orphan' => 0,
					'physical_filename' => $physical,
					'real_filename' => $definition['real'],
					'download_count' => 7 + $index,
					'attach_comment' => $definition['comment'],
					'extension' => $definition['extension'],
					'mimetype' => $definition['mime'],
					'filesize' => strlen($definition['content']),
					'filetime' => time(),
					'thumbnail' => 0,
				]));
				$attachmentId = $this->lastInsertedId();
			}
			$this->context->addId('attachments', $attachmentId);
		}
		$db->sql_query('UPDATE ' . POSTS_TABLE . ' SET post_attachment = 1 WHERE post_id = ' . $postId);
	}

	private function reportPost(int $postId, int $reporterId): void
	{
		$db = $this->context->db;
		$result = $db->sql_query_limit('SELECT report_id FROM ' . REPORTS_TABLE . ' WHERE post_id = ' . $postId, 1);
		$reportId = (int) $db->sql_fetchfield('report_id');
		$db->sql_freeresult($result);
		if ($reportId)
		{
			$this->context->addId('reports', $reportId);
			return;
		}

		$result = $db->sql_query_limit(
			'SELECT topic_id, post_text, bbcode_uid, bbcode_bitfield, enable_bbcode, enable_smilies, enable_magic_url
				FROM ' . POSTS_TABLE . ' WHERE post_id = ' . $postId,
			1
		);
		$post = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$reasonId = $this->reportReasonId();
		$db->sql_query('INSERT INTO ' . REPORTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'reason_id' => $reasonId,
			'post_id' => $postId,
			'pm_id' => 0,
			'user_id' => $reporterId,
			'user_notify' => 0,
			'report_closed' => 0,
			'report_time' => time(),
			'report_text' => 'This reply has been reported for moderator review.',
			'reported_post_text' => $post['post_text'],
			'reported_post_uid' => $post['bbcode_uid'],
			'reported_post_bitfield' => $post['bbcode_bitfield'],
			'reported_post_enable_bbcode' => $post['enable_bbcode'],
			'reported_post_enable_smilies' => $post['enable_smilies'],
			'reported_post_enable_magic_url' => $post['enable_magic_url'],
		]));
		$reportId = $this->lastInsertedId();
		$this->context->addId('reports', $reportId);
		$db->sql_query('UPDATE ' . POSTS_TABLE . ' SET post_reported = 1 WHERE post_id = ' . $postId);
		$db->sql_query('UPDATE ' . TOPICS_TABLE . ' SET topic_reported = 1 WHERE topic_id = ' . (int) $post['topic_id']);
	}

	private function seedPrivateMessages(array $users): array
	{
		$founder = $this->context->founderId();
		$messages = [];
		$this->context->switchUser($users['admin']);
		$messages['unread'] = $this->sendMessage(
			$founder,
			'Unread private message',
			'This message has not been opened yet, so it should appear as unread in the inbox.'
		);
		$this->context->switchUser($users['moderator']);
		$messages['read'] = $this->sendMessage(
			$founder,
			'Read private message',
			'This message has already been opened and should appear as read in the inbox.'
		);
		$this->markMessageRead($messages['read'], $founder);
		$this->context->switchUser($users['regular_2']);
		$messages['reported'] = $this->sendMessage(
			$founder,
			'Reported private message',
			'This message has an open report attached and should appear in the moderator queue.'
		);
		$this->reportMessage($messages['reported'], $users['regular']);
		$this->context->switchUser($founder);
		$messages['sent'] = $this->sendMessage(
			$users['regular'],
			'Sent private message',
			'This message was sent by the board founder and should appear in the sent messages folder.'
		);
		$this->markMessageRead($messages['sent'], $users['regular']);

		for ($index = 0; $index < 5; $index++)
		{
			$sender = $users['posters'][$index];
			$recipient = $users['posters'][$index + 8];
			$this->context->switchUser($sender);
			$messages['peer_' . $index] = $this->sendMessage(
				$recipient,
				sprintf('Peer private message %d', $index + 1),
				'This is a private conversation between two generated users.'
			);
		}
		$this->context->setMeta('messages', $messages);
		return $messages;
	}

	private function sendMessage(int $recipientId, string $label, string $message): int
	{
		$db = $this->context->db;
		$subject = $this->context->prefix . $label;
		$authorId = (int) $this->context->user->data['user_id'];
		$result = $db->sql_query_limit(
			'SELECT msg_id FROM ' . PRIVMSGS_TABLE . "
				WHERE author_id = $authorId
					AND message_subject = '" . $db->sql_escape($subject) . "'",
			1
		);
		$messageId = (int) $db->sql_fetchfield('msg_id');
		$db->sql_freeresult($result);
		if (!$messageId)
		{
			$uid = $bitfield = '';
			$options = 7;
			generate_text_for_storage($message, $uid, $bitfield, $options, true, true, true);
			$data = [
				'address_list' => ['u' => [$recipientId => 'to']],
				'from_user_id' => $authorId,
				'from_user_ip' => '127.0.0.1',
				'from_username' => $this->context->user->data['username'],
				'enable_sig' => true,
				'enable_bbcode' => true,
				'enable_smilies' => true,
				'enable_urls' => true,
				'icon_id' => 0,
				'bbcode_uid' => $uid,
				'bbcode_bitfield' => $bitfield,
				'message' => $message,
			];
			submit_pm('post', $subject, $data, true);
			$messageId = (int) ($data['msg_id'] ?? 0);
		}
		if (!$messageId)
		{
			throw new RuntimeException("Unable to create development private message: $subject");
		}
		$this->context->addId('messages', $messageId);
		return $messageId;
	}

	private function markMessageRead(int $messageId, int $userId): void
	{
		$db = $this->context->db;
		$db->sql_query('UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET pm_unread = 0, pm_new = 0, folder_id = ' . PRIVMSGS_INBOX . '
			WHERE msg_id = ' . $messageId . ' AND user_id = ' . $userId);
		$db->sql_query('UPDATE ' . PRIVMSGS_TO_TABLE . '
			SET folder_id = ' . PRIVMSGS_SENTBOX . '
			WHERE msg_id = ' . $messageId . ' AND folder_id = ' . PRIVMSGS_OUTBOX);
		$db->sql_query('UPDATE ' . USERS_TABLE . '
			SET user_new_privmsg = CASE WHEN user_new_privmsg > 0 THEN user_new_privmsg - 1 ELSE 0 END,
				user_unread_privmsg = CASE WHEN user_unread_privmsg > 0 THEN user_unread_privmsg - 1 ELSE 0 END
			WHERE user_id = ' . $userId);
	}

	private function reportMessage(int $messageId, int $reporterId): void
	{
		$db = $this->context->db;
		$result = $db->sql_query_limit('SELECT report_id FROM ' . REPORTS_TABLE . ' WHERE pm_id = ' . $messageId, 1);
		$reportId = (int) $db->sql_fetchfield('report_id');
		$db->sql_freeresult($result);
		if ($reportId)
		{
			$this->context->addId('reports', $reportId);
			return;
		}
		$result = $db->sql_query_limit(
			'SELECT message_text, bbcode_uid, bbcode_bitfield, enable_bbcode, enable_smilies, enable_magic_url
				FROM ' . PRIVMSGS_TABLE . ' WHERE msg_id = ' . $messageId,
			1
		);
		$message = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		$db->sql_query('INSERT INTO ' . REPORTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
			'reason_id' => $this->reportReasonId(),
			'post_id' => 0,
			'pm_id' => $messageId,
			'user_id' => $reporterId,
			'user_notify' => 0,
			'report_closed' => 0,
			'report_time' => time(),
			'report_text' => 'This private message has been reported for moderator review.',
			'reported_post_text' => $message['message_text'],
			'reported_post_uid' => $message['bbcode_uid'],
			'reported_post_bitfield' => $message['bbcode_bitfield'],
			'reported_post_enable_bbcode' => $message['enable_bbcode'],
			'reported_post_enable_smilies' => $message['enable_smilies'],
			'reported_post_enable_magic_url' => $message['enable_magic_url'],
		]));
		$reportId = $this->lastInsertedId();
		$this->context->addId('reports', $reportId);
		$db->sql_query('UPDATE ' . PRIVMSGS_TABLE . ' SET message_reported = 1 WHERE msg_id = ' . $messageId);
	}

	private function reportReasonId(): int
	{
		$result = $this->context->db->sql_query_limit(
			'SELECT reason_id FROM ' . REPORTS_REASONS_TABLE . ' ORDER BY reason_order ASC',
			1
		);
		$id = (int) $this->context->db->sql_fetchfield('reason_id');
		$this->context->db->sql_freeresult($result);
		return $id ?: 1;
	}

	private function seedUcpState(int $forumId, int $topicId): void
	{
		$db = $this->context->db;
		$userId = $this->context->founderId();
		$this->insertIfMissing(
			BOOKMARKS_TABLE,
			'topic_id = ' . $topicId . ' AND user_id = ' . $userId,
			['topic_id' => $topicId, 'user_id' => $userId]
		);
		$this->insertIfMissing(
			TOPICS_WATCH_TABLE,
			'topic_id = ' . $topicId . ' AND user_id = ' . $userId,
			['topic_id' => $topicId, 'user_id' => $userId, 'notify_status' => 0]
		);
		$this->insertIfMissing(
			FORUMS_WATCH_TABLE,
			'forum_id = ' . $forumId . ' AND user_id = ' . $userId,
			['forum_id' => $forumId, 'user_id' => $userId, 'notify_status' => 0]
		);

		$subject = $this->context->prefix . 'Saved draft';
		$result = $db->sql_query_limit(
			'SELECT draft_id FROM ' . DRAFTS_TABLE . "
				WHERE user_id = $userId AND draft_subject = '" . $db->sql_escape($subject) . "'",
			1
		);
		$draftId = (int) $db->sql_fetchfield('draft_id');
		$db->sql_freeresult($result);
		if (!$draftId)
		{
			$db->sql_query('INSERT INTO ' . DRAFTS_TABLE . ' ' . $db->sql_build_array('INSERT', [
				'user_id' => $userId,
				'topic_id' => 0,
				'forum_id' => $forumId,
				'save_time' => time(),
				'draft_subject' => $subject,
				'draft_message' => 'This draft has been saved but not posted.',
			]));
			$draftId = $this->lastInsertedId();
		}
		$this->context->addId('drafts', $draftId);
		$this->context->setMeta('ucp', ['user_id' => $userId, 'forum_id' => $forumId, 'topic_id' => $topicId]);
	}

	private function insertIfMissing(string $table, string $where, array $row): void
	{
		$db = $this->context->db;
		$result = $db->sql_query_limit("SELECT * FROM $table WHERE $where", 1);
		$exists = (bool) $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if (!$exists)
		{
			$db->sql_query("INSERT INTO $table " . $db->sql_build_array('INSERT', $row));
		}
	}

	private function seedNotifications(array $users, array $forums, array $content, array $messages): void
	{
		$db = $this->context->db;
		$result = $db->sql_query('SELECT notification_type_id, notification_type_name FROM ' . NOTIFICATION_TYPES_TABLE);
		$types = [];
		while ($row = $db->sql_fetchrow($result))
		{
			$types[$row['notification_type_name']] = (int) $row['notification_type_id'];
		}
		$db->sql_freeresult($result);
		$recipient = $this->context->founderId();
		$definitions = [
			[
				'type' => 'notification.type.post',
				'item' => $content['normal']['post_id'],
				'parent' => $content['normal']['topic_id'],
				'data' => [
					'poster_id' => $users['regular'],
					'post_username' => '',
					'post_subject' => $this->context->prefix . 'Normal topic',
					'topic_title' => $this->context->prefix . 'Normal topic',
					'forum_id' => $forums['lobby'],
				],
			],
			[
				'type' => 'notification.type.quote',
				'item' => $content['bbcode_reply']['post_id'],
				'parent' => $content['bbcode']['topic_id'],
				'data' => [
					'poster_id' => $users['moderator'],
					'post_subject' => $this->context->prefix . 'Re: BBCode and smiley showcase',
					'topic_title' => $this->context->prefix . 'BBCode and smiley showcase',
					'forum_id' => $forums['lobby'],
				],
			],
			[
				'type' => 'notification.type.pm',
				'item' => $messages['unread'],
				'parent' => 0,
				'data' => [
					'from_user_id' => $users['admin'],
					'message_subject' => $this->context->prefix . 'Unread private message',
				],
			],
		];
		foreach ($definitions as $definition)
		{
			if (!isset($types[$definition['type']]))
			{
				throw new RuntimeException('Required notification type is unavailable: ' . $definition['type']);
			}
			$typeId = $types[$definition['type']];
			$result = $db->sql_query_limit(
				'SELECT notification_id FROM ' . NOTIFICATIONS_TABLE . '
					WHERE notification_type_id = ' . $typeId . '
						AND item_id = ' . (int) $definition['item'] . '
						AND user_id = ' . $recipient,
				1
			);
			$notificationId = (int) $db->sql_fetchfield('notification_id');
			$db->sql_freeresult($result);
			if (!$notificationId)
			{
				$db->sql_query('INSERT INTO ' . NOTIFICATIONS_TABLE . ' ' . $db->sql_build_array('INSERT', [
					'notification_type_id' => $typeId,
					'item_id' => (int) $definition['item'],
					'item_parent_id' => (int) $definition['parent'],
					'user_id' => $recipient,
					'notification_read' => 0,
					'notification_time' => time(),
					'notification_data' => serialize($definition['data']),
				]));
				$notificationId = $this->lastInsertedId();
			}
			$this->context->addId('notifications', $notificationId);
		}
	}

	private function refreshSearchIndex(): void
	{
		$searchClass = (string) ($this->context->config['search_type'] ?? '\\phpbb\\search\\fulltext_native');
		if ($this->context->container->has('search.backend_factory'))
		{
			$search = $this->context->container->get('search.backend_factory')->get_active();
		}
		else
		{
			if (!class_exists($searchClass))
			{
				throw new RuntimeException("Configured phpBB search backend is unavailable: $searchClass");
			}
			$error = false;
			/** @noinspection PhpConditionAlreadyCheckedInspection */
			$search = new $searchClass(
				$error,
				$this->context->root,
				$this->context->phpEx,
				$this->context->auth,
				$this->context->config,
				$this->context->db,
				$this->context->user,
				$GLOBALS['phpbb_dispatcher']
			);
			/** @noinspection PhpConditionAlreadyCheckedInspection */
			if ($error)
			{
				throw new RuntimeException("Configured phpBB search backend failed: $error");
			}
		}
		if (!method_exists($search, 'index'))
		{
			throw new RuntimeException("Configured phpBB search backend cannot index posts: $searchClass");
		}
		$postIds = $this->context->ids('posts');
		$result = $this->context->db->sql_query('SELECT post_id, post_subject, post_text, poster_id, forum_id
			FROM ' . POSTS_TABLE . '
			WHERE ' . $this->context->db->sql_in_set('post_id', $postIds));
		while ($post = $this->context->db->sql_fetchrow($result))
		{
			$search->index(
				'edit',
				(int) $post['post_id'],
				$post['post_text'],
				$post['post_subject'],
				(int) $post['poster_id'],
				(int) $post['forum_id']
			);
		}
		$this->context->db->sql_freeresult($result);
		$this->context->setMeta('search_backend', $searchClass);
	}
}
