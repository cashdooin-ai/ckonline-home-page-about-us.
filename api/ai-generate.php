<?php
// AI Content Generation Endpoint — CollegeKampus
// Uses the same multi-provider AI system (Gemini/Groq/OpenAI/Anthropic/
// Cohere) already configured in the main admin's AI Providers settings -
// see includes/ai-chat.php.
//
// Admin-only (called from admin/generate.php's own JS, same-origin) - had
// no auth check at all, so any visitor could POST a topic here and burn
// the site's paid AI provider quota, and the wildcard CORS header let any
// other website automate that from its own visitors' browsers too. Session
// check is the same ck_admin_auth flag admin/auth.php's page-level guard
// uses; CORS headers removed entirely since the only real caller is
// same-origin and never needed them.

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'POST method required']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['ck_admin_auth'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Admin login required']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/ai-chat.php';

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

// ── Call AI (first configured provider that succeeds) ─────────────────────────
try {
    $db = getDB();
    $result = kampusAIGetReply($db, '', [['role' => 'user', 'parts' => [['text' => $prompt]]]]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

$generatedContent = trim($result['reply'] ?? '');

if (!$generatedContent) {
    http_response_code(500);
    echo json_encode(['error' => 'No content returned from AI.']);
    exit;
}

echo json_encode([
    'content'  => $generatedContent,
    'type'     => $type,
    'subject'  => $subject,
    'provider' => $result['provider'],
    'model'    => $result['model'],
]);
