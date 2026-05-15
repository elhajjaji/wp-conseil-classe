# Génère conseil-classe.zip (et conseil-classe-VERSION.zip) avec UNE seule racine : conseil-classe/
# Usage : depuis la racine du dépôt  .\scripts\build-plugin-zip.ps1
# Incrémente automatiquement la version patch dans conseil-classe.php et readme.txt

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path (Join-Path $repoRoot 'conseil-classe\conseil-classe.php'))) {
    throw "Exécutez ce script depuis le dépôt (dossier conseil-classe attendu à la racine)."
}

$srcDir = Join-Path $repoRoot 'conseil-classe'
$mainPhp = Join-Path $srcDir 'conseil-classe.php'
$readmeTxt = Join-Path $srcDir 'readme.txt'
if (-not (Test-Path $mainPhp)) { throw "Dossier conseil-classe introuvable : $srcDir" }

$verLine = Select-String -Path $mainPhp -Pattern '^\s*\*\s*Version:\s*' | Select-Object -First 1
if (-not $verLine) { throw "Version introuvable dans conseil-classe.php" }
$versionBefore = ($verLine.Line -replace '.*?Version:\s*', '').Trim()
if ($versionBefore -notmatch '^(\d+)\.(\d+)\.(\d+)$') {
    throw "Version non supportée pour auto-incrément : $versionBefore"
}

$major = [int] $Matches[1]
$minor = [int] $Matches[2]
$patch = [int] $Matches[3] + 1
$version = "$major.$minor.$patch"

$phpContent = Get-Content -Raw -Path $mainPhp
$phpContent = [regex]::Replace($phpContent, '^\s*\*\s*Version:\s*.*$', " * Version:           $version", [System.Text.RegularExpressions.RegexOptions]::Multiline, [TimeSpan]::FromSeconds(2))
$phpContent = [regex]::Replace($phpContent, "^define\('CC_PLUGIN_VERSION',\s*'.*'\);$", "define('CC_PLUGIN_VERSION', '$version');", [System.Text.RegularExpressions.RegexOptions]::Multiline, [TimeSpan]::FromSeconds(2))
Set-Content -Path $mainPhp -Value $phpContent -Encoding UTF8

if (Test-Path $readmeTxt) {
    $readmeContent = Get-Content -Raw -Path $readmeTxt
    $readmeContent = [regex]::Replace($readmeContent, '^\s*Stable tag:\s*.*$', "Stable tag: $version", [System.Text.RegularExpressions.RegexOptions]::Multiline, [TimeSpan]::FromSeconds(2))
    Set-Content -Path $readmeTxt -Value $readmeContent -Encoding UTF8
}

Write-Host "Version incrémentée : $versionBefore -> $version"

$tmpRoot = Join-Path $env:TEMP ("cc-zipbuild-" + [guid]::NewGuid().ToString('N').Substring(0, 8))
$build = Join-Path $tmpRoot 'conseil-classe'
try {
    New-Item -ItemType Directory -Path $build -Force | Out-Null
    Get-ChildItem $srcDir -Force | ForEach-Object {
        Copy-Item -LiteralPath $_.FullName -Destination (Join-Path $build $_.Name) -Recurse -Force
    }

    $stableZip = Join-Path $repoRoot 'conseil-classe.zip'
    $versionZip = Join-Path $repoRoot "conseil-classe-$version.zip"
    foreach ($z in @($stableZip, $versionZip)) {
        if (Test-Path $z) { Remove-Item $z -Force }
    }
    Compress-Archive -Path $build -DestinationPath $stableZip -CompressionLevel Optimal -Force
    Copy-Item $stableZip $versionZip -Force

    Write-Host "OK : $stableZip"
    Write-Host "OK : $versionZip"

    $first = (tar -tf $stableZip | Select-Object -First 1).ToString().Trim()
    if ($first -notmatch '^conseil-classe/') {
        throw "Structure ZIP invalide (attendu conseil-classe/...) : $first"
    }
} finally {
    Remove-Item $tmpRoot -Recurse -Force -ErrorAction SilentlyContinue
}
