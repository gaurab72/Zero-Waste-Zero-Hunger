<#!
.SYNOPSIS
Creates a Git backup of this project and uploads it to GitHub.

.DESCRIPTION
Run from this project's folder:
  .\backup.ps1

The first time, connect a GitHub repository by running:
  git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git
#>

$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot

if (-not (git remote get-url origin 2>$null)) {
    throw "No GitHub repository is connected. Add one with: git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPOSITORY.git"
}

git add --all

if (git diff --cached --quiet) {
    Write-Host 'No project changes to back up.'
    exit 0
}

$timestamp = Get-Date -Format 'yyyy-MM-dd HH:mm'
git commit -m "Backup: $timestamp"
git push origin HEAD

Write-Host 'Backup complete.'
