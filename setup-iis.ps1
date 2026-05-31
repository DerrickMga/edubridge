#Requires -RunAsAdministrator
# EduBridge IIS Site Setup Script
# Run with: Right-click -> Run with PowerShell (as Administrator)

$SiteName      = "EduBridge"
$PhysicalPath  = "C:\inetpub\wwwroot\edubridge\public"
$HostName      = "edu.kmgvitallinks.co.uk"
$AppPoolName   = "EduBridge"
$PHP_EXE       = "C:\inetpub\wwwroot\tools\php-8.3\php.exe"

Import-Module WebAdministration -ErrorAction Stop

# 1. Create App Pool
if (-not (Test-Path "IIS:\AppPools\$AppPoolName")) {
    New-WebAppPool -Name $AppPoolName
    Write-Host "Created AppPool: $AppPoolName"
} else {
    Write-Host "AppPool already exists: $AppPoolName"
}

# Configure the App Pool (No Managed Code for PHP)
Set-ItemProperty "IIS:\AppPools\$AppPoolName" -Name managedRuntimeVersion -Value ""
Set-ItemProperty "IIS:\AppPools\$AppPoolName" -Name processModel.userName -Value "ApplicationPoolIdentity"

# 2. Create the Site (HTTP on 80 first; HTTPS will be added by SSL binding)
$existingSite = Get-Website -Name $SiteName -ErrorAction SilentlyContinue
if (-not $existingSite) {
    New-Website -Name $SiteName `
                -PhysicalPath $PhysicalPath `
                -HostHeader $HostName `
                -Port 80 `
                -ApplicationPool $AppPoolName
    Write-Host "Created IIS site: $SiteName -> $PhysicalPath"
} else {
    Write-Host "Site already exists: $SiteName"
    # Update physical path if needed
    Set-ItemProperty "IIS:\Sites\$SiteName" -Name physicalPath -Value $PhysicalPath
}

# 3. Add HTTPS binding (port 443) — requires SSL cert already in IIS
# If you have a wildcard or SAN cert for *.kmgvitallinks.co.uk, get its thumbprint:
$cert = Get-ChildItem Cert:\LocalMachine\My | Where-Object {
    $_.Subject -match "kmgvitallinks\.co\.uk" -or $_.Subject -match "edu\.kmgvitallinks"
} | Sort-Object NotAfter -Descending | Select-Object -First 1

if ($cert) {
    $thumbprint = $cert.Thumbprint
    Write-Host "Found SSL cert: $thumbprint (expires $($cert.NotAfter))"

    # Remove existing HTTPS binding if present, then re-add
    $existingHttps = Get-WebBinding -Name $SiteName -Protocol "https" -ErrorAction SilentlyContinue
    if ($existingHttps) { Remove-WebBinding -Name $SiteName -Protocol "https" -Port 443 }

    New-WebBinding -Name $SiteName -Protocol "https" -Port 443 -HostHeader $HostName -SslFlags 1
    $binding = Get-WebBinding -Name $SiteName -Protocol "https" -Port 443
    $binding.AddSslCertificate($thumbprint, "My")
    Write-Host "HTTPS binding added with SNI."
} else {
    Write-Host "WARNING: No SSL cert found for kmgvitallinks.co.uk in LocalMachine\My."
    Write-Host "         The site will run on HTTP only until a cert is installed."
    Write-Host "         To get a free cert, run: winacme.exe --target manual --host edu.kmgvitallinks.co.uk"
}

# 4. Ensure the site is started
Start-Website -Name $SiteName -ErrorAction SilentlyContinue
Write-Host ""
Write-Host "Done. Site status:"
Get-Website -Name $SiteName | Select-Object Name, State, PhysicalPath | Format-List
Write-Host ""
Write-Host "Test: http://$HostName"
