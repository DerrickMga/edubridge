Import-Module WebAdministration -ErrorAction Stop
$SiteName     = "EduBridge"
$PhysicalPath = "C:\inetpub\wwwroot\edubridge\public"
$HostName     = "edu.kmgvitallinks.co.uk"
$AppPoolName  = "EduBridge"
$ResultFile   = "C:\inetpub\wwwroot\edubridge\iis-setup-result.txt"

"Starting IIS setup $(Get-Date)" | Out-File $ResultFile

if (-not (Test-Path "IIS:\AppPools\$AppPoolName")) {
    New-WebAppPool -Name $AppPoolName
    Set-ItemProperty "IIS:\AppPools\$AppPoolName" -Name managedRuntimeVersion -Value ""
    "Created App Pool: $AppPoolName" | Add-Content $ResultFile
} else { "App Pool exists: $AppPoolName" | Add-Content $ResultFile }

if (-not (Get-Website -Name $SiteName -EA SilentlyContinue)) {
    New-Website -Name $SiteName -PhysicalPath $PhysicalPath -HostHeader $HostName -Port 80 -ApplicationPool $AppPoolName
    Start-Website -Name $SiteName
    "Created Site: $SiteName" | Add-Content $ResultFile
} else { "Site exists: $SiteName" | Add-Content $ResultFile }

$cert = Get-ChildItem Cert:\LocalMachine\WebHosting | Where-Object { $_.Subject -match "kmgvitallinks" } | Sort-Object NotAfter -Descending | Select-Object -First 1
if (-not $cert) { $cert = Get-ChildItem Cert:\LocalMachine\My | Where-Object { $_.Subject -match "kmgvitallinks" } | Sort-Object NotAfter -Descending | Select-Object -First 1 }
if ($cert) {
    $store = if ($cert.PSParentPath -match "WebHosting") { "WebHosting" } else { "My" }
    New-WebBinding -Name $SiteName -Protocol "https" -Port 443 -HostHeader $HostName -SslFlags 1 -EA SilentlyContinue
    $b = Get-WebBinding -Name $SiteName -Protocol "https" -Port 443 -EA SilentlyContinue
    if ($b) { $b.AddSslCertificate($cert.Thumbprint, $store) }
    "HTTPS cert bound: $($cert.Subject) expires $($cert.NotAfter)" | Add-Content $ResultFile
} else { "No cert found - run win-acme after site is up" | Add-Content $ResultFile }

"DONE $(Get-Date)" | Add-Content $ResultFile
(Get-Website -Name $SiteName | Format-List | Out-String) | Add-Content $ResultFile
