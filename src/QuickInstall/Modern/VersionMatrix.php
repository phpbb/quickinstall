<?php

namespace QuickInstall\Modern;

class VersionMatrix
{
	public function runtimeFor(string $version): array
	{
		if ($version === 'latest' || preg_match('/^(master|main|4\.|dev-)/', $version))
		{
			return ['php' => '8.2'];
		}

		if (version_compare($version, '3.3.0', '>='))
		{
			return ['php' => '8.1'];
		}

		if (version_compare($version, '3.2.2', '>='))
		{
			return ['php' => '7.2'];
		}

		if (version_compare($version, '3.2.0', '>='))
		{
			return ['php' => '7.1'];
		}

		return ['php' => '5.6'];
	}
}
