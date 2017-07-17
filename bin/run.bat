@echo off
set /p dir="Enter filepath of folder containing invoiced files: "
php ../main.php --dir %dir%
pause