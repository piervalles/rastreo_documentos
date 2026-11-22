@echo off
title Servidor Local - DocuTrace
color 0b
echo ====================================================
echo        DOCUTRACE - SISTEMA DE RASTREO DOCUMENTAL
echo ====================================================
echo.
echo Iniciando servidor web local en el puerto 8000...
echo.
echo - Para entrar desde esta computadora:
echo   http://localhost:8000/instalar.php
echo.
echo - Para entrar desde otras computadoras del edificio:
echo   Revisa tu IP con 'ipconfig' (ej. http://192.168.1.X:8000)
echo.
echo Presiona Ctrl + C en esta ventana para apagar el servidor.
echo ====================================================
echo.

php -S 0.0.0.0:8000
pause
