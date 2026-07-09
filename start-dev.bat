@echo off
REM SPD Sports Therapy - Start Development Environment
REM This script starts the Docker containers for PHP and MySQL development

echo.
echo ================================================
echo   SPD Sports Therapy - Development Environment
echo ================================================
echo.

REM Check if Docker is running
echo Checking Docker availability...
docker ps > nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Docker Desktop is not running!
    echo.
    echo Please start Docker Desktop and run this script again.
    echo.
    pause
    exit /b 1
)

echo [OK] Docker is running
echo.

REM Check if .env file exists
if not exist ".env" (
    echo [INFO] .env file not found, creating from .env.example...
    copy ".env.example" ".env"
    echo [OK] .env file created
    echo.
    echo ^!^!^! IMPORTANT ^!^!^!
    echo.
    echo Before starting, please update .env with your Gmail credentials:
    echo   1. Go to https://myaccount.google.com/apppasswords
    echo   2. Generate an app password
    echo   3. Update MAIL_USERNAME and MAIL_PASSWORD in .env
    echo.
    echo You can edit .env with any text editor.
    echo.
    pause
)

REM Start Docker Compose
echo Starting services...
docker-compose up

if %errorlevel% neq 0 (
    echo.
    echo ERROR: Failed to start Docker Compose
    echo.
    pause
    exit /b 1
)
