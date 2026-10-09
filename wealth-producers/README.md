# Wealth Producers – Information Form (PHP + MySQL)

4-step form: Personal Information → Emergency Contact → Specific Skills → Document Upload.

## Setup

1. **Add your images** to the `assets/` folder:
   - `assets/logo.png` – the WP logo
   - `assets/bg.jpg` – the green gold-line background
2. **Create the database:** import `schema.sql` (phpMyAdmin → Import, or `mysql -u root -p < schema.sql`).
3. **Edit `config.php`** with your MySQL host, name, user and password.
4. **Run it:** put this folder in XAMPP `htdocs` (or any PHP 8.1+ host) and open `http://localhost/<folder>/`.
   Or from this folder: `php -S localhost:8000`.
5. Make sure `uploads/` is writable by the web server.

## Files

| File | Purpose |
|---|---|
| `index.php` | The 4-step form |
| `submit.php` | Validates, saves uploads, inserts into MySQL |
| `success.php` | Thank-you page |
| `config.php` | DB settings + dropdown/skill lists |
| `schema.sql` | Database + `applicants` table |
| `assets/style.css`, `assets/app.js` | Styling and step logic |
| `uploads/` | Stored documents (web access blocked by `.htaccess`) |

## Notes

- Document uploads are optional (marked "to follow"). To make them required, add `required` to the file inputs in `index.php`.
- Uploads: JPEG/PNG for bank info and assessments, PDF for the contract, max 5 MB each, renamed to random filenames, MIME-checked.
- Uses prepared statements, CSRF token and a honeypot field.
- Colors: black `#131313`, gold `#d8ab33`, green `#0b8346`.
- There is no admin page yet; view submissions in phpMyAdmin (`applicants` table). Ask if you want an admin login + dashboard.
- Nginx users: `.htaccess` doesn't apply, so deny access to `/uploads/` in your server config.
