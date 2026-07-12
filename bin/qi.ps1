$php = Get-Command php -ErrorAction SilentlyContinue
if (-not $php) {
	[Console]::Error.WriteLine('QuickInstall requires PHP 8.0 or newer.')
	[Console]::Error.WriteLine('Install PHP, add php.exe to PATH, then reopen your terminal.')
	exit 1
}

$qi = Join-Path $PSScriptRoot 'qi'
& $php.Source $qi @args
exit $LASTEXITCODE
