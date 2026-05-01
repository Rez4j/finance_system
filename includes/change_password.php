<?php
require_once '../../config/db.php';

function changePassword($user_id, $current_password, $new_password) {
    global $pdo;
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($current_password, $user['password'])) {
        return ['success' => false, 'message' => 'Current password is incorrect.'];
    }
    
    // Hash and update new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed_password, $user_id]);
    
    return ['success' => true, 'message' => 'Password updated successfully.'];
}
?>