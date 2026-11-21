<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/layout.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !csrf_verify($_POST['csrf_token'] ?? null)) {
    header('Location: index.php');
    exit;
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

header('Location: login.php');
exit;
