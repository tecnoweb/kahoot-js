-- Soffitta.ai — Schema completo database
-- Importare su phpMyAdmin o via: mysql -u root -p soffitta_ai < schema.sql

CREATE DATABASE IF NOT EXISTS soffitta_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE soffitta_ai;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    plan ENUM('free','pro') DEFAULT 'free',
    scans_used INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE scans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    image_path VARCHAR(500) NOT NULL,
    object_name VARCHAR(255),
    description TEXT,
    estimated_min DECIMAL(10,2),
    estimated_max DECIMAL(10,2),
    condition_notes TEXT,
    sell_suggestions TEXT,
    era VARCHAR(100),
    category VARCHAR(100),
    confidence_score INT,
    curiosity TEXT,
    is_premium TINYINT(1) DEFAULT 0,
    paid TINYINT(1) DEFAULT 0,
    public_slug VARCHAR(300),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scan_id INT NOT NULL,
    user_id INT,
    amount DECIMAL(10,2) NOT NULL,
    stripe_session_id VARCHAR(255),
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scan_id) REFERENCES scans(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Aziende acquirenti: antiquari, case d'aste, gioiellerie, collezionisti
CREATE TABLE companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_name VARCHAR(100),
    phone VARCHAR(50),
    city VARCHAR(100),
    province VARCHAR(10),
    website VARCHAR(255),
    vat_number VARCHAR(30),
    company_type ENUM('antiquario','casa_aste','gioielleria','galleria','collezionista','altro') DEFAULT 'antiquario',
    description TEXT,
    -- Preferenze acquisto: JSON array di categorie, es. ["quadri","ceramiche"]
    categories_interest JSON,
    -- Range valori di interesse in euro (0 = nessun limite)
    budget_min INT DEFAULT 0,
    budget_max INT DEFAULT 0,
    -- Epoche di interesse (testo libero, es. "XIX secolo, Art Déco")
    era_interest VARCHAR(500),
    verified TINYINT(1) DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    logo_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Abbinamenti tra scansioni e potenziali acquirenti
CREATE TABLE buyer_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scan_id INT NOT NULL,
    company_id INT NOT NULL,
    match_score INT DEFAULT 0,        -- 0-100, calcolato dall'algoritmo
    status ENUM('pending','interested','not_interested','offer_made','sold') DEFAULT 'pending',
    offer_amount DECIMAL(10,2),       -- offerta in euro dell'azienda
    message TEXT,                     -- messaggio dell'azienda al venditore
    notified_at TIMESTAMP NULL,       -- quando l'azienda è stata notificata
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scan_id) REFERENCES scans(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (scan_id, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Indici per performance SEO e query frequenti
CREATE INDEX idx_scans_category ON scans(category);
CREATE INDEX idx_scans_paid ON scans(paid);
CREATE INDEX idx_scans_user ON scans(user_id);
CREATE INDEX idx_scans_slug ON scans(public_slug(100));
CREATE INDEX idx_companies_active ON companies(active, verified);
CREATE INDEX idx_buyer_matches_scan ON buyer_matches(scan_id);
CREATE INDEX idx_buyer_matches_company ON buyer_matches(company_id, status);
CREATE INDEX idx_buyer_matches_score ON buyer_matches(match_score DESC);
