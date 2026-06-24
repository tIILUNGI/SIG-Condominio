$source = "pages\dashboard_utf8.html"
$target = "pages\dashboard.html"
if (Test-Path $source) {
    $content = [System.IO.File]::ReadAllText($source, [System.Text.Encoding]::UTF8)
    # Ensure the content has the JS and correct margins
    # The dashboard_utf8.html was created before I added some JS in fix_dashboard_v2.ps1
    # So I should probably just write a clean new dashboard.html
}
