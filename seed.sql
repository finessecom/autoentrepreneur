USE autoentrepreneur;

-- Admin user (password: admin123)
INSERT INTO users (role, nom_complet, nom_affichage, titre_pro, ville, email, password, raison_sociale, ice, identifiant_fiscal)
VALUES ('admin', 'Admin System', 'Admin', 'Administrateur', 'Casablanca', 'admin@admin.com',
        '$2y$10$oFTjs5/fa6kdAJ7AJ0sPcuODL6gqrO5hDnk.LTW3dwQF8ktuk1atu',
        'Admin System SARL', '001234567000001', '12345678');
