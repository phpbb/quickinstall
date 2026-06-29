<?php

namespace QuickInstall\Modern;

class BoardRunner
{
	private Project $project;

	public function __construct(Project $project)
	{
		$this->project = $project;
	}

	public function start(string $name): void
	{
		$board = $this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'up', '--build', '-d']);
		$this->waitUntilInstalled($name);
		$this->seedIfNeeded($name, $board['populate'] ?? 'none');
	}

	public function stop(string $name): void
	{
		$this->project->board($name);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'stop']);
	}

	public function destroy(string $name): void
	{
		$this->project->board($name);
		$compose = $this->project->composePath($name);
		if (file_exists($compose))
		{
			$this->run(['docker', 'compose', '-f', $compose, 'down', '--volumes', '--remove-orphans']);
		}

		$this->project->deleteTree($this->project->boardPath($name));
		$this->project->deleteTree($this->project->runtimePath($name));
		$this->project->deleteTree($this->project->dbPath($name));
		$this->project->removeBoard($name);
	}

	public function status(string $name): string
	{
		$compose = $this->project->composePath($name);
		if (!file_exists($compose))
		{
			return 'missing';
		}

		$result = $this->capture(['docker', 'compose', '-f', $compose, 'ps', '--format', 'json']);
		if ($result['exit_code'] !== 0)
		{
			return 'error';
		}

		$output = trim($result['output']);
		if ($output === '')
		{
			return 'stopped';
		}

		$containers = [];
		foreach (explode("\n", $output) as $line)
		{
			$data = json_decode($line, true);
			if (is_array($data))
			{
				$containers[] = $data;
			}
		}

		if (!$containers)
		{
			$data = json_decode($output, true);
			$containers = is_array($data) && isset($data[0]) ? $data : [];
		}

		if (!$containers)
		{
			return 'stopped';
		}

		$running = 0;
		foreach ($containers as $container)
		{
			$state = strtolower((string) ($container['State'] ?? ''));
			if ($state === 'running')
			{
				$running++;
			}
		}

		if ($running === count($containers))
		{
			return 'running';
		}

		return $running > 0 ? 'partial' : 'stopped';
	}

	public function seed(string $name, string $preset, int $seed): void
	{
		$this->project->board($name);
		$this->runSeeder($name, $preset, $seed);
	}

	private function seedIfNeeded(string $name, string $preset): void
	{
		if ($preset === 'none' || $preset === '')
		{
			return;
		}

		$marker = $this->project->runtimePath($name) . '/seeded-' . preg_replace('/[^A-Za-z0-9._-]/', '_', $preset);
		if (file_exists($marker))
		{
			echo "Populate preset already applied: $preset\n";
			return;
		}

		$this->runSeeder($name, $preset, 1);
		file_put_contents($marker, gmdate('c') . "\n");
	}

	private function waitUntilInstalled(string $name): void
	{
		$boardPath = $this->project->boardPath($name);
		$deadline = time() + 120;

		while (time() <= $deadline)
		{
			$config = $boardPath . '/config.php';
			if (file_exists($boardPath . '/includes/startup.php') && file_exists($config) && filesize($config) > 0 && !is_dir($boardPath . '/install'))
			{
				return;
			}

			usleep(500000);
		}

		throw new \RuntimeException("Timed out waiting for phpBB install to complete for board: $name");
	}

	private function runSeeder(string $name, string $preset, int $seed): void
	{
		$writer = new SeederWriter($this->project);
		$script = $writer->write($name);

		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'cp', $script, 'web:/tmp/qi_seed.php']);
		$this->run(['docker', 'compose', '-f', $this->project->composePath($name), 'exec', '-T', 'web', 'php', '/tmp/qi_seed.php', $preset, (string) $seed]);
	}

	private function run(array $command): void
	{
		echo '$ ' . implode(' ', array_map('escapeshellarg', $command)) . "\n";

		$descriptor = [
			0 => STDIN,
			1 => STDOUT,
			2 => STDERR,
		];

		$process = proc_open($command, $descriptor, $pipes);
		if (!is_resource($process))
		{
			throw new \RuntimeException('Unable to start command: ' . $command[0]);
		}

		$status = proc_close($process);
		if ($status !== 0)
		{
			throw new \RuntimeException("Command failed with exit code $status: {$command[0]}");
		}
	}

	private function capture(array $command): array
	{
		$descriptor = [
			0 => STDIN,
			1 => ['pipe', 'w'],
			2 => ['pipe', 'w'],
		];

		$process = proc_open($command, $descriptor, $pipes);
		if (!is_resource($process))
		{
			return ['exit_code' => 1, 'output' => ''];
		}

		$output = stream_get_contents($pipes[1]) ?: '';
		$error = stream_get_contents($pipes[2]) ?: '';
		fclose($pipes[1]);
		fclose($pipes[2]);

		return [
			'exit_code' => proc_close($process),
			'output' => $output . $error,
		];
	}
}
