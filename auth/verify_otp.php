<?php

#region start
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../modules/mysql.php';

header('Content-Type: application/json');

$username = $_POST["username"] ?? null;
$otpCode = $_POST["otp"] ?? null;
#endregion start

if (!$username || !$otpCode) {
    error_log("ERROR 1147612495891721083358115470285324157875");
    echo json_encode(false);
    die();
}

try {
    // Check if OTP exists and is valid
    $otpRecords = mysql_fetch_array(
        "SELECT * FROM otp_session WHERE username = ? AND code = ? ORDER BY expires_at DESC LIMIT 1",
        [$username, $otpCode]
    );

    if (empty($otpRecords)) {
        // OTP not found or invalid
        echo json_encode([
            'success' => false,
            'message' => 'קוד שגוי או לא קיים'
        ]);
        die();
    }

    $otpRecord = $otpRecords[0];

    $currentTime = time();
    $expiryTime = strtotime($otpRecord['expires_at']);

    if ($currentTime > $expiryTime) {
        mysql_delete('otp_session', ['id' => $otpRecord['id']]);

        echo json_encode([
            'success' => false,
            'message' => 'הקוד פג תוקף, אנא בקשי קוד חדש'
        ]);
        die();
    }

    $userRecords = mysql_fetch_array(
        "SELECT * FROM users WHERE username = ?",
        [$username]
    );

    if (empty($userRecords)) {
        echo json_encode([
            'success' => false,
            'message' => 'משתמש לא נמצא'
        ]);
        die();
    }

    $userData = $userRecords[0];

    mysql_delete('otp_session', ['id' => $otpRecord['id']]);

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['username'] = $userData['username'];
    $_SESSION['logged_in'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'התחברת בהצלחה',
        'user' => [
            'id' => $userData['id'],
            'username' => $userData['username'],
            'full_name' => $userData['full_name'] ?? '',
            'profile_pic' => $userData['profile_pic'] ?? ''
        ]
    ]);
} catch (Exception $e) {
    error_log("OTP verification error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'שגיאה בשרת, נסי שוב מאוחר יותר'
    ]);
}
