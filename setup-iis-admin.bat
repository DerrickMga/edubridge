@echo off
:: EduBridge IIS Site Setup
:: RIGHT-CLICK this file → "Run as administrator"

net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Please right-click this file and select "Run as administrator"
    pause
    exit /b 1
)

echo Setting up EduBridge IIS site...

:: Create App Pool
"%SystemRoot%\system32\inetsrv\appcmd.exe" add apppool /name:"EduBridge" >nul 2>&1
"%SystemRoot%\system32\inetsrv\appcmd.exe" set apppool /apppool.name:"EduBridge" /managedRuntimeVersion:"" >nul 2>&1
echo [OK] App Pool: EduBridge

:: Create Site
"%SystemRoot%\system32\inetsrv\appcmd.exe" add site /name:"EduBridge" /physicalPath:"C:\inetpub\wwwroot\edubridge\public" /bindings:"http/*:80:edu.kmgvitallinks.co.uk" >nul 2>&1
"%SystemRoot%\system32\inetsrv\appcmd.exe" set site /site.name:"EduBridge" /[path='/'].applicationPool:"EduBridge" >nul 2>&1
echo [OK] Site: EduBridge -> http://edu.kmgvitallinks.co.uk

:: Add HTTPS binding (SNI)
"%SystemRoot%\system32\inetsrv\appcmd.exe" set site /site.name:"EduBridge" /+bindings.[protocol='https',bindingInformation='*:443:edu.kmgvitallinks.co.uk'] >nul 2>&1
echo [OK] HTTPS binding added (attach SSL cert in IIS Manager)

:: Start the site
"%SystemRoot%\system32\inetsrv\appcmd.exe" start site /site.name:"EduBridge" >nul 2>&1
echo [OK] Site started

echo.
echo Done! EduBridge is configured at http://edu.kmgvitallinks.co.uk
echo.
echo NEXT: In IIS Manager, bind your *.kmgvitallinks.co.uk SSL cert to the HTTPS binding.
echo.
pause
