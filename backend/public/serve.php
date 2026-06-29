<?php
// DEBUG MODE — remove after fixing
error_reporting(E_ALL);
ini_set('display_errors', '1');
set_exception_handler(function($e) {
    http_response_code(500);
    echo '<pre>ERROR: ' . $e->getMessage() . "\nFile: " . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString() . '</pre>';
    exit;
});

$encoded = $_GET['f'] ?? null;
if (!$encoded) { http_response_code(400); exit; }

$path = base64_decode(strtr($encoded, '-_', '+/'));

// Only allow private paths
if (!str_starts_with($path, 'documents/') && !str_starts_with($path, 'nid_documents/')) {
    http_response_code(403); exit;
}

// Bootstrap Laravel (env, config, DB, providers — no HTTP request handling)
define('LARAVEL_START', microtime(true));
require '/home/annoghor/repositories/ApexTutor/backend/vendor/autoload.php';
$app = require_once '/home/annoghor/repositories/ApexTutor/backend/bootstrap/app.php';
$app->usePublicPath(__DIR__);
$app->bootstrapWith([
    Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

// Auth — read Sanctum token from cookie
$tokenString = $_COOKIE['auth_token'] ?? null;
if (!$tokenString) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Unauthenticated.']);
    exit;
}

$tokenModel = Laravel\Sanctum\PersonalAccessToken::findToken($tokenString);
$user       = $tokenModel?->tokenable;

if (!$user) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['message' => 'Unauthenticated.']);
    exit;
}

$isAdmin = $user->role === 'super_admin';

// IDOR check
if (str_starts_with($path, 'nid_documents/')) {
    $ownerId = explode('/', $path)[1] ?? null;
    if (!$isAdmin && (string) $user->id !== (string) $ownerId) {
        http_response_code(403); exit;
    }
} elseif (str_starts_with($path, 'documents/')) {
    if (!$isAdmin) {
        $owns = App\Models\TutorDocument::where('file_path', $path)
            ->whereHas('tutorProfile', fn($q) => $q->where('user_id', $user->id))
            ->exists();
        if (!$owns) { http_response_code(403); exit; }
    }
}

// Serve the file
$file = rtrim(config('filesystems.disks.public.root'), '/') . '/' . $path;
if (!file_exists($file)) { http_response_code(404); exit; }

$mime = mime_content_type($file) ?: 'application/octet-stream';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($file));
header('Content-Disposition: inline; filename="' . basename($file) . '"');
header('Cache-Control: private, max-age=3600');
readfile($file);
