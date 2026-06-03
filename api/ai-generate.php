<?php
// AI Content Generation Endpoint — CollegeKampus
// Uses Claude API (claude-sonnet-4-6) to generate SEO blog posts and program pages.
// NOTE: Set ANTHROPIC_API_KEY environment variable on server.

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST method required']);
    exit;
}

$apiKey = getenv('ANTHROPIC_API_KEY') ?: '';
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'ANTHROPIC_API_KEY not set on server. Contact the administrator.']);
    exit;
}

$type    = $_POST['type']    ?? 'blog';
$topic   = trim($_POST['topic']   ?? '');
$program = trim($_POST['program'] ?? '');

if (!$topic && !$program) {
    http_response_code(400);
    echo json_encode(['error' => 'Provide either topic (for blog) or program (for program page)']);
    exit;
}

// ── Build prompt ──────────────────────────────────────────────────────────────
if ($type === 'program') {
    $subject = $program ?: $topic;
    $prompt = <<<PROMPT
Write a comprehensive 3000-word program detail page in HTML for "Online {$subject} Degree in India" targeting Indian students searching for online education options in 2025.

Requirements:
- Write ONLY the inner HTML content (no <html>, <head>, <body> tags)
- Use Bootstrap 5 compatible classes where applicable
- Include these sections with proper H2/H3 headings:
  1. What is Online {$subject}? (overview paragraph)
  2. Career Scope & Job Opportunities (bullet list with salary ranges in INR)
  3. Eligibility & Admission Requirements
  4. Popular Specializations (if applicable)
  5. Top Universities Offering Online {$subject} in India (comparison table with University, Fees, Duration, NAAC Grade columns)
  6. Fee Structure & EMI Options
  7. FAQs (8 questions as HTML accordion using Bootstrap collapse)
  8. Conclusion with CTA paragraph ending: "Get Free Counselling from CollegeKampus and find the perfect online {$subject} program for your career goals."
- Use realistic Indian university names, fees in INR (₹), and India-specific context
- Include at least one comparison table
- Use <strong> for important terms, <ul>/<ol> for lists
- Keep tone friendly, informative, and helpful for Indian students
PROMPT;
} else {
    $subject = $topic ?: $program;
    $prompt = <<<PROMPT
Write a 1500-word SEO-optimized blog post in HTML about "{$subject}" for Indian students considering online education in 2025.

Requirements:
- Write ONLY the inner HTML content (no <html>, <head>, <body> tags)  
- Use proper H2 and H3 headings (at least 4 H2 sections)
- Include bullet lists for key points
- Include one comparison table (university/course comparison with fees, duration, highlights)
- Use <strong> for important keywords and facts
- Include specific data: fees in INR (₹), India-specific universities (Amity, LPU, Chandigarh University, Symbiosis, Jain, etc.)
- Tone: expert but friendly, aimed at Indian students aged 22-35
- End with a Conclusion section that includes: "Get Free Counselling from CollegeKampus to find the best program that matches your career goals and budget."
- Add a pro-tip box using: <div class="alert alert-info">💡 Pro Tip: ...</div>
- Do NOT include any markdown, only clean HTML
PROMPT;
}

// ── Call Claude API ───────────────────────────────────────────────────────────
$payload = json_encode([
    'model'      => 'claude-sonnet-4-6',
    'max_tokens' => 4096,
    'messages'   => [
        ['role' => 'user', 'content' => $prompt]
    ]
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_HTTPHEADER     => [
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
        'content-type: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    http_response_code(500);
    echo json_encode(['error' => 'Network error contacting AI: ' . $curlErr]);
    exit;
}

$data = json_decode($response, true);

if ($httpCode !== 200) {
    $errMsg = $data['error']['message'] ?? $response;
    http_response_code(502);
    echo json_encode(['error' => 'Claude API error (' . $httpCode . '): ' . $errMsg]);
    exit;
}

$generatedContent = $data['content'][0]['text'] ?? '';

if (!$generatedContent) {
    http_response_code(500);
    echo json_encode(['error' => 'No content returned from AI. Response: ' . substr($response, 0, 200)]);
    exit;
}

echo json_encode([
    'content'     => $generatedContent,
    'type'        => $type,
    'subject'     => $subject,
    'tokens_used' => $data['usage']['output_tokens'] ?? 0,
]);
