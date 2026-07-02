@echo off
cd /d "C:\Users\BigLigas\Desktop\Proyectos\Cowork_claude\tcocina\13-5-26\13-5-26"
title TCocina - Laravel Server
echo Iniciando servidor Laravel en http://127.0.0.1:8000 ...
php artisan serve --host=127.0.0.1 --port=8000
pause
