# Génère conseil-classe.zip (et conseil-classe-VERSION.zip) avec UNE seule racine : conseil-classe/
# Usage : depuis la racine du dépôt  .\scripts\build-plugin-zip.ps1
# Utilise la version déjà déclarée dans conseil-classe.php et readme.txt

$ErrorActionPreference = 'Stop'
$repoRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path (Join-Path $repoRoot 'conseil-classe\conseil-classe.php'))) {
    throw "Exécutez ce script depuis le dépôt (dossier conseil-classe attendu à la racine)."
}

$srcDir = Join-Path $repoRoot 'conseil-classe'
$mainPhp = Join-Path $srcDir 'conseil-classe.php'
if (-not (Test-Path $mainPhp)) { throw "Dossier conseil-classe introuvable : $srcDir" }

$verLine = Select-String -Path $mainPhp -Pattern '^\s*\*\s*Version:\s*' | Select-Object -First 1
if (-not $verLine) { throw "Version introuvable dans conseil-classe.php" }
$version = ($verLine.Line -replace '.*?Version:\s*', '').Trim()
if ($version -notmatch '^(\d+)\.(\d+)\.(\d+)$') {
    throw "Version non supportée : $version"
}

Write-Host "Version détectée : $version"

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
