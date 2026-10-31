<?php
require_once __DIR__ . '/../../include/config.php';
session_start();
// peep: change your own password. Needs your CURRENT password (replaces Chirp's public reset).
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error' => 'Use POST']); exit; }
if (empty($_SESSION['user_id']) && empty($_SESSION['username'])) { http_response_code(401); echo json_encode(['error' => 'Sign in first.']); exit; }
if (!peep_same_origin()) { http_response_code(400); echo json_encode(['error' => 'Wrong site.']); exit; }
$current = $_POST['currentPassword'] ?? ''; $new = $_POST['newPassword'] ?? ''; $confirm = $_POST['confirmPassword'] ?? '';
if (strlen($new) < 8) { echo json_encode(['error' => 'New password needs at least 8 characters.']); exit; }
if ($new !== $confirm) { echo json_encode(['error' => 'The new passwords do not match.']); exit; }
$db = peep_db();
$st = $db->prepare('SELECT id, password_hash FROM users WHERE LOWER(username) = LOWER(:u)');
$st->execute(['u' => $_SESSION['username']]);
$user = $st->fetch(PDO::FETCH_ASSOC);
if (!$user || !password_verify($current, $user['password_hash'])) { echo json_encode(['error' => 'Your current password is not right.']); exit; }
$up = $db->prepare('UPDATE users SET password_hash = :h WHERE id = :id');
$up->execute(['h' => password_hash($new, PASSWORD_DEFAULT), 'id' => $user['id']]);
echo json_encode(['success' => true]);
