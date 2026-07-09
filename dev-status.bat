@echo off
REM SPD Sports Therapy - Development Environment Status
REM This script shows the status of Docker containers

echo.
echo ================================================
echo   SPD Sports Therapy - Services Status
echo ================================================
echo.

REM Show running containers
echo Running Containers:
echo.
docker-compose ps

echo.
echo ================================================
echo   Service URLs
echo ================================================
echo.
echo Website:     http://localhost:8000/
echo Admin Panel: http://localhost:8000/admin/
echo Database:    localhost:3306
echo.

REM Show recent logs
echo.
echo ================================================
echo   Recent Logs (last 20 lines)
echo ================================================
echo.
docker-compose logs --tail=20

echo.
pause
