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

/** Coordinates count-based tiny, extension-dev, load-test, and random plans. */
class VolumeSeeder extends SeedPlan
{
	public function seed(): void
	{
		$counts = $this->resolveCounts();
		$this->context->setMeta('counts', $counts);

		$this->context->status('Creating ' . $counts['users'] . ' ' . $this->context->preset . ' '
			. $this->plural((int) $counts['users'], 'user', 'users') . '...');
		$users = (new UserBuilder($this->context))->seedVolume($counts['users'], $counts['groups']);
		$this->context->saveManifest();

		$forumCount = (int) $counts['categories'] * (int) $counts['forums_per_category'];
		$this->context->status('Creating ' . $counts['categories'] . ' '
			. $this->plural((int) $counts['categories'], 'category', 'categories') . " and $forumCount "
			. $this->plural($forumCount, 'forum', 'forums') . '...');
		$forums = (new ForumBuilder($this->context))->seedVolume(
			$counts['categories'],
			$counts['forums_per_category']
		);
		$this->context->saveManifest();

		$this->context->status('Creating ' . $counts['topics'] . ' '
			. $this->plural((int) $counts['topics'], 'topic', 'topics') . ' with ' . $counts['replies'] . ' '
			. $this->plural((int) $counts['replies'], 'reply', 'replies') . ' each...');
		(new ContentBuilder($this->context))->seedVolume(
			$forums,
			$users,
			$counts['topics'],
			$counts['replies']
		);
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
		$this->assertChecks($checks, "{$this->context->preset} seed verification failed: ");

		echo "Seeded {$this->context->preset} preset {$this->context->seed}: {$counts['users']} "
			. $this->plural((int) $counts['users'], 'user', 'users') . ", {$counts['categories']} "
			. $this->plural((int) $counts['categories'], 'category', 'categories') . ", $expectedForums "
			. $this->plural($expectedForums, 'forum', 'forums') . ", {$counts['topics']} "
			. $this->plural((int) $counts['topics'], 'topic', 'topics') . ", $expectedPosts "
			. $this->plural($expectedPosts, 'post', 'posts') . ".\n";
	}

	/** @noinspection RandomApiMigrationInspection */
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
}
