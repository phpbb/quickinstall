<?php
/**
 *
 * QuickInstall seed plans
 *
 * @copyright (c) 2026 phpBB Limited <https://www.phpbb.com>
 * @license       GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace QuickInstallSeed;

use RuntimeException;

/** Provides shared context and verification helpers for preset plans. */
abstract class SeedPlan
{
	/**
	 * @var SeedContext
	 * @noinspection PhpMissingFieldTypeInspection
	 */
	protected $context;

	public function __construct(SeedContext $context)
	{
		$this->context = $context;
	}

	protected function existingIdCount(string $table, string $column, array $ids): int
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

	protected function fieldCount(string $table, string $column, array $ids, string $condition): int
	{
		if (!$ids)
		{
			return 0;
		}
		$result = $this->context->db->sql_query("SELECT COUNT($column) AS total FROM $table WHERE "
			. $this->context->db->sql_in_set($column, $ids) . " AND $condition");
		$count = (int) $this->context->db->sql_fetchfield('total');
		$this->context->db->sql_freeresult($result);
		return $count;
	}

	protected function queryCount(string $table, string $where): int
	{
		$result = $this->context->db->sql_query("SELECT COUNT(*) AS total FROM $table WHERE $where");
		$count = (int) $this->context->db->sql_fetchfield('total');
		$this->context->db->sql_freeresult($result);
		return $count;
	}

	protected function assertChecks(array $checks, string $failureMessage): void
	{
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
			throw new RuntimeException($failureMessage . implode(', ', $failed));
		}
	}

	protected function plural(int $count, string $singular, string $plural): string
	{
		return $count === 1 ? $singular : $plural;
	}
}
