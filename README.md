# L'Auto-Entrepreneur

Application de gestion pour auto-entrepreneurs au Maroc.

## Fonctionnalités

- **Documents** : Devis, factures, bons de livraison avec numérotation automatique
- **PDF professionnel** : Logo, signature, bannière, multi-devises
- **Déclarations fiscales** : Simulateur IR, CNSS, retenue à la source
- **Gestion** : Clients, produits, dashboard, annonces
- **Profil** : Informations légales, banque, RIB

## Prérequis

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache/Nginx avec mod_rewrite
- Extension PHP : pdo_mysql, json, mbstring

## Installation

1. Copier les fichiers dans le répertoire web
2. Créer la base de données :
   ```sql
   source database/autoentrepreneur_final.sql
   ```
3. Configurer `config/database.php` avec vos identifiants
4. Configurer `config/app.php` avec l'URL de votre site
5. Configurer les permissions :
   ```bash
   chmod -R 755 uploads/
   chmod -R 755 assets/
   ```

## Sécurité

- Headers de sécurité activés (X-Content-Type-Options, X-Frame-Options, etc.)
- Protection CSRF sur les formulaires
- Rate limiting sur la connexion (5 tentatives / 5 min)
- Session ID régénéré périodiquement
- Files sensibles protégés par .htaccess

## Configuration Production

1. Activer HTTPS dans `.htaccess` (décommenter la section Force HTTPS)
2. Mettre `session.cookie_secure` à 1 dans `config/security.php`
3. Supprimer le fichier `database/autoentrepreneur_final.sql` après import
4. Vérifier les permissions des dossiers `uploads/`

## Compte par défaut

- **Email** : admin@admin.com
- **Mot de passe** : admin123
