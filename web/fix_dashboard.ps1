# Script to fix dashboard.html encoding issues and replace broken sections
$filepath = "C:\Users\us\Downloads\SIG-Condominio\web\pages\dashboard.html"
$enc = [System.Text.Encoding]::GetEncoding(1252)
$content = [System.IO.File]::ReadAllText($filepath, $enc)
Write-Host "File read OK, length: $($content.Length)"
Write-Host "Done"
