Set oShell = CreateObject("Shell.Application")
oShell.ShellExecute "cmd.exe", "/c """ & "C:\inetpub\wwwroot\edubridge\setup-iis-admin.bat" & """", "", "runas", 1
