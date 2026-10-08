# AGENTS.md - Application Information

## Application Overview
**Name:** Gestion Auto-Entrepreneur
**Type:** Monolithic server-rendered PHP application for Moroccan auto-entrepreneurs
**Language:** PHP 8.1+ (vanilla, no framework — 8.1 required by dompdf's `thecodingmachine/safe` dependency)
**Database:** MySQL/MariaDB (PDO, utf8mb4_unicode_ci)
**Frontend:** Bootstrap 5.3, Alpine.js 3.x, Font Awesome 6.5, Inter font, custom CSS
**PDF Generation:** dompdf/dompdf 3.1 (Composer)
**Server:** Apache with mod_rewrite or PHP built-in server

## Directory Structure
```
autoentrepreneur/
├── auth/              # Standalone login/register/logout pages
├── config/            # App, database, security configuration
├── database/          # SQL schema files
├── includes/          # Shared layout components (header, footer, sidebar, auth_check)
├── pages/             # All page templates (views)
│   ├── admin/         # Admin-only pages (dashboard, annonces) — Alpine.js
│   ├── annonces/      # Public announcement view
│   ├── charges/       # Expense tracking CRUD
│   ├── clients/       # Client management CRUD + view
│   ├── declarations/  # Fiscal declaration simulator
│   ├── documents/     # Invoice/quote/delivery note management
│   ├── produits/      # Product/service catalog
│   └── profil/        # User profile (private + public)
├── src/               # Business logic classes (models/services)
├── assets/            # CSS, JS, images
│   ├── css/custom.css # Design system + Alpine.js + modal styles
│   └── js/app.js      # Alert auto-dismiss only (sidebar managed in footer)
└── uploads/           # User-uploaded files (.htaccess blocks PHP execution)
    ├── signatures/
    ├── logos/
    ├── produits/
    └── echanges/
```

## Main Entry Points
- `index.php` - Main authenticated app (auth guard + page router via `?page=` parameter)
- `landing.php` - Public landing page with login/register forms (CSRF protected)
- `router.php` - PHP built-in server router
- `auth/login.php`, `auth/register.php`, `auth/logout.php` - Standalone auth pages

## Routing Table (via `?page=` parameter)
| Route | Page |
|-------|------|
| `dashboard` | pages/dashboard.php |
| `clients` | pages/clients/index.php |
| `clients/view` | pages/clients/view.php |
| `clients/create` | pages/clients/create.php |
| `clients/edit` | pages/clients/edit.php |
| `clients/delete` | pages/clients/delete.php |
| `produits` | pages/produits/index.php |
| `produits/create` | pages/produits/create.php |
| `produits/edit` | pages/produits/edit.php |
| `produits/delete` | pages/produits/delete.php |
| `charges` | pages/charges/index.php |
| `charges/create` | pages/charges/create.php |
| `charges/edit` | pages/charges/edit.php |
| `charges/delete` | pages/charges/delete.php |
| `documents` | pages/documents/index.php |
| `documents/create` | pages/documents/create.php |
| `documents/edit` | pages/documents/edit.php |
| `documents/view` | pages/documents/view.php |
| `documents/pdf` | pages/documents/pdf.php |
| `documents/delete` | pages/documents/delete.php |
| `declarations` | pages/declarations/index.php |
| `declarations/create` | pages/declarations/create.php |
| `declarations/edit` | pages/declarations/edit.php |
| `declarations/delete` | pages/declarations/delete.php |
| `profil` | pages/profil/index.php |
| `profil/edit` | pages/profil/edit.php |
| `admin` | pages/admin/dashboard.php |
| `admin/annonces` | pages/admin/annonces.php |
| `annonces/view` | pages/annonces/view.php |

## Database Schema (9 tables)

### users
User accounts and profiles. Columns: id, role (admin/user), nom_complet, nom_affichage, titre_pro (FR), titre_pro_ar (AR), ville, langue_principale, bio, bio_ar, whatsapp, telephone, email (unique), password (bcrypt), site_web, reseaux_sociaux (JSON), mots_cles, raison_sociale, email_pro, ice, identifiant_fiscal, nom_banque, rib, taxe_professionnelle, prefixe_devis (DEV), prefixe_facture (FAC), prefixe_livraison (BL), signature_url, signature_taille, logo_pdf_url, cnie, created_at, updated_at

### clients
Customer records. Columns: id, user_id (FK→users), nom_client, ice, email, telephone, adresse, devise (MAD/EUR/USD), date_echance, montant, created_at

### produits_services
Product/service catalog. Columns: id, user_id (FK→users), type_activite (commerce/service), designation, detail, prix_unitaire, devise (MAD/EUR/USD/GBP), image_url, created_at

### documents
Invoices, quotes, delivery notes. Columns: id, numero (auto: PREFIX-YEAR-SEQ), user_id (FK→users), client_id (FK→clients), type_document (devis/facture/bon_livraison), date_document, total_ht, total_ttc, statut (brouillon/envoye/paye/annule), devise (MAD), decl_trimestre, decl_annee, decl_total, date_paiement, montant_paiement, mode_paiement, created_at

### document_items
Line items in documents. Columns: id, document_id (FK→documents), produit_service_id (FK→produits_services), designation, detail, quantite (default 1), prix_unitaire, total_ligne

### declarations
Quarterly fiscal declarations. Columns: id, user_id (FK→users), annee, trimestre (1-4), ca_commerce, ca_service, ir_calcule, cnss_calcule, cnss_tranche (T0-T8), retenue_source, total_a_payer, est_declare, est_paye, mode_paiement, date_declaration, ref_declaration, ref_paiement, date_paiement, created_at. UNIQUE(user_id, annee, trimestre)

### annonces
Announcements and posts. Columns: id, user_id (FK→users), type (post/annonce), titre, texte, created_at

### charges
Business expenses. Columns: id, user_id (FK→users), date_charge, designation, montant_eur, montant_mad, created_at

### client_echanges (CRM log)
Client communication log. Columns: id, client_id (FK→clients), user_id (FK→users), date_echange, titre, message, document_url, created_at

## Authentication & Authorization
- **Session-based:** PHP sessions with httponly, strict mode, same-site=Lax
- **Session regeneration:** `session_regenerate_id(true)` after login and register (prevents session fixation)
- **Password hashing:** password_hash() with PASSWORD_DEFAULT (bcrypt)
- **Role system:** admin, user (ENUM)
- **Auth guard:** `includes/auth_check.php` → redirects to login if no session
- **Admin guard:** `Auth::requireAdmin()` → called BEFORE HTML output (prevents header-sent errors)
- **Data isolation:** All queries filter by user_id = Auth::userId()
- **Rate limiting:** 5 attempts per 5 minutes per IP (file-based)
- **CSRF protection:** Token generation/verification on ALL forms (login, register, CRUD, profile, declarations)
- **Security headers:** X-Content-Type-Options, X-Frame-Options, X-XSS-Protection, Referrer-Policy
- **Upload validation:** MIME type check + extension whitelist via `Helper::validateUpload()`
- **Upload directory protection:** `.htaccess` blocks PHP execution in `uploads/`
- **Default admin:** admin@admin.com / admin123

## Architecture Pattern: POST-before-header
All CRUD pages follow this critical pattern to ensure redirects work:
```php
<?php
$pageTitle = '...';

// 1. Load models + data
$model = new Model();
$entity = $model->getById($id, Auth::userId());

// 2. Handle POST (BEFORE header.php output)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    // ... process ...
    Helper::setSuccess('...');
    Helper::redirect(APP_URL . '/?page=...');
}

// 3. Include header (outputs HTML)
require_once __DIR__ . '/../../includes/header.php';
// ... HTML template ...
```

## Features

### Dashboard
- Top navbar: user dropdown only (the global `+ Nouveau` button was removed)
- Admin view (in order): **Stats Globaux** card with 5 mini-cards (Utilisateurs, Clients, Devis, Factures, `CA Global DH` — plain number, no currency suffix) → **Stats par user** full-width table (Nom user, Email, Date creation, Nb clients, Nb devis et factures, CA Global) built by `User::statsParUser()` → Posts & Annonces (unchanged)
- The old admin bottom blocks (Utilisateurs list + Derniers documents) and the `+ Nouvelle annonce` button were removed
- User view: clients count, documents by type, CA Previsionnel, Charges total, CA Realise, Resultat, recent documents

### Clients Module
- CRUD operations with search
- Stats cards: Nombre de clients, **CA Previsionnel** (sum of client montants)
- Client exchanges (CRM-like communication log with file upload)
- View client detail with linked documents

### Products/Services Module
- CRUD with type filter (commerce/service)
- Image upload with MIME validation
- CTA button: `+ Nouveau Produit ou Service`

### Documents Module (menu label: **Devis-Factures**)
- Sidebar/menu label is `Devis-Factures` (was `Documents`), same for page title
- Types: devis (quote), facture (invoice) — `bon_livraison` removed from UI (create/edit selects, list filter, dashboard card); legacy BL rows still display
- CTA button: `+ Nouveau Devis ou Facture`
- List table columns: Ref, Date, Client, Designation, Facture (total HT), Paiement MAD (montant payé, devise dans l'en-tête seulement), Statut, Actions (no Type column — type is readable from the Ref prefix)
- Dynamic line items with product selection
- Status workflow: brouillon → envoye → paye/annule
- Annulation avec motif (bouton + modale sur la vue, documents non payés uniquement) : colonnes `motif_annulation`/`date_annulation`, exclue des compteurs et du CA, PDF marqué (filigrane « ANNULÉE » + encadré motif) ; la numérotation est préservée (jamais de suppression). Un document déjà annulé permet de modifier le motif via `Document::updateMotifAnnulation()` ; l'option « Annulé » a été retirée du select d'édition (motif obligatoire via la modale)
- Payment tracking (especes, virement, cashplus, taptapsend)
- Fiscal declaration linking (year, quarter)
- PDF generation with legal mentions (Art 89 II 1 c CGI, Art 29 III a CGI)
- Amount in French words (corrected singular/plural)

### Charges Module
- Expense tracking with EUR and MAD amounts
- Search and statistics

### Fiscal Declarations Module
- Simulator with live calculation
- IR calculation: Commerce 0.5% (cap 500k MAD), Service 1% (cap 200k MAD) — JS matches PHP
- CNSS tiers: T0 (0 MAD) to T8 (3,600 MAD) per quarter — consistent between create/edit
- RAS: 30% withholding when client CA > 80,000 MAD
- Late penalties: 10% per month of delay

### Profile Module
- Full profile editor (personal, legal, social networks, uploads)
- Password change (error preserved, not overwritten by success)
- Logo and signature upload with MIME validation
- Public profile page (no auth required)

### Admin Module
- Dashboard: Stats Globaux + Stats par user (see Dashboard section)
- Announcements/posts management with Alpine.js delete confirmation modal

## Design System
- **Primary color:** #6236FF (Purple)
- **Success:** #22C55E, **Warning:** #F59E0B, **Danger:** #EF4444, **Info:** #3B82F6
- **Sidebar:** Dark theme (#1E293B), 260px width, responsive
- **Cards:** 12px border-radius, subtle shadow, fade-in animation
- **Font:** Inter, 14px base
- **Responsive:** Breakpoints at 991px and 576px
- **Alpine.js:** Used in admin dashboards for search, modals, dynamic lists
- **Modals:** Custom styled (12px radius, subtle shadows)
- **x-cloak:** Applied to prevent Alpine.js content flash

## Key Business Logic
- **Document numbering:** PREFIX-YEAR-SEQ (e.g., FAC-2026-0001), uses user's custom prefixes
- **Multi-currency:** MAD, EUR, USD, GBP
- **Amount to words:** French language conversion (corrected plural/singular, cent without trailing s)
- **CNSS tranches:** 9 tiers (T0-T8) with fixed quarterly amounts
- **Legal references:** Art 89 II 1 c CGI, Art 29 III a CGI

## Security Fixes Applied
1. **Session fixation:** `session_regenerate_id(true)` after login/register
2. **CSRF everywhere:** All forms include `csrf_field()` + `verify_csrf_token()`
3. **Upload validation:** `Helper::validateUpload()` with MIME type + extension check
4. **Upload directory protection:** `.htaccess` blocks PHP execution
5. **Admin auth guard:** Moved before HTML output (prevents header-sent bypass)
6. **IDOR fix:** `ClientEchange::delete()` now filters by `user_id`
7. **POST-before-header:** All CRUD pages redirect correctly after save

## Prerequisites
- PHP 8.1+ (composer deps require it; local dev runs on 8.2)
- MySQL 5.7+ or MariaDB
- Apache with mod_rewrite (or PHP built-in server)
- Composer
