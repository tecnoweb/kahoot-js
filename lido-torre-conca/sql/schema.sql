-- ============================================================
-- Lido Torre Conca - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS lido_torre_conca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lido_torre_conca;

-- Postazioni (60 total: 6 rows A-F × 10 stations)
CREATE TABLE IF NOT EXISTS postazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fila CHAR(1) NOT NULL,           -- A=mare ... F=retro
    numero INT NOT NULL,             -- 1-10
    numero_globale INT NOT NULL,     -- 1-60
    stato ENUM('attiva','manutenzione') NOT NULL DEFAULT 'attiva',
    UNIQUE KEY uq_fila_numero (fila, numero)
) ENGINE=InnoDB;

-- Prezzi configurabili
CREATE TABLE IF NOT EXISTS prezzi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(60) NOT NULL UNIQUE,
    nome VARCHAR(120) NOT NULL,
    prezzo DECIMAL(10,2) NOT NULL,
    descrizione VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB;

-- Prenotazioni
CREATE TABLE IF NOT EXISTS prenotazioni (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codice VARCHAR(12) NOT NULL UNIQUE,
    postazione_id INT NOT NULL,
    tipo_prenotazione ENUM('giornata_intera','mezza_giornata','settimanale','mensile') NOT NULL,
    data_inizio DATE NOT NULL,
    data_fine DATE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    cognome VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(25) NOT NULL,
    adulti TINYINT UNSIGNED NOT NULL DEFAULT 2,
    bambini TINYINT UNSIGNED NOT NULL DEFAULT 0,
    extra_lettino TINYINT(1) NOT NULL DEFAULT 0,
    navetta TINYINT(1) NOT NULL DEFAULT 0,
    note TEXT DEFAULT NULL,
    prezzo_totale DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stato ENUM('in_attesa','confermata','annullata') NOT NULL DEFAULT 'in_attesa',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (postazione_id) REFERENCES postazioni(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Noleggi
CREATE TABLE IF NOT EXISTS noleggi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(40) NOT NULL UNIQUE,
    nome VARCHAR(80) NOT NULL,
    prezzo_ora DECIMAL(10,2) NOT NULL,
    quantita_disponibile INT NOT NULL DEFAULT 1,
    icona VARCHAR(10) DEFAULT NULL
) ENGINE=InnoDB;

-- ── Admin users (ruoli granulari) ────────────────────────────
-- livello 1 = Sviluppatore (accesso totale + configurazione)
-- livello 2 = Gestore (prenotazioni, POS, report, menu)
-- livello 3 = Cassiere (solo POS e cucina)
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nome VARCHAR(100) DEFAULT NULL,
    livello TINYINT NOT NULL DEFAULT 2,   -- 1|2|3
    attivo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Menu Food & Drinks ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS menu_categorie (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    descrizione VARCHAR(255) DEFAULT NULL,
    ordine INT NOT NULL DEFAULT 0,
    attiva TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descrizione VARCHAR(255) DEFAULT NULL,
    prezzo DECIMAL(8,2) NOT NULL,
    disponibile TINYINT(1) NOT NULL DEFAULT 1,
    allergeni VARCHAR(255) DEFAULT NULL,
    ordine INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES menu_categorie(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Ordini food ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ordini (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codice VARCHAR(10) NOT NULL UNIQUE,
    postazione_id INT DEFAULT NULL,         -- NULL = ordine da ristorante/banco
    tipo_origine ENUM('spiaggia','ristorante','banco') NOT NULL DEFAULT 'spiaggia',
    nome_cliente VARCHAR(100) NOT NULL,
    telefono VARCHAR(25) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    totale DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    stato ENUM('nuovo','in_corso','pronto','consegnato','annullato') NOT NULL DEFAULT 'nuovo',
    notifica_inviata TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (postazione_id) REFERENCES postazioni(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ordini_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ordine_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    nome_item VARCHAR(120) NOT NULL,    -- snapshot nome al momento ordine
    prezzo_unitario DECIMAL(8,2) NOT NULL,
    quantita TINYINT UNSIGNED NOT NULL DEFAULT 1,
    note VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (ordine_id)    REFERENCES ordini(id)     ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- 60 postazioni: righe A-F, 10 per fila
INSERT IGNORE INTO postazioni (fila, numero, numero_globale) VALUES
('A',1,1),('A',2,2),('A',3,3),('A',4,4),('A',5,5),
('A',6,6),('A',7,7),('A',8,8),('A',9,9),('A',10,10),
('B',1,11),('B',2,12),('B',3,13),('B',4,14),('B',5,15),
('B',6,16),('B',7,17),('B',8,18),('B',9,19),('B',10,20),
('C',1,21),('C',2,22),('C',3,23),('C',4,24),('C',5,25),
('C',6,26),('C',7,27),('C',8,28),('C',9,29),('C',10,30),
('D',1,31),('D',2,32),('D',3,33),('D',4,34),('D',5,35),
('D',6,36),('D',7,37),('D',8,38),('D',9,39),('D',10,40),
('E',1,41),('E',2,42),('E',3,43),('E',4,44),('E',5,45),
('E',6,46),('E',7,47),('E',8,48),('E',9,49),('E',10,50),
('F',1,51),('F',2,52),('F',3,53),('F',4,54),('F',5,55),
('F',6,56),('F',7,57),('F',8,58),('F',9,59),('F',10,60);

-- Prezzi di listino
INSERT IGNORE INTO prezzi (tipo, nome, prezzo, descrizione) VALUES
('giornata_intera', 'Giornata Intera', 25.00, '08:30 – 19:00 · 2 lettini + 1 ombrellone'),
('mezza_giornata',  'Mezza Giornata',  15.00, '14:00 – 19:00 · 2 lettini + 1 ombrellone'),
('settimanale',     'Abbonamento Settimanale', 140.00, '7 giorni consecutivi · giornata intera'),
('mensile',         'Abbonamento Mensile',     450.00, '30 giorni consecutivi · giornata intera'),
('extra_lettino',   'Lettino Extra',   5.00, 'Lettino aggiuntivo per postazione'),
('navetta',         'Servizio Navetta', 3.00, 'A/R per ogni giorno prenotato');

-- Noleggi
INSERT IGNORE INTO noleggi (tipo, nome, prezzo_ora, quantita_disponibile) VALUES
('pedalo',     'Pedalò',        8.00, 5),
('canoa',      'Canoa',         6.00, 4),
('moto_acqua', 'Moto d\'Acqua', 30.00, 2),
('sup',        'SUP',           8.00, 3);

-- Admin: livello 1 (sviluppatore) — password: lido2025
INSERT IGNORE INTO admin_users (username, password_hash, nome, livello)
VALUES ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sviluppatore', 1);

-- Admin: livello 2 (gestore) — password: gestore2025
INSERT IGNORE INTO admin_users (username, password_hash, nome, livello)
VALUES ('gestore', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gestore', 2);

-- Admin: livello 3 (cassiere) — password: cassa2025
INSERT IGNORE INTO admin_users (username, password_hash, nome, livello)
VALUES ('cassiere', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Cassiere', 3);

-- Menu categorie
INSERT IGNORE INTO menu_categorie (id, nome, descrizione, ordine) VALUES
(1, 'Panini & Piadine',   'Freschi e saporiti',              1),
(2, 'Insalate',           'Leggere e gustose',                2),
(3, 'Primi Piatti',       'Pasta e riso del giorno',          3),
(4, 'Secondi & Grigliata','Pesce fresco e carne alla griglia',4),
(5, 'Dolci',              'Granite, gelati e dolci freschi',  5),
(6, 'Aperitivi',          'Spritz, Negroni e sfiziosità',     6),
(7, 'Cocktail',           'Long drink e cocktail estivi',     7),
(8, 'Bibite & Acqua',     'Bibite, succhi e acqua',           8);

-- Menu items (placeholder sostituibili)
INSERT IGNORE INTO menu_items (categoria_id, nome, descrizione, prezzo, ordine) VALUES
-- Panini & Piadine
(1,'Panino Caprese',    'Mozzarella di bufala, pomodoro, basilico',            6.50, 1),
(1,'Panino Tonno',      'Tonno, pomodoro, cipolla di Tropea',                  6.00, 2),
(1,'Piadina Prosciutto','Crudo, mozzarella, rucola',                           7.00, 3),
(1,'Piadina Vegana',    'Hummus, verdure grigliate, avocado',                  7.50, 4),
-- Insalate
(2,'Greca',             'Feta, cetrioli, pomodori, olive',                     9.00, 1),
(2,'Nizzarda',          'Tonno, acciughe, fagiolini, uova',                   10.00, 2),
(2,'Caesar',            'Pollo, parmigiano, crostini, salsa caesar',           9.50, 3),
-- Primi
(3,'Pasta al Pomodoro', 'Pomodorini freschi e basilico',                        9.00, 1),
(3,'Spaghetti Vongole', 'Vongole veraci, aglio, prezzemolo',                  13.00, 2),
(3,'Risotto Gamberi',   'Gamberi freschi, limone e zafferano',                14.00, 3),
-- Secondi
(4,'Spigola al Sale',   'Spigola intera al sale, 400g',                       18.00, 1),
(4,'Salmone Grigliato', 'Con verdure di stagione',                            15.00, 2),
(4,'Tagliata Manzo',    'Rucola, grana, pomodorini',                          17.00, 3),
-- Dolci
(5,'Granita Limone',    'Granita siciliana artigianale',                        3.50, 1),
(5,'Granita Mandorla',  'Con brioche',                                         4.50, 2),
(5,'Gelato Artigianale','3 gusti a scelta',                                    4.00, 3),
(5,'Tiramisù',          'Ricetta tradizionale',                                 5.50, 4),
-- Aperitivi
(6,'Spritz Aperol',     'Aperol, Prosecco, soda',                              7.00, 1),
(6,'Spritz Campari',    'Campari, Prosecco, soda',                             7.00, 2),
(6,'Negroni',           'Gin, Campari, Vermouth rosso',                        8.00, 3),
-- Cocktail
(7,'Mojito',            'Rum bianco, lime, menta, zucchero di canna',          8.50, 1),
(7,'Piña Colada',       'Rum, cocco, ananas',                                  8.50, 2),
(7,'Hugo',              'Elderflower, Prosecco, menta',                        7.50, 3),
(7,'Sex on the Beach',  'Vodka, pesca, arancia, ribes',                        8.00, 4),
-- Bibite
(8,'Acqua Naturale',    '0.5L',                                                1.50, 1),
(8,'Acqua Frizzante',   '0.5L',                                                1.50, 2),
(8,'Coca-Cola',         '0.33L lattina',                                       3.00, 3),
(8,'Succo Frutta',      'Pesca, albicocca, arancia',                           3.00, 4),
(8,'Birra Moretti',     '0.33L',                                               4.00, 5),
(8,'Birra Artigianale', '0.33L – selezione del giorno',                        5.00, 6);
