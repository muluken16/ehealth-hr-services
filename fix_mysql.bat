@echo off
set "MYSQL_DATA=C:\xampp\mysql\data"
echo ===================================================
echo   XAMPP MySQL EMERGENCY REPAIR SCRIPT
echo ===================================================
echo.
echo Phase 1: Killing ghost processes...
taskkill /f /im mysqld.exe /t 2>nul
echo Done.
echo.
echo Phase 2: Removing corrupted control and log files...
if exist "%MYSQL_DATA%\aria_log_control" (
    del /f "%MYSQL_DATA%\aria_log_control"
    echo - Deleted aria_log_control
)
if exist "%MYSQL_DATA%\ib_logfile0" (
    del /f "%MYSQL_DATA%\ib_logfile0"
    echo - Deleted ib_logfile0
)
if exist "%MYSQL_DATA%\ib_logfile1" (
    del /f "%MYSQL_DATA%\ib_logfile1"
    echo - Deleted ib_logfile1
)
if exist "%MYSQL_DATA%\mysql.pid" (
    del /f "%MYSQL_DATA%\mysql.pid"
    echo - Deleted stale PID file
)
echo.
echo Phase 3: Verification...
echo Log files cleared. MySQL will recreate them fresh on next start.
echo.
echo ===================================================
echo SUCCESS: Please try starting MySQL in XAMPP now.
echo ===================================================
pause
