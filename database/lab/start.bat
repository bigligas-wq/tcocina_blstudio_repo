@echo off
echo.
echo ==========================================
echo  BLStudio Lab — Servidor local
echo ==========================================
echo.
echo Levantando servidor en: http://localhost:8888
echo.
echo Cuando veas "Serving HTTP", abri este link:
echo   http://localhost:8888/Laboratorio.html
echo.
echo Para cerrar, apreta Ctrl+C y despues S
echo.
py -m http.server 8888
