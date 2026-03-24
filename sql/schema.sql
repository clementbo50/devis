-- Schema base de données pour l'application de devis

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des entreprises (société de l'utilisateur)
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    legal_status VARCHAR(100),
    representative_name VARCHAR(100),
    representative_firstname VARCHAR(100),
    address TEXT,
    siret VARCHAR(50),
    tva_number VARCHAR(50),
    tva_exempt BOOLEAN DEFAULT FALSE,
    email VARCHAR(255),
    phone VARCHAR(50),
    logo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des clients
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('particulier', 'entreprise', 'public') DEFAULT 'particulier',
    name VARCHAR(255) NOT NULL,
    address TEXT,
    siret VARCHAR(50),
    code_ape VARCHAR(50),
    service VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des devis
CREATE TABLE IF NOT EXISTS quotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_id INT NOT NULL,
    client_id INT NOT NULL,
    quote_number VARCHAR(50) NOT NULL,
    quote_date DATE NOT NULL,
    valid_until DATE NOT NULL,
    note TEXT,
    conditions TEXT,
    tva_rate DECIMAL(5,2) DEFAULT 20.00,
    tva_custom_rate DECIMAL(5,2) DEFAULT NULL,
    total_ht DECIMAL(12,2) DEFAULT 0.00,
    total_tva DECIMAL(12,2) DEFAULT 0.00,
    total_ttc DECIMAL(12,2) DEFAULT 0.00,
    status ENUM('draft', 'sent', 'accepted', 'rejected') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);

-- Table des lignes de devis
CREATE TABLE IF NOT EXISTS quote_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quote_id INT NOT NULL,
    description TEXT NOT NULL,
    item_date DATE,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
    unit VARCHAR(50),
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (quote_id) REFERENCES quotes(id) ON DELETE CASCADE
);

-- Index pour optimiser les requêtes
CREATE INDEX idx_quotes_user ON quotes(user_id);
CREATE INDEX idx_quotes_status ON quotes(status);
CREATE INDEX idx_clients_user ON clients(user_id);
CREATE INDEX idx_quote_items_quote ON quote_items(quote_id);

-- Insertion de l'utilisateur admin par défaut (password: dev123)
INSERT INTO users (email, password_hash, name, role) 
VALUES ('admin@devis.fr', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'admin');

-- Insertion d'une entreprise par défaut pour l'admin
INSERT INTO companies (user_id, name, legal_status, email) 
SELECT id, 'Mon Entreprise', 'SARL', email FROM users WHERE email = 'admin@devis.fr';
