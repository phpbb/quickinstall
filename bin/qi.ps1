$qi = Join-Path $PSScriptRoot 'qi'
& php $qi @args
exit $LASTEXITCODE
