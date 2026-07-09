# SPD Sports Therapy - Backend Development Setup Guide

## 🚀 Quick Start

### Prerequisites
- **Docker Desktop** (https://www.docker.com/products/docker-desktop) - Includes Docker and Docker Compose
- **Windows 10/11** with WSL2 support (recommended)
- **Gmail Account** (for email notifications)

### First Time Setup (5 minutes)

1. **Install Docker Desktop**
   - Download from https://www.docker.com/products/docker-desktop
   - Install and restart your computer
   - Verify installation: Open PowerShell and run `docker --version`

2. **Configure Gmail SMTP**
   - Go to https://myaccount.google.com/apppasswords
   - Select "Mail" and "Windows Computer"
   - Copy the 16-character app password generated
   - Save it somewhere safe temporarily

3. **Start Development Environment**
   - Double-click `start-dev.bat` in the project root
   - The script will create `.env` from `.env.example`
   - When prompted, update `.env` with:
     - `MAIL_USERNAME`: your Gmail address
     - `MAIL_PASSWORD`: the 16-character app password
   - Save and close `.env`
   - Press Enter in the batch file to continue
   - Wait 30-60 seconds for services to start

4. **Verify Everything Works**
   - Open browser and go to http://localhost:8000
   - You should see your SPD Sports Therapy website
   - Try submitting a test review to verify the system

---

## 📁 Project Structure

```
SPD-Sports-therapy/
├── .env                          # Environment variables (DO NOT commit)
├── .env.example                  # Template for .env
├── docker-compose.yml            # Docker container configuration
├── Dockerfile                    # PHP container custom setup
├── composer.json                 # PHP dependencies (PHPMailer, etc.)
├── start-dev.bat                 # Start development environment
├── stop-dev.bat                  # Stop development environment
├── dev-status.bat                # Show services status
├── dev-shell.bat                 # Access PHP container shell
│
├── config/                       # Configuration files
│   ├── config.php                # Environment loader and helpers
│   └── email.php                 # Email service configuration
│
├── api/                          # Backend API endpoints
│   ├── submit_review.php         # Submit new review
│   ├── admin_login.php           # Admin authentication
│   ├── get_reviews.php           # Get random approved reviews
│   ├── get_all_reviews.php       # Admin: Get all reviews
│   ├── approve_review.php        # Admin: Approve review
│   ├── email_action.php          # Handle email token actions
│   ├── send_review_notification.php
│   ├── resend_review_email.php
│   ├── check_auth.php
│   └── admin_logout.php
│
├── database/
│   ├── database.sql              # Database schema and seed data
│   └── init.sh                   # Auto-initialization script
│
├── templates/emails/             # Email HTML templates
│   ├── admin-notification.php    # Email to admin about new review
│   ├── review-approved.php       # Email to reviewer (approved)
│   └── review-rejected.php       # Email to reviewer (rejected)
│
├── css/
├── js/
├── images/
└── index.html, review.html, etc.

```

---

## 🐳 Using Docker Commands

### Basic Commands

**Start Services**
```bash
start-dev.bat    # Windows batch file (easiest)
```

**Stop Services**
```bash
stop-dev.bat
```

**Check Status**
```bash
dev-status.bat   # Shows running containers and recent logs
```

**Access PHP Shell** (for running commands inside container)
```bash
dev-shell.bat
# Then inside the container:
cd /var/www/html
composer install   # Install PHP dependencies
php -v             # Check PHP version
exit               # Return to Windows
```

---

## ⚙️ Configuration

### Environment Variables (.env)

The `.env` file controls all configuration. Example values:

```
# Database (Docker names)
DB_HOST=mysql
DB_NAME=spd_sports_therapy
DB_USER=spd_user
DB_PASSWORD=dev_password_123
DB_PORT=3306

# Site URLs
SITE_URL=http://localhost:8000
ADMIN_EMAIL=info.spdsportstherapy@gmail.com

# Gmail SMTP Email Service
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-16-char-app-password
MAIL_FROM_NAME=SPD Sports Therapy
MAIL_FROM_EMAIL=your-email@gmail.com
```

### Setting Up Gmail SMTP

1. **Create App Password**
   - Login to https://myaccount.google.com/security
   - Scroll to "App passwords" (if not visible, enable 2FA first)
   - Select "Mail" and "Windows Computer"
   - Google generates a 16-character password

2. **Update .env**
   - `MAIL_USERNAME` = your Gmail email address
   - `MAIL_PASSWORD` = the 16-character password (no spaces)

3. **Test Email Sending**
   - Submit a test review via the form
   - Check if admin receives email
   - If not, check logs: `dev-status.bat`

---

## 🗄️ Database

### Database Details

- **Host**: mysql (inside Docker) or localhost:3306 (from Windows)
- **Database**: spd_sports_therapy
- **User**: spd_user (or root)
- **Password**: See .env file

### Database Tables

1. **users** - Reviewer profiles
2. **reviews** - Review submissions
3. **admin_users** - Admin accounts
4. **email_tokens** - One-time email action tokens

### Auto-Setup

Database schema and seed data are automatically created on first startup via `database/init.sh`.

### Manual Database Access

```bash
# From Windows, using MySQL client (if installed):
mysql -h localhost -u spd_user -p spd_sports_therapy

# OR from inside PHP container:
dev-shell.bat
mysql -h mysql -u spd_user -p spd_sports_therapy
```

---

## 📧 Email Service

### How Email Works

1. **User submits review** → Form sends to `api/submit_review.php`
2. **Admin notification** → Email sent to ADMIN_EMAIL with approve/reject links
3. **Email token created** → Unique token generated for each action
4. **Admin clicks link** → `api/email_action.php` handles approval/rejection
5. **Confirmation email** → Reviewer receives notification

### Email Templates

Located in `templates/emails/`:

- `admin-notification.php` - Sent to admin when new review submitted
- `review-approved.php` - Sent to reviewer when review approved
- `review-rejected.php` - Sent to reviewer when review rejected

### Testing Email

1. Ensure `.env` has valid Gmail credentials
2. Submit a test review via http://localhost:8000/review.html
3. Check email inbox for admin notification
4. Click approve/reject links in email
5. Check reviewer email for confirmation

---

## 🔧 API Endpoints

### Public Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/submit_review.php` | POST | Submit new review |
| `/api/get_reviews.php` | GET | Get random approved reviews |
| `/api/email_action.php` | GET | Handle email approval/rejection tokens |

### Admin Endpoints (Requires Session)

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/admin_login.php` | POST | Admin login |
| `/api/admin_logout.php` | POST | Admin logout |
| `/api/check_auth.php` | GET | Verify admin session |
| `/api/get_all_reviews.php` | GET | Get all reviews (dashboard) |
| `/api/approve_review.php` | POST | Approve/reject review |
| `/api/resend_review_email.php` | POST | Resend approval/rejection email |

---

## 🛠️ Troubleshooting

### Docker won't start

**Error**: "Docker Desktop is not running"

**Solution**:
1. Open Docker Desktop application
2. Wait for it to fully start (icon should be solid, not animated)
3. Run `start-dev.bat` again

### Services crash on startup

**Error**: Container exits immediately

**Solution**:
1. Run `dev-status.bat` to check logs
2. Most common cause: Port 8000 or 3306 already in use
3. Stop other services: `docker ps` and `docker kill <container>`
4. Run `stop-dev.bat` first, then `start-dev.bat` again

### Emails not sending

**Error**: Review submitted but no email received

**Solution**:
1. Check `.env` has correct Gmail credentials
2. Verify Gmail app password (not regular password)
3. Check logs: `dev-status.bat`
4. Test with different email address
5. Check Gmail security settings allow app access

### Database connection error

**Error**: "Connection refused" or "Cannot connect to host"

**Solution**:
1. Ensure MySQL container is running: `dev-status.bat`
2. If not running, restart: `stop-dev.bat` then `start-dev.bat`
3. Wait 10-15 seconds for MySQL to be ready
4. Tables auto-created on startup, check logs

### PHP errors in browser

**Error**: Website shows "Fatal error" or blank page

**Solution**:
1. Check browser console (F12) for errors
2. Check Docker logs: `dev-status.bat`
3. Access container shell: `dev-shell.bat`
4. Check PHP error logs: `tail -f /var/log/apache2/error.log`

---

## 📝 Environment Switching

### Production Setup

When deploying to production:

1. Create separate `.env.production` (never commit)
2. Update:
   - `DB_HOST` - Production database server
   - `DB_PASSWORD` - Secure production password
   - `SITE_URL` - Production domain (https://yourdomain.com)
   - `MAIL_USERNAME` / `MAIL_PASSWORD` - Production email service
   - `APP_ENV=production`
   - `APP_DEBUG=0` (disable debug mode)

3. Use production Docker Compose overlay or environment variables

### Local Testing

- Development `.env` uses Docker container names
- Database: `DB_HOST=mysql` (inside Docker network)
- For Windows access: `localhost:3306`

---

## 🚀 Development Workflow

### Making Changes

1. **PHP/API Changes**:
   - Edit files in `api/` folder
   - Changes take effect immediately (volume-mounted)
   - No restart needed

2. **Database Changes**:
   - Edit `database/database.sql`
   - Restart: `stop-dev.bat` then `start-dev.bat`
   - Or manually run: `dev-shell.bat` → `mysql ... < database.sql`

3. **Configuration Changes**:
   - Edit `.env` file
   - Reload PHP: `dev-shell.bat` → `systemctl restart apache2`
   - Or restart Docker: `stop-dev.bat` then `start-dev.bat`

### Installing PHP Dependencies

```bash
dev-shell.bat
composer install
exit
```

---

## 📚 Additional Resources

- **Docker**: https://docs.docker.com/
- **PHPMailer**: https://github.com/PHPMailer/PHPMailer
- **MySQL**: https://dev.mysql.com/doc/
- **PHP PDO**: https://www.php.net/manual/en/book.pdo.php

---

## 🔐 Security Notes

### Development vs Production

**Development** (.env):
- Empty/weak passwords OK for local testing
- Debug mode ON
- No SSL/HTTPS required

**Production**:
- Strong passwords required
- Debug mode OFF
- SSL/HTTPS enforced
- Separate `.env.production`
- Firewall rules for database access
- Regular backups

### Never Commit to Git

```
.env                 # Contains passwords
vendor/              # Large dependency files
mysql_data/          # Database files
composer.lock        # (usually committed, but .env is not)
```

Included in `.gitignore` ✓

---

## ❓ FAQ

**Q: Can I access the database from Windows?**
A: Yes, via localhost:3306 with tools like MySQL Workbench or phpMyAdmin

**Q: How do I backup the database?**
A: `docker-compose exec spd-mysql mysqldump -u root -p spd_sports_therapy > backup.sql`

**Q: What if I forgot the Gmail app password?**
A: Generate a new one at https://myaccount.google.com/apppasswords and update `.env`

**Q: How do I scale to production?**
A: Switch from local Docker to cloud database (AWS RDS, etc.), update `.env`, deploy to server

**Q: Can I use a different email service?**
A: Yes, update config/email.php to use SendGrid, Mailgun, etc. instead of Gmail SMTP

---

## 📞 Support

For issues or questions:
1. Check this document's Troubleshooting section
2. Review Docker logs: `dev-status.bat`
3. Check API response in browser console (F12)
4. Consult PHPMailer docs if email issues persist

---

**Last Updated**: July 2026  
**Version**: 1.0  
**Maintainers**: SPD Sports Therapy Team
