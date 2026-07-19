USE autoentrepreneur;

-- Admin user (password: admin123)
INSERT INTO users (role, nom_complet, nom_affichage, titre_pro, ville, email, password, raison_sociale, ice, identifiant_fiscal)
VALUES ('admin', 'Admin System', 'Admin', 'Administrateur', 'Casablanca', 'admin@admin.com',
        '$2y$10$oFTjs5/fa6kdAJ7AJ0sPcuODL6gqrO5hDnk.LTW3dwQF8ktuk1atu',
        'Admin System SARL', '001234567000001', '12345678');

-- User 1 (password: password)
INSERT INTO users (role, nom_complet, nom_affichage, titre_pro, titre_pro_ar, ville, email, password, raison_sociale, ice, identifiant_fiscal, taxe_professionnelle, cnie)
VALUES ('user', 'Mohamed Alami', 'Mohamed', 'Développeur Web & Mobile', 'مطور الويب والموبايل', 'Rabat', 'mohamed@test.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Alami Tech SARL', '002345678000002', '87654321', 'TP12345', 'EA123456');

-- User 2 (password: password)
INSERT INTO users (role, nom_complet, nom_affichage, titre_pro, ville, email, password, raison_sociale, ice)
VALUES ('user', 'Fatima Zahra Benani', 'Fatima', 'Consultante Marketing Digital', 'Marrakech', 'fatima@test.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'Benani Marketing', '003456789000003');

-- Clients pour User 2
INSERT INTO clients (user_id, nom_client, ice, email, telephone, adresse, devise) VALUES
(2, 'Entreprise Atlas Construction', '001112223000011', 'contact@atlas-construction.ma', '0522334455', 'Bd Zerktouni, Marrakech', 'MAD'),
(2, 'Restaurant Le Jardin', '002223334000022', 'info@jardin.ma', '0524667788', 'Gueliz, Marrakech', 'MAD'),
(2, 'Tech Startup Inc', '003334445000033', 'hello@techstartup.ma', '0612345678', 'CFC, Casablanca', 'EUR');

-- Produits/Services pour User 2
INSERT INTO produits_services (user_id, type_activite, designation, prix_unitaire) VALUES
(2, 'service', 'Création site web vitrine', 5000.00),
(2, 'service', 'Référencement SEO (mois)', 2000.00),
(2, 'service', 'Gestion réseaux sociaux (mois)', 1500.00),
(2, 'commerce', 'Pack cartes de visite', 200.00),
(2, 'service', 'Formation digitale (jour)', 3000.00);

-- Documents pour User 2
INSERT INTO documents (user_id, client_id, type_document, date_document, total_ht, total_ttc, statut) VALUES
(2, 1, 'devis', '2026-01-15', 7000.00, 7000.00, 'envoye'),
(2, 1, 'facture', '2026-02-01', 5000.00, 5000.00, 'paye'),
(2, 2, 'devis', '2026-03-10', 3500.00, 3500.00, 'brouillon'),
(2, 3, 'facture', '2026-04-05', 2000.00, 2000.00, 'envoye'),
(2, 1, 'bon_livraison', '2026-02-15', 5000.00, 5000.00, 'paye');

-- Items pour les documents
INSERT INTO document_items (document_id, produit_service_id, designation, quantite, prix_unitaire, total_ligne) VALUES
(1, 1, 'Création site web vitrine', 1, 5000.00, 5000.00),
(1, 3, 'Gestion réseaux sociaux (mois)', 1, 1500.00, 1500.00),
(1, 4, 'Pack cartes de visite', 2, 250.00, 500.00),
(2, 1, 'Création site web vitrine', 1, 5000.00, 5000.00),
(3, 2, 'Référencement SEO (mois)', 1, 2000.00, 2000.00),
(3, 4, 'Pack cartes de visite', 5, 200.00, 1000.00),
(3, 3, 'Gestion réseaux sociaux (mois)', 1, 1500.00, 1500.00),
(4, 2, 'Référencement SEO (mois)', 1, 2000.00, 2000.00),
(5, 1, 'Création site web vitrine', 1, 5000.00, 5000.00);

-- Déclarations pour User 2
INSERT INTO declarations (user_id, annee, trimestre, ca_commerce, ca_service, ir_calcule, cnss_calcule, retenue_source, total_a_payer, est_declare, mode_paiement, date_declaration) VALUES
(2, 2026, 1, 200.00, 8500.00, 86.00, 173.52, 0.00, 259.52, 1, 'virement', '2026-04-15'),
(2, 2026, 2, 400.00, 5500.00, 57.00, 108.24, 0.00, 165.24, 0, NULL, NULL);
