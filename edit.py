from pathlib import Path
from PIL import Image
import re, shutil, zipfile
root=Path('/tmp/spdedit/SPD-Sports-therapy')
public=root/'public'
images=public/'images'
images.mkdir(exist_ok=True)
blue=Image.open('/mnt/data/01.png').convert('RGBA')
white=Image.open('/mnt/data/02.png').convert('RGBA')
# Exact logo asset as transparent PNG
blue.save(images/'logo.png', optimize=True)
# Keep a JPEG only if legacy external references exist; replace it with blue logo on white? Better remove legacy.
legacy=images/'logo.jpeg'
if legacy.exists(): legacy.unlink()
# Favicon assets: transparent white mark, standard sizes.
for size, name in [(16,'favicon-16x16.png'),(32,'favicon-32x32.png'),(48,'favicon-48x48.png'),(180,'apple-touch-icon.png'),(192,'icon-192.png'),(512,'icon-512.png')]:
    white.resize((size,size), Image.Resampling.LANCZOS).save(public/name, optimize=True)
# ICO with multiple standard sizes
white.save(root/'favicon.ico', format='ICO', sizes=[(16,16),(32,32),(48,48),(64,64)])
white.save(public/'favicon.ico', format='ICO', sizes=[(16,16),(32,32),(48,48),(64,64)])
# Web manifest for pinned/taskbar/PWA contexts
(root/'site.webmanifest').write_text('''{\n  "name": "SPD Sports Therapy",\n  "short_name": "SPD Sports Therapy",\n  "icons": [\n    {"src":"public/icon-192.png","sizes":"192x192","type":"image/png"},\n    {"src":"public/icon-512.png","sizes":"512x512","type":"image/png"}\n  ],\n  "theme_color": "#ffffff",\n  "background_color": "#ffffff",\n  "display": "standalone"\n}\n''', encoding='utf-8')
# Update all pages: favicon, manifest, logo.
for html in root.glob('*.html'):
    s=html.read_text(encoding='utf-8')
    s=re.sub(r'<link rel="icon"[^>]*>', '<link rel="icon" href="public/favicon.ico" sizes="any" type="image/x-icon">\n<link rel="icon" href="public/favicon-48x48.png" sizes="48x48" type="image/png">', s, count=1)
    s=re.sub(r'<link rel="shortcut icon"[^>]*>\n?', '<link rel="apple-touch-icon" href="public/apple-touch-icon.png">\n<link rel="manifest" href="site.webmanifest">\n<meta name="theme-color" content="#ffffff">\n', s, count=1)
    s=s.replace('public/images/logo.jpeg','public/images/logo.png')
    html.write_text(s,encoding='utf-8')
# Also ensure robots/sitemap stay untouched.
# Repack
out=Path('/mnt/data/SPD-Sports-Therapy-logo-favicon-updated.zip')
if out.exists(): out.unlink()
with zipfile.ZipFile(out,'w',zipfile.ZIP_DEFLATED) as z:
    for p in root.parent.rglob('*'):
        if p.is_file():
            z.write(p, p.relative_to(root.parent))
print(out)
print('files:', [p.name for p in public.glob('favicon*')])
