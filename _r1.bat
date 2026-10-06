@echo off
cd /d D:\ohmywashpos
del /q _sB.txt _stepB.bat 2>nul
echo ===GIT-STATUS=== > _p1.txt 2>&1
git status --short --branch >> _p1.txt 2>&1
echo. >> _p1.txt
echo ===TRACKED-JUNK=== >> _p1.txt
git ls-files | findstr /R /I "^_.*\.(txt|bat|ps1)$ audit_.* winget.*\.log$" >> _p1.txt 2>&1
if errorlevel 1 echo no-tracked-junk >> _p1.txt
echo. >> _p1.txt
echo ===LOOSE-JUNK=== >> _p1.txt
dir /b _*.txt _*.bat audit_*.ps1 2>nul >> _p1.txt
if errorlevel 1 echo no-loose-junk >> _p1.txt
echo DONE >> _p1.txt
