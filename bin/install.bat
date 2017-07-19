@echo off
echo "Before coninuing please ensure you have followed the steps on the wiki:"
echo "\t1.Install XAMPP"
echo "\t2.Add PHP to your system environment path"
pause
echo "Installing appropriate libraries"
cd ..
composer install
echo "Install complete"
pause
