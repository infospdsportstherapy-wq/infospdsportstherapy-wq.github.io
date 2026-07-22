# SPD Sports Therapy - Professional Folder Organization

## 📦 Project Structure

```
SPD-Sports-therapy/
├── 📄 HTML Pages (Static, serve directly)
│   ├── index.html          Home page
│   ├── services.html       Services + reviews carousel
│   ├── about.html          About company
│   └── review.html         Review submission form
│
├── 📁 public/              Static web assets
│   ├── css/
│   │   ├── main.css        Main stylesheet
│   │   └── ...             Other stylesheets
│   ├── js/
│   │   ├── main.js         Main JavaScript
│   │   ├── reviews.js      Review carousel logic
│   │   └── ...             Other scripts
│   ├── images/             All website images
│   │   ├── Chatblue.png    Logo
│   │   ├── hero.jpg        Hero image
│   │   ├── logo.jpeg       Favicon
│   │   └── ...             Other images
│   ├── CNAME               Domain configuration
│   ├── robots.txt          SEO robots directive
│   ├── sitemap.xml         XML sitemap
│   └── fav.ico             Favicon
│
├── 📁 api/                 Backend API endpoints (PHP)
│   ├── get_reviews.php     Fetch reviews
│   ├── save_reviews.php    Save reviews
│   ├── submit_review.php   Form submission
│   ├── generate_embedded_reviews.php  Review generator
│   └── ...
│
├── 📁 admin/               Admin panel (optional)
│   ├── index.html          Admin dashboard
│   └── ...
│
├── 📁 data/                Data storage
│   └── reviews.json        Reviews database (JSON)
│
├── 📁 config/              Configuration
│   └── (Reserved for config files)
│
├── 📁 docs/                Documentation
│   └── (Project documentation)
│
├── 📄 Configuration Files
│   ├── .env                Environment variables
│   ├── .gitignore          Git ignore rules
│   └── start-dev.bat       Development startup script
│
└── 📁 .git/                Git repository
    └── (Version control)
```

## ✨ What Changed

### Organized Into Folders:
- **Before:** All CSS, JS, images scattered in root
- **After:** All grouped in `public/` subfolder

### File Paths Updated:
All HTML files automatically updated to use new paths:
- `css/main.css` → `public/css/main.css`
- `js/main.js` → `public/js/main.js`
- `images/hero.jpg` → `public/images/hero.jpg`

### Benefits:
✅ Professional structure  
✅ Clear separation of concerns  
✅ Static assets isolated in `public/`  
✅ Easy to deploy (upload `public/` to web root)  
✅ Better SEO (robots.txt, sitemap.xml organized)  
✅ Scalable (easy to add new pages)  

## 🚀 How It Works

### Pages (HTML):
- Keep HTML files in root for easy navigation
- Each HTML file references assets in `public/`

### Static Assets (`public/`):
- **CSS:** All stylesheets in `public/css/`
- **JS:** All JavaScript in `public/js/`
- **Images:** All images in `public/images/`
- **Web Config:** robots.txt, sitemap.xml, CNAME, favicon

### Backend (`api/`):
- PHP files handle form submissions
- Saves reviews to `data/reviews.json`
- Returns JSON data for review carousel

### Data (`data/`):
- `reviews.json` - Centralized review data
- Single source of truth for all reviews

## 🔗 File References in HTML

All paths in HTML files now use `public/` prefix:

```html
<!-- CSS -->
<link rel="stylesheet" href="public/css/main.css">

<!-- Images -->
<img src="public/images/Chatblue.png">

<!-- JavaScript -->
<script src="public/js/main.js"></script>

<!-- Favicon -->
<link rel="icon" href="public/images/logo.jpeg">
```

## 📝 Deployment

When deploying to production:

### Option 1: Upload Entire Folder
```
Upload entire project to server
```

### Option 2: Upload Public Folder Only
```
Upload public/ contents to web root
Place API files where server expects them
```

### Option 3: Use with Web Server
```
Configure web server to:
- Serve HTML files from root
- Serve assets from public/ folder
- Route API requests to api/ folder
```

## 🛠 Maintenance Tips

### Add New CSS:
1. Create file in `public/css/`
2. Link in HTML: `<link href="public/css/yourfile.css">`

### Add New Image:
1. Upload to `public/images/`
2. Reference: `<img src="public/images/yourimage.jpg">`

### Add New JavaScript:
1. Create file in `public/js/`
2. Include in HTML: `<script src="public/js/yourfile.js"></script>`

### Add New Page:
1. Create `yourpage.html` in root
2. Copy navbar/footer from existing page
3. Reference assets using `public/` paths

## 📊 Folder Statistics

```
Total Files: 37
HTML Pages: 4
CSS Files: 1
JavaScript Files: 2+
Images: 5+
API Endpoints: 4+
Configuration Files: 3
```

## ✅ Verification

All files have been:
- ✓ Organized into professional folders
- ✓ File paths updated in HTML
- ✓ Tested for functionality
- ✓ Ready for deployment

---

**Last Updated:** July 22, 2026  
**Version:** 1.0 (Professional Organization)
