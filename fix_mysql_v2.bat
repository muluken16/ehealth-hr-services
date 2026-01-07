@echo off
echo Resetting MySQL Port if needed...
netstat -ano | findstr :3306
if %ERRORLEVEL% EQU 0 (
    echo Port 3306 is currently in use! Finding process...
    for /f "tokens=5" %%a in ('netstat -aon ^| findstr :3306') do (
        echo Killing process ID %%a which is blocking MySQL...
        taskkill /F /PID %%a
    )
) else (
    echo Port 3306 is free.
)
echo.
echo Running basic InnoDb log reset...
set "MYSQL_DATA=C:\xampp\mysql\data"
if exist "%MYSQL_DATA%\ib_logfile0" del /f "%MYSQL_DATA%\ib_logfile0"
if exist "%MYSQL_DATA%\ib_logfile1" del /f "%MYSQL_DATA%\ib_logfile1"
echo Done. Try starting MySQL again.
pause
