<?php
require_once __DIR__ . '/../../../include/config.php';
// peep: the old public reset (username + invite code) let anyone who knew those two take over an account.
// It is switched off. Admins reset passwords from /admin; signed-in users change theirs in
// Settings > Security and login (which needs the current password).
header('Content-Type: application/json');
http_response_code(410);
echo json_encode(['error' => 'Password reset is handled by an admin now.']);
