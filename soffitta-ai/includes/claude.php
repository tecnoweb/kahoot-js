<?php
require_once __DIR__ . '/config.php';

function analyzeObjectWithClaude(string $imagePath): array {
    if (!file_exists($imagePath)) {
        return ['error' => 'File immagine non trovato'];
    }

    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType  = mime_content_type($imagePath);

    $prompt = <<<PROMPT
Sei un esperto antiquario e perito italiano con 30 anni di esperienza.
Analizza questa immagine e rispondi SOLO con un JSON valido, nessun testo fuori dal JSON.

{
  "object_name": "nome preciso dell'oggetto",
  "category": "categoria (es: quadri, ceramiche, gioielli, mobili, argenteria, altro)",
  "era": "epoca stimata (es: anni '50, XIX secolo, ecc.)",
  "description": "descrizione dettagliata in italiano, 3-4 frasi",
  "condition_notes": "note sulle condizioni visibili dall'immagine",
  "estimated_min": 150,
  "estimated_max": 400,
  "confidence_score": 75,
  "sell_suggestions": [
    "Aste locali come Cambi o Bertolami",
    "eBay categoria Antiquariato",
    "Mercatino dell'antiquariato di [città]"
  ],
  "curiosity": "un fatto interessante o storico sull'oggetto"
}

Regole:
- estimated_min e estimated_max sono numeri interi in euro, valore di mercato realistico
- confidence_score è da 0 a 100
- Se l'oggetto non è identificabile, metti confidence_score sotto 30 e spiega nel campo description
- Rispondi SEMPRE e SOLO con JSON valido
PROMPT;

    $payload = json_encode([
        'model'      => CLAUDE_MODEL,
        'max_tokens' => 1024,
        'messages'   => [[
            'role'    => 'user',
            'content' => [
                [
                    'type'   => 'image',
                    'source' => [
                        'type'       => 'base64',
                        'media_type' => $mimeType,
                        'data'       => $imageData,
                    ],
                ],
                ['type' => 'text', 'text' => $prompt],
            ],
        ]],
    ]);

    $ch = curl_init('https://api.anthropic.com/v1/messages');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . CLAUDE_API_KEY,
            'anthropic-version: 2023-06-01',
        ],
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log('Claude cURL error: ' . $curlErr);
        return ['error' => 'Errore di connessione all\'API AI'];
    }

    if ($httpCode !== 200) {
        error_log('Claude API HTTP ' . $httpCode . ': ' . $response);
        return ['error' => 'Errore API AI (codice ' . $httpCode . ')'];
    }

    $body = json_decode($response, true);
    $text = $body['content'][0]['text'] ?? '';

    // Rimuovi eventuali backtick markdown
    $text = preg_replace('/```json\s*|\s*```/', '', $text);
    $text = trim($text);

    $result = json_decode($text, true);
    if (!$result || json_last_error() !== JSON_ERROR_NONE) {
        error_log('Claude JSON parse error: ' . $text);
        return ['error' => 'Risposta AI non valida. Riprova.'];
    }

    // Sanifica valori numerici
    $result['estimated_min']    = (int) ($result['estimated_min'] ?? 0);
    $result['estimated_max']    = (int) ($result['estimated_max'] ?? 0);
    $result['confidence_score'] = max(0, min(100, (int) ($result['confidence_score'] ?? 0)));

    return $result;
}

// Ridimensiona e converte immagine in WebP per SEO performance
function processImage(string $srcPath, string $destDir, string $seoName): string|false {
    $mime = mime_content_type($srcPath);
    $img  = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($srcPath),
        'image/png'  => imagecreatefrompng($srcPath),
        'image/webp' => imagecreatefromwebp($srcPath),
        default      => false,
    };

    if (!$img) {
        return false;
    }

    $origW = imagesx($img);
    $origH = imagesy($img);
    $maxSide = 1200;

    if ($origW > $maxSide || $origH > $maxSide) {
        $ratio = min($maxSide / $origW, $maxSide / $origH);
        $newW  = (int) ($origW * $ratio);
        $newH  = (int) ($origH * $ratio);
        $resized = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);
        $img = $resized;
    }

    $destPath = rtrim($destDir, '/') . '/' . $seoName . '.webp';
    $ok = imagewebp($img, $destPath, 85);
    imagedestroy($img);

    return $ok ? $destPath : false;
}
