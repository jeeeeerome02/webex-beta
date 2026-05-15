Add-Type -AssemblyName System.Drawing
=[System.Drawing.Image]::FromFile('c:/webex-beta/resources/js/assets/images/logo.png')
Write-Host .Width
Write-Host .Height
.Dispose()

