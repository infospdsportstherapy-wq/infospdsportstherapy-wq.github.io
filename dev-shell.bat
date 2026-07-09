@echo off
REM SPD Sports Therapy - Access PHP Container Shell
REM This script opens a bash shell inside the PHP container

echo.
echo ================================================
echo   SPD Sports Therapy - Container Shell
echo ================================================
echo.

REM Check if Docker is running
docker ps > nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Docker Desktop is not running!
    echo.
    pause
    exit /b 1
)

REM Check if PHP container is running
docker-compose ps spd-php | find "Up" > nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ERROR: PHP container is not running!
    echo.
    echo Start the environment first with: start-dev.bat
    echo.
    pause
    exit /b 1
)

echo Opening shell in PHP container...
echo.
echo You are now inside the PHP container at /var/www/html
echo Type 'exit' to return to Windows
echo.

REM Access the container shell
docker-compose exec -it spd-php bash
