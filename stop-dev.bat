@echo off
REM SPD Sports Therapy - Stop Development Environment
REM This script stops and removes the Docker containers

echo.
echo ================================================
echo   SPD Sports Therapy - Stopping Environment
echo ================================================
echo.

REM Stop and remove containers
echo Stopping Docker services...
docker-compose down

if %errorlevel% neq 0 (
    echo.
    echo ERROR: Failed to stop Docker Compose
    echo.
    pause
    exit /b 1
)

echo.
echo [OK] Services stopped successfully
echo.
pause
