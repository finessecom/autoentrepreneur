-- =====================================================
-- Base de données : Auto-Entrepreneur
-- Version finale : 1.0.0
-- Date : Juillet 2026
-- =====================================================

CREATE DATABASE IF NOT EXISTS autoentrepreneur
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE autoentrepreneur;

-- =====================================================
-- 1. Table des utilisateurs
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('admin', 'user') DEFAULT 'user',
    nom_complet VARCHAR(100) NOT NULL,
    nom_affichage VARCHAR(100) NOT NULL,
    titre_pro VARCHAR(100),
    titre_pro_ar VARCHAR(100),
    ville VARCHAR(100),
    langue_principale VARCHAR(50) DEFAULT 'Français',
    whatsapp VARCHAR(20),
    telephone VARCHAR(20),
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    site_web VARCHAR(255),
    reseaux_sociaux JSON,
    mots_cles VARCHAR(255),
    raison_sociale VARCHAR(150),
    email_pro VARCHAR(150),
    ice VARCHAR(20),
    identifiant_fiscal VARCHAR(20),
    nom_banque VARCHAR(150),
    rib VARCHAR(50),
    taxe_professionnelle VARCHAR(20),
    prefixe_devis VARCHAR(10) DEFAULT 'DEV',
    prefixe_facture VARCHAR(10) DEFAULT 'FAC',
    prefixe_livraison VARCHAR(10) DEFAULT 'BL',
    signature_url VARCHAR(255),
    signature_taille INT DEFAULT 100,
    logo_pdf_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- 2. Table des clients
-- =====================================================
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom_client VARCHAR(150) NOT NULL,
    ice VARCHAR(20),
    email VARCHAR(150),
    telephone VARCHAR(20),
    adresse TEXT,
    devise VARCHAR(10) DEFAULT 'MAD',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 3. Table des produits et services
-- =====================================================
CREATE TABLE produits_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type_activite ENUM('commerce', 'service') NOT NULL,
    designation VARCHAR(255) NOT NULL,
    detail TEXT,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    devise VARCHAR(10) DEFAULT 'MAD',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 4. Table des documents (Devis, Factures, Bons de livraison)
-- =====================================================
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(50),
    user_id INT NOT NULL,
    client_id INT NOT NULL,
    type_document ENUM('devis', 'facture', 'bon_livraison') NOT NULL,
    date_document DATE NOT NULL,
    total_ht DECIMAL(10, 2) DEFAULT 0.00,
    total_ttc DECIMAL(10, 2) DEFAULT 0.00,
    statut ENUM('brouillon', 'envoye', 'paye', 'annule') DEFAULT 'brouillon',
    devise VARCHAR(10) DEFAULT 'MAD',
    decl_trimestre TINYINT NULL,
    decl_annee YEAR NULL,
    decl_total DECIMAL(10, 2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- 5. Table des lignes de documents
-- =====================================================
CREATE TABLE document_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    produit_service_id INT,
    designation VARCHAR(255) NOT NULL,
    detail TEXT,
    quantite INT NOT NULL DEFAULT 1,
    prix_unitaire DECIMAL(10, 2) NOT NULL,
    total_ligne DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (produit_service_id) REFERENCES produits_services(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =====================================================
-- 6. Table des déclarations fiscales
-- =====================================================
CREATE TABLE declarations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    annee YEAR NOT NULL,
    trimestre TINYINT NOT NULL CHECK (trimestre BETWEEN 1 AND 4),
    ca_commerce DECIMAL(10, 2) DEFAULT 0.00,
    ca_service DECIMAL(10, 2) DEFAULT 0.00,
    ir_calcule DECIMAL(10, 2) DEFAULT 0.00,
    cnss_calcule DECIMAL(10, 2) DEFAULT 0.00,
    cnss_tranche VARCHAR(10) DEFAULT 'T0',
    retenue_source DECIMAL(10, 2) DEFAULT 0.00,
    total_a_payer DECIMAL(10, 2) DEFAULT 0.00,
    est_declare BOOLEAN DEFAULT FALSE,
    est_paye BOOLEAN DEFAULT FALSE,
    mode_paiement VARCHAR(50),
    date_declaration DATE,
    ref_declaration VARCHAR(100),
    ref_paiement VARCHAR(100),
    date_paiement DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_declaration (user_id, annee, trimestre)
) ENGINE=InnoDB;

-- =====================================================
-- 7. Table des annonces et posts
-- =====================================================
CREATE TABLE annonces (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('post', 'annonce') NOT NULL DEFAULT 'annonce',
    titre VARCHAR(255) NOT NULL,
    texte TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- Index pour optimiser les performances
-- =====================================================
CREATE INDEX idx_user_email ON users(email);
CREATE INDEX idx_doc_user_type ON documents(user_id, type_document);
CREATE INDEX idx_decl_user_annee ON declarations(user_id, annee);

-- =====================================================
-- Données initiales : Admin par défaut
-- Mot de passe : admin123
-- =====================================================
INSERT INTO users (role, nom_complet, nom_affichage, titre_pro, ville, email, password, raison_sociale, ice, identifiant_fiscal)
VALUES ('admin', 'Admin System', 'Admin', 'Administrateur', 'Casablanca', 'admin@admin.com',
        '$2y$10$oFTjs5/fa6kdAJ7AJ0sPcuODL6gqrO5hDnk.LTW3dwQF8ktuk1atu',
        'Admin System SARL', '001234567000001', '12345678');
