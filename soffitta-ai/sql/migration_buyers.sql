-- Soffitta.ai — Migrazione: aggiunta tabelle acquirenti
-- Eseguire su installazioni esistenti (schema.sql già importato)
-- mysql -u root -p soffitta_ai < migration_buyers.sql

USE soffitta_ai;

CREATE TABLE IF NOT EXISTS companies (
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
    categories_interest JSON,
    budget_min INT DEFAULT 0,
    budget_max INT DEFAULT 0,
    era_interest VARCHAR(500),
    verified TINYINT(1) DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    logo_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS buyer_matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scan_id INT NOT NULL,
    company_id INT NOT NULL,
    match_score INT DEFAULT 0,
    status ENUM('pending','interested','not_interested','offer_made','sold') DEFAULT 'pending',
    offer_amount DECIMAL(10,2),
    message TEXT,
    notified_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (scan_id) REFERENCES scans(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (scan_id, company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX IF NOT EXISTS idx_companies_active ON companies(active, verified);
CREATE INDEX IF NOT EXISTS idx_buyer_matches_scan ON buyer_matches(scan_id);
CREATE INDEX IF NOT EXISTS idx_buyer_matches_company ON buyer_matches(company_id, status);
CREATE INDEX IF NOT EXISTS idx_buyer_matches_score ON buyer_matches(match_score DESC);
