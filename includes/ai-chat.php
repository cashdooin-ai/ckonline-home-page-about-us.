<?php
/**
 * KampusAI — Multi-Provider AI Chat
 *
 * Verbatim copy of ckampus-dasboard's shared/includes/ai-chat.php. That
 * repo's admin already has a working "AI Providers" settings page and
 * admin_settings-backed API keys - this portal reads the exact same table
 * on the same shared database instead of requiring its own separate
 * ANTHROPIC_API_KEY server env var (which was never set here, so the AI
 * Content Generator/AI Suggest features always failed). Configure keys
 * once in the main admin; both apps use them automatically. Keep this in
 * sync with the original if it changes there.
 *
 * Supported providers (tried in priority order, skipping any without a key):
 *   gemini    — Google Gemini (free tier, self-updating "-latest" model aliases -
 *               see ckAIProviderDefaults(); auto-discovers a live model via
 *               ckAIDiscoverGeminiModel() if Google retires them again)
 *   groq      — Groq inference (free tier: Llama 3.3 70B, very fast)
 *   openai    — OpenAI ChatGPT (paid: gpt-4o-mini or gpt-4o)
 *   anthropic — Anthropic Claude (paid: claude-haiku-4-5)
 *   cohere    — Cohere Command (free tier available)
 *
 * API keys are stored in admin_settings under category='ai_providers'.
 * Provider order is controlled by the 'provider_order' setting.
 *
 * Usage:
 *   require_once __DIR__ . '/ai-chat.php';
 *   $result = kampusAIGetReply($pdo, $systemPrompt, $contents);
 *   // $result = ['reply' => string, 'provider' => string, 'model' => string]
 */

// ============================================================================
// Public API
// ============================================================================

/**
 * Get an AI reply using configured providers, falling back automatically.
 *
 * @param  PDO    $pdo
 * @param  string $systemPrompt  The system/persona instructions.
 * @param  array  $contents      Gemini-format multi-turn array
 *                               [{role:'user'|'model', parts:[{text:'...'}]}, ...]
 * @return array  ['reply'=>string, 'provider'=>string, 'model'=>string, 'ms'=>int]
 * @throws Exception  When all configured providers fail.
 */
function kampusAIGetReply(PDO $pdo, string $systemPrompt, array $contents): array
{
    $providers = ckAIGetActiveProviders($pdo);

    if (empty($providers)) {
        throw new Exception('No AI providers configured. Add an API key in Admin → AI Settings.');
    }

    $lastException = null;
    foreach ($providers as $p) {
        $t0 = microtime(true);
        try {
            $reply = ckAICallProvider($p, $systemPrompt, $contents);
            return [
                'reply'    => $reply,
                'provider' => $p['provider'],
                'model'    => $p['model'],
                'ms'       => (int)((microtime(true) - $t0) * 1000),
            ];
        } catch (Exception $e) {
            $ms = (int)((microtime(true) - $t0) * 1000);
            error_log("KampusAI [{$p['provider']}] failed ({$ms}ms): " . $e->getMessage());
            $lastException = $e;
            // Continue to the next provider
        }
    }

    throw $lastException ?? new Exception('All AI providers failed.');
}

/**
 * Return a diagnostics array for all configured providers.
 * Tries a simple "Hello" request against each one.
 */
function kampusAIDiagnostics(PDO $pdo): array
{
    $providers = ckAIGetActiveProviders($pdo);
    $results   = [];

    $testContents = [['role' => 'user', 'parts' => [['text' => 'Say hello in one word.']]]];
    $testPrompt   = 'You are a helpful assistant.';

    foreach ($providers as $p) {
        $entry = ['provider' => $p['provider'], 'model' => $p['model'], 'ok' => false];
        $t0 = microtime(true);
        try {
            $reply = ckAICallProvider($p, $testPrompt, $testContents);
            $entry['ok']    = true;
            $entry['reply'] = substr($reply, 0, 80);
            $entry['ms']    = (int)((microtime(true) - $t0) * 1000);
        } catch (Exception $e) {
            $entry['error'] = $e->getMessage();
            $entry['ms']    = (int)((microtime(true) - $t0) * 1000);
        }
        $results[] = $entry;
    }

    // List unconfigured providers too
    $allProviders = ['gemini', 'groq', 'openai', 'anthropic', 'cohere'];
    $configured   = array_column($providers, 'provider');
    foreach ($allProviders as $name) {
        if (!in_array($name, $configured, true)) {
            $results[] = ['provider' => $name, 'ok' => false, 'error' => 'No API key configured'];
        }
    }

    return $results;
}

// ============================================================================
// Provider loading
// ============================================================================

/**
 * Default model and fallback model for each provider.
 */
function ckAIProviderDefaults(): array
{
    return [
        // Google retired gemini-2.0-flash / gemini-2.0-flash-lite on 2026-06-01.
        // Use the "-latest" aliases so this doesn't silently break again on the
        // next model retirement - Google hot-swaps them with a 2-week notice.
        'gemini'    => ['model' => 'gemini-flash-latest', 'fallback' => 'gemini-flash-lite-latest'],
        'groq'      => ['model' => 'llama-3.3-70b-versatile', 'fallback' => 'llama-3.1-8b-instant'],
        'openai'    => ['model' => 'gpt-4o-mini',             'fallback' => 'gpt-3.5-turbo'],
        'anthropic' => ['model' => 'claude-haiku-4-5-20251001', 'fallback' => null],
        'cohere'    => ['model' => 'command-r',               'fallback' => 'command-r-plus'],
    ];
}

/**
 * Load AI provider settings from admin_settings table.
 */
function ckAILoadSettings(PDO $pdo): array
{
    $out = [];
    try {
        $stmt = $pdo->prepare(
            "SELECT setting_key, setting_value FROM admin_settings
             WHERE category IN ('ai','ai_providers')
             ORDER BY setting_key"
        );
        $stmt->execute();
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
    } catch (Exception $e) {
        // Table may not exist yet
    }

    // Legacy fallback: GEMINI_API_KEY constant
    if (empty($out['gemini_api_key']) && defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') {
        $out['gemini_api_key'] = GEMINI_API_KEY;
    }

    return $out;
}

/**
 * Build the ordered list of providers that have an API key configured.
 */
function ckAIGetActiveProviders(PDO $pdo): array
{
    $settings = ckAILoadSettings($pdo);
    $defaults = ckAIProviderDefaults();

    // Provider order (comma-separated, admin-configurable)
    $orderStr = $settings['provider_order'] ?? 'gemini,groq,openai,anthropic,cohere';
    $order    = array_filter(array_map('trim', explode(',', $orderStr)));

    $active = [];
    foreach ($order as $name) {
        if (!isset($defaults[$name])) continue;
        $key = trim((string)($settings[$name . '_api_key'] ?? ''));
        if ($key === '') continue;

        $d = $defaults[$name];
        $active[] = [
            'provider' => $name,
            'api_key'  => $key,
            'model'    => trim((string)($settings[$name . '_model'] ?? '')) ?: $d['model'],
            'fallback' => $d['fallback'],
        ];
    }

    return $active;
}

// ============================================================================
// Provider router
// ============================================================================

function ckAICallProvider(array $p, string $systemPrompt, array $contents): string
{
    switch ($p['provider']) {
        case 'gemini':
            return ckAICallGemini($p, $systemPrompt, $contents);
        case 'groq':
            return ckAICallOpenAICompat($p, 'https://api.groq.com/openai/v1/chat/completions', $systemPrompt, $contents);
        case 'openai':
            return ckAICallOpenAICompat($p, 'https://api.openai.com/v1/chat/completions', $systemPrompt, $contents);
        case 'anthropic':
            return ckAICallAnthropic($p, $systemPrompt, $contents);
        case 'cohere':
            return ckAICallCohere($p, $systemPrompt, $contents);
        default:
            throw new Exception("Unknown AI provider: {$p['provider']}");
    }
}

// ============================================================================
// Google Gemini
// ============================================================================

function ckAICallGemini(array $p, string $systemPrompt, array $contents): string
{
    $models = array_unique(array_filter([$p['model'], $p['fallback']]));

    $payload = [
        'systemInstruction' => ['parts' => [['text' => $systemPrompt]]],
        'contents'          => $contents,
        'generationConfig'  => ['temperature' => 0.7, 'maxOutputTokens' => 2048],
    ];

    $lastEx = null;
    $anyModelNotFound = false;
    foreach ($models as $model) {
        $res = ckAICallGeminiModel($model, $payload, $p['api_key']);

        if ($res['code'] !== 200) {
            $msg = ckAIExtractError($res, "HTTP {$res['code']}");
            $lastEx = new Exception("HTTP {$res['code']}: {$msg}");
            if ($res['code'] === 404) {
                $anyModelNotFound = true;
                error_log("KampusAI Gemini: model '{$model}' not found, trying next.");
                continue;
            }
            throw $lastEx; // auth/quota/etc - fail fast, retrying won't help
        }

        return ckAIExtractGeminiText($res['data']);
    }

    // Every configured model id 404'd - Google periodically retires dated
    // Gemini model versions (this exact bug happened on 2026-06-01 when
    // gemini-2.0-flash / gemini-2.0-flash-lite were sunset). Rather than
    // fail until an admin manually finds and types in a new model name, ask
    // the API directly which models this key can use and try once more.
    if ($anyModelNotFound) {
        $discovered = ckAIDiscoverGeminiModel($p['api_key']);
        if ($discovered !== null && !in_array($discovered, $models, true)) {
            $res = ckAICallGeminiModel($discovered, $payload, $p['api_key']);
            if ($res['code'] === 200) {
                error_log("KampusAI Gemini: configured model(s) not found, auto-discovered '{$discovered}'. Update Admin -> Settings -> AI Providers -> Gemini Model to '{$discovered}' to skip this lookup next time.");
                return ckAIExtractGeminiText($res['data']);
            }
        }
    }

    throw $lastEx ?? new Exception('Gemini: all models failed');
}

function ckAICallGeminiModel(string $model, array $payload, string $apiKey): array
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key="
         . urlencode($apiKey);
    return ckAIHttpPost($url, $payload, ['Content-Type: application/json']);
}

function ckAIExtractGeminiText(?array $data): string
{
    $finishReason = $data['candidates'][0]['finishReason'] ?? '';
    if (in_array($finishReason, ['SAFETY', 'RECITATION'], true)) {
        return "I'm sorry, I can't respond to that. Please ask something else about colleges or courses.";
    }

    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    if ($text === '') {
        throw new Exception("Gemini returned empty response (finishReason: {$finishReason})");
    }
    return $text;
}

/**
 * Ask Google directly which models this API key can use for generateContent,
 * as a last-resort fallback when the configured model id(s) both 404. Prefers
 * a flash-lite model (cheapest/fastest free-tier option), then any flash
 * model, then whatever else supports generateContent.
 */
function ckAIDiscoverGeminiModel(string $apiKey): ?string
{
    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . urlencode($apiKey);
    $res = ckAIHttpGet($url);
    if ($res['code'] !== 200 || empty($res['data']['models']) || !is_array($res['data']['models'])) {
        return null;
    }

    $flashLite = null;
    $flash     = null;
    $any       = null;
    foreach ($res['data']['models'] as $m) {
        $methods = $m['supportedGenerationMethods'] ?? [];
        if (!in_array('generateContent', $methods, true)) continue;
        $id = preg_replace('#^models/#', '', (string)($m['name'] ?? ''));
        if ($id === '') continue;
        if ($any === null) $any = $id;
        if ($flash === null && stripos($id, 'flash') !== false) $flash = $id;
        if ($flashLite === null && stripos($id, 'flash-lite') !== false) $flashLite = $id;
    }
    return $flashLite ?? $flash ?? $any;
}

// ============================================================================
// OpenAI-compatible (OpenAI + Groq)
// ============================================================================

/**
 * Works for any OpenAI-compatible endpoint (OpenAI, Groq, Together, etc.)
 * Tries the fallback model automatically on 429 rate-limit responses.
 */
function ckAICallOpenAICompat(array $p, string $endpoint, string $systemPrompt, array $contents): string
{
    // Convert Gemini-format contents → OpenAI messages
    $messages = [['role' => 'system', 'content' => $systemPrompt]];
    foreach ($contents as $c) {
        $role       = ($c['role'] === 'model') ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'content' => $c['parts'][0]['text'] ?? ''];
    }

    $headers = [
        'Authorization: Bearer ' . $p['api_key'],
        'Content-Type: application/json',
    ];

    // Try primary model, then fallback model (if configured) on 429 rate-limit
    // or 404 model-not-found (providers like Groq retire/rename model ids too).
    $models  = array_values(array_unique(array_filter([$p['model'], $p['fallback'] ?? null])));
    $lastEx  = null;
    $anyModelNotFound = false;

    foreach ($models as $model) {
        $payload = [
            'model'       => $model,
            'messages'    => $messages,
            'max_tokens'  => 1024,
            'temperature' => 0.3,
        ];

        $res = ckAIHttpPost($endpoint, $payload, $headers);

        if ($res['code'] === 429) {
            $errMsg = ckAIExtractError($res, 'Rate limit reached');
            // Extract retry-after hint if present
            preg_match('/(\d+m\d+\.?\d*s|\d+\.?\d+s|\d+m)/', $errMsg, $m);
            $retryHint = isset($m[1]) ? " Retry after: {$m[1]}." : '';
            $lastEx = new Exception("HTTP 429: Rate limit reached for model '{$model}'.{$retryHint}");
            error_log("KampusAI [{$p['provider']}] 429 on model '{$model}'{$retryHint} — " . ($model !== end($models) ? 'trying fallback.' : 'no more fallbacks.'));
            continue; // try fallback model
        }

        if ($res['code'] === 404) {
            $anyModelNotFound = true;
            $lastEx = new Exception("HTTP 404: " . ckAIExtractError($res, "Model '{$model}' not found"));
            error_log("KampusAI [{$p['provider']}] model '{$model}' not found, trying next.");
            continue;
        }

        if ($res['code'] !== 200) {
            throw new Exception("HTTP {$res['code']}: " . ckAIExtractError($res, "HTTP {$res['code']}"));
        }

        $text = $res['data']['choices'][0]['message']['content'] ?? '';
        if ($text === '') throw new Exception('Provider returned empty response');
        return $text;
    }

    // Every configured model id 404'd - ask the provider's own /models
    // endpoint which ids this key can actually use, and try once more,
    // instead of failing outright (same self-healing pattern as Gemini).
    if ($anyModelNotFound) {
        $discovered = ckAIDiscoverOpenAICompatModel($endpoint, $p['api_key']);
        if ($discovered !== null && !in_array($discovered, $models, true)) {
            $payload = [
                'model'       => $discovered,
                'messages'    => $messages,
                'max_tokens'  => 1024,
                'temperature' => 0.3,
            ];
            $res = ckAIHttpPost($endpoint, $payload, $headers);
            if ($res['code'] === 200) {
                $text = $res['data']['choices'][0]['message']['content'] ?? '';
                if ($text !== '') {
                    error_log("KampusAI [{$p['provider']}]: configured model(s) not found, auto-discovered '{$discovered}'. Update Admin -> Settings -> AI Providers to skip this lookup next time.");
                    return $text;
                }
            }
        }
    }

    // All models rate-limited/not-found — throw a user-friendly message
    $hint = $lastEx ? $lastEx->getMessage() : 'Request failed.';
    throw new Exception($hint . ' Configure an additional AI provider (Gemini free tier recommended) in Admin → Settings → AI Providers.');
}

/**
 * Ask an OpenAI-compatible provider's own /models endpoint which model ids
 * this API key can actually use, as a last-resort fallback when the
 * configured model id(s) both 404 (providers like Groq retire/rename models
 * on their own schedule, same as Gemini).
 */
function ckAIDiscoverOpenAICompatModel(string $chatEndpoint, string $apiKey): ?string
{
    $modelsUrl = preg_replace('#/chat/completions$#', '/models', $chatEndpoint);
    $res = ckAIHttpGet($modelsUrl, ['Authorization: Bearer ' . $apiKey]);
    if ($res['code'] !== 200 || empty($res['data']['data']) || !is_array($res['data']['data'])) {
        return null;
    }
    foreach ($res['data']['data'] as $m) {
        $id = (string)($m['id'] ?? '');
        if ($id !== '') return $id;
    }
    return null;
}

// ============================================================================
// Anthropic Claude
// ============================================================================

function ckAICallAnthropic(array $p, string $systemPrompt, array $contents): string
{
    // Convert Gemini-format contents → Anthropic messages (must alternate user/assistant)
    $raw = [];
    foreach ($contents as $c) {
        $role  = ($c['role'] === 'model') ? 'assistant' : 'user';
        $raw[] = ['role' => $role, 'content' => $c['parts'][0]['text'] ?? ''];
    }
    $messages = ckAINormalizeAlternation($raw);

    $payload = [
        'model'      => $p['model'],
        'max_tokens' => 2048,
        'system'     => $systemPrompt,
        'messages'   => $messages,
    ];

    $res = ckAIHttpPost('https://api.anthropic.com/v1/messages', $payload, [
        'x-api-key: '         . $p['api_key'],
        'anthropic-version: 2023-06-01',
        'Content-Type: application/json',
    ]);

    if ($res['code'] !== 200) {
        throw new Exception("HTTP {$res['code']}: " . ckAIExtractError($res, "HTTP {$res['code']}"));
    }

    $text = $res['data']['content'][0]['text'] ?? '';
    if ($text === '') throw new Exception('Anthropic returned empty response');
    return $text;
}

// ============================================================================
// Cohere
// ============================================================================

function ckAICallCohere(array $p, string $systemPrompt, array $contents): string
{
    $messages = [['role' => 'system', 'content' => $systemPrompt]];
    foreach ($contents as $c) {
        $role       = ($c['role'] === 'model') ? 'assistant' : 'user';
        $messages[] = ['role' => $role, 'content' => $c['parts'][0]['text'] ?? ''];
    }

    $payload = [
        'model'    => $p['model'],
        'messages' => $messages,
    ];

    $res = ckAIHttpPost('https://api.cohere.com/v2/chat', $payload, [
        'Authorization: Bearer ' . $p['api_key'],
        'Content-Type: application/json',
    ]);

    if ($res['code'] !== 200) {
        $msg = is_array($res['data']) ? ($res['data']['message'] ?? "HTTP {$res['code']}") : "HTTP {$res['code']}";
        throw new Exception("HTTP {$res['code']}: {$msg}");
    }

    $text = $res['data']['message']['content'][0]['text'] ?? '';
    if ($text === '') throw new Exception('Cohere returned empty response');
    return $text;
}

// ============================================================================
// Shared helpers
// ============================================================================

/**
 * Execute a POST request via cURL.
 * Returns ['code'=>int, 'data'=>array|null, 'raw'=>string]
 */
function ckAIHttpPost(string $url, array $payload, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 45,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $raw     = curl_exec($ch);
    $code    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        throw new Exception("cURL error: {$curlErr}");
    }

    return [
        'code' => $code,
        'data' => json_decode($raw, true),
        'raw'  => (string) $raw,
    ];
}

/**
 * Execute a GET request via cURL. Never throws - callers treat code 0 /
 * null data as "couldn't reach it" and fall back gracefully.
 * Returns ['code'=>int, 'data'=>array|null, 'raw'=>string]
 */
function ckAIHttpGet(string $url, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $raw     = curl_exec($ch);
    $code    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['code' => 0, 'data' => null, 'raw' => ''];
    }

    return [
        'code' => $code,
        'data' => json_decode($raw, true),
        'raw'  => (string) $raw,
    ];
}

/**
 * Extract a human-readable error message from an API error response.
 */
function ckAIExtractError(array $res, string $fallback): string
{
    $d = $res['data'];
    if (!is_array($d)) return $fallback;

    // Gemini / Google style
    if (isset($d['error']['message'])) return substr($d['error']['message'], 0, 300);
    if (isset($d['error']['status']))  return $d['error']['status'];

    // OpenAI style
    if (isset($d['error']['code']))    return (string)$d['error']['code'];

    // Anthropic / Cohere style
    if (isset($d['message']))          return substr($d['message'], 0, 300);

    return $fallback;
}

/**
 * Ensure messages strictly alternate user/assistant (required by some providers).
 * Merges consecutive same-role messages and guarantees the first role is 'user'.
 */
function ckAINormalizeAlternation(array $messages): array
{
    if (empty($messages)) {
        return [['role' => 'user', 'content' => '']];
    }

    $out      = [];
    $prevRole = '';
    foreach ($messages as $m) {
        if ($m['role'] === $prevRole) {
            $out[count($out) - 1]['content'] .= "\n" . $m['content'];
        } else {
            $out[]    = $m;
            $prevRole = $m['role'];
        }
    }

    if ($out[0]['role'] !== 'user') {
        array_unshift($out, ['role' => 'user', 'content' => '']);
    }

    return $out;
}
