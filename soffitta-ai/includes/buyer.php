<?php
require_once __DIR__ . '/db.php';

/**
 * Motore di buyer matching.
 * Chiamato dopo ogni nuova scansione — abbina l'oggetto alle aziende interessate.
 */

// Calcola il match score tra una scansione e un'azienda (0-100)
function calculateMatchScore(array $scan, array $company): int {
    $score = 0;

    // +40 se la categoria coincide con gli interessi dell'azienda
    $interests = json_decode($company['categories_interest'] ?? '[]', true) ?: [];
    if (!empty($scan['category']) && in_array($scan['category'], $interests, true)) {
        $score += 40;
    }

    // +30 se il valore stimato rientra nel budget dell'azienda
    $budMin = (int) $company['budget_min'];
    $budMax = (int) $company['budget_max'];
    $valMin = (int) $scan['estimated_min'];
    $valMax = (int) $scan['estimated_max'];

    if ($budMax === 0) {
        // Nessun limite superiore → basta che il valore minimo superi il budget minimo
        if ($valMin >= $budMin) {
            $score += 30;
        }
    } else {
        // Range si sovrappone
        if ($valMin <= $budMax && $valMax >= $budMin) {
            $score += 30;
        }
    }

    // +20 se l'era di interesse è menzionata nel campo era_interest
    if (!empty($scan['era']) && !empty($company['era_interest'])) {
        $eraLower     = mb_strtolower($scan['era']);
        $interestLower = mb_strtolower($company['era_interest']);
        // Controllo parziale: es. "XIX" in "XIX secolo, Art Déco"
        $eraWords = preg_split('/\s+/', $eraLower);
        foreach ($eraWords as $word) {
            if (strlen($word) >= 3 && str_contains($interestLower, $word)) {
                $score += 20;
                break;
            }
        }
    }

    // +10 bonus se la confidence AI è alta (>=70)
    if ((int) $scan['confidence_score'] >= 70) {
        $score += 10;
    }

    return min(100, $score);
}

// Crea i buyer_matches per una scansione appena inserita
function createBuyerMatches(int $scanId): int {
    $pdo = getDB();

    $stmt = $pdo->prepare('SELECT * FROM scans WHERE id = ?');
    $stmt->execute([$scanId]);
    $scan = $stmt->fetch();
    if (!$scan) {
        return 0;
    }

    // Solo aziende attive e verificate
    $companies = $pdo->query(
        'SELECT * FROM companies WHERE active = 1 AND verified = 1'
    )->fetchAll();

    $inserted = 0;
    $insertStmt = $pdo->prepare(
        'INSERT IGNORE INTO buyer_matches (scan_id, company_id, match_score)
         VALUES (?, ?, ?)'
    );

    foreach ($companies as $company) {
        $score = calculateMatchScore($scan, $company);
        if ($score >= 30) {
            $insertStmt->execute([$scanId, $company['id'], $score]);
            $inserted++;
        }
    }

    return $inserted;
}

// Recupera i buyer match per una scansione (per mostrarli al venditore)
function getBuyerMatches(int $scanId, int $limit = 5): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT bm.id, bm.match_score, bm.status, bm.offer_amount, bm.message,
                c.company_name, c.city, c.province, c.company_type, c.logo_path,
                c.description, c.website
         FROM buyer_matches bm
         JOIN companies c ON bm.company_id = c.id
         WHERE bm.scan_id = ?
         ORDER BY bm.match_score DESC, bm.status = \'interested\' DESC
         LIMIT ?'
    );
    $stmt->bindValue(1, $scanId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

// Recupera gli oggetti che corrispondono agli interessi di un'azienda
function getMatchedScansForCompany(int $companyId, int $limit = 20, int $offset = 0): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT bm.id AS match_id, bm.match_score, bm.status, bm.offer_amount,
                s.id AS scan_id, s.object_name, s.image_path, s.estimated_min,
                s.estimated_max, s.category, s.era, s.description, s.confidence_score,
                s.created_at, s.public_slug
         FROM buyer_matches bm
         JOIN scans s ON bm.scan_id = s.id
         WHERE bm.company_id = ?
         ORDER BY bm.match_score DESC, s.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->bindValue(1, $companyId, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function countMatchedScansForCompany(int $companyId): int {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer_matches WHERE company_id = ?');
    $stmt->execute([$companyId]);
    return (int) $stmt->fetchColumn();
}

// Recupera l'azienda corrente dalla sessione
function getCurrentCompany(): ?array {
    if (empty($_SESSION['company_id'])) {
        return null;
    }
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT id, email, company_name, contact_name, city, province, company_type,
                description, categories_interest, budget_min, budget_max, era_interest,
                verified, active, logo_path, website, phone
         FROM companies WHERE id = ?'
    );
    $stmt->execute([$_SESSION['company_id']]);
    return $stmt->fetch() ?: null;
}

function isCompanyLoggedIn(): bool {
    return !empty($_SESSION['company_id']);
}

function requireCompanyLogin(): void {
    if (!isCompanyLoggedIn()) {
        redirect('/login-company.php');
    }
}

// Login azienda
function loginCompany(string $email, string $password): array {
    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id, company_name, password, active, verified FROM companies WHERE email = ?');
    $stmt->execute([mb_strtolower(trim($email))]);
    $company = $stmt->fetch();

    if (!$company || !password_verify($password, $company['password'])) {
        return ['success' => false, 'message' => 'Email o password errati'];
    }
    if (!$company['active']) {
        return ['success' => false, 'message' => 'Account disabilitato. Contatta il supporto.'];
    }

    session_regenerate_id(true);
    $_SESSION['company_id']   = $company['id'];
    $_SESSION['company_name'] = $company['company_name'];
    $_SESSION['company_verified'] = (bool) $company['verified'];

    return ['success' => true];
}

// Registrazione azienda
function registerCompany(array $data): array {
    if (strlen($data['password']) < 8) {
        return ['success' => false, 'message' => 'La password deve avere almeno 8 caratteri'];
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Email non valida'];
    }
    if (empty(trim($data['company_name']))) {
        return ['success' => false, 'message' => 'Ragione sociale obbligatoria'];
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id FROM companies WHERE email = ?');
    $stmt->execute([mb_strtolower(trim($data['email']))]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email già registrata'];
    }

    $hash        = password_hash($data['password'], PASSWORD_BCRYPT);
    $categories  = json_encode(array_values(array_intersect(
        $data['categories'] ?? [],
        ['quadri', 'ceramiche', 'gioielli', 'mobili', 'argenteria', 'altro']
    )));

    $validTypes = ['antiquario','casa_aste','gioielleria','galleria','collezionista','altro'];
    $type = in_array($data['company_type'] ?? '', $validTypes, true) ? $data['company_type'] : 'altro';

    $stmt = $pdo->prepare("
        INSERT INTO companies
            (email, password, company_name, contact_name, phone, city, province,
             website, vat_number, company_type, description,
             categories_interest, budget_min, budget_max, era_interest)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        mb_strtolower(trim($data['email'])),
        $hash,
        sanitize($data['company_name']),
        sanitize($data['contact_name'] ?? ''),
        sanitize($data['phone'] ?? ''),
        sanitize($data['city'] ?? ''),
        sanitize($data['province'] ?? ''),
        sanitize($data['website'] ?? ''),
        sanitize($data['vat_number'] ?? ''),
        $type,
        sanitize($data['description'] ?? ''),
        $categories,
        max(0, (int) ($data['budget_min'] ?? 0)),
        max(0, (int) ($data['budget_max'] ?? 0)),
        sanitize($data['era_interest'] ?? ''),
    ]);

    $companyId = (int) $pdo->lastInsertId();
    session_regenerate_id(true);
    $_SESSION['company_id']   = $companyId;
    $_SESSION['company_name'] = sanitize($data['company_name']);
    $_SESSION['company_verified'] = false;

    return ['success' => true];
}

// Etichette leggibili per tipo azienda
function companyTypeLabel(string $type): string {
    return match ($type) {
        'antiquario'   => 'Antiquario',
        'casa_aste'    => 'Casa d\'aste',
        'gioielleria'  => 'Gioielleria',
        'galleria'     => 'Galleria d\'arte',
        'collezionista'=> 'Collezionista privato',
        default        => 'Altro',
    };
}
