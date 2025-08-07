<?php

#region start
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/autoload.php';

header("Content-Type: application/json; charset=utf-8");

#endregion start

if (!empty($_POST['website'])) {
    error_log("ERROR 4352125814515388278318435676292535151350");
    echo json_encode(false);
    die();
}

$username = $_POST["username"] ?? null;

if (!$username) {
    error_log("ERROR 4419534678838408807240903561936269558827");
    echo json_encode(false);
    die();
}

$userResult = mysql_fetch_array("SELECT * FROM users WHERE username = ? LIMIT 1", [$username]);

if (!$userResult || count($userResult) === 0 || count($userResult[0]) === 0) {
    error_log("ERROR 5071326077071812673836577747224863850279");
    echo json_encode(false);
    die();
}

$user = $userResult[0];
$otp_code = rand(100000, 999999);

if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->safeLoad();
}

$apiKey = $_ENV['BREVO_API_KEY'] ?? getenv('BREVO_API_KEY') ?? null;
$fromEmail = $_ENV['BREVO_EMAIL'] ?? getenv('BREVO_EMAIL') ?? null;
$toEmail = $user["email"] ?? null;

if (!$apiKey || !$fromEmail || !$toEmail) {
    error_log("ERROR 5701820883326924409822092047126152297311");
    echo json_encode(false);
    die();
}

try {
    $deleteSessionsQuery = "DELETE FROM user_sessions WHERE username = ?";
    mysql_prepared_execute($deleteSessionsQuery, [$username]);

    $deleteOtpQuery = "DELETE FROM otp_session WHERE username = ?";
    mysql_prepared_execute($deleteOtpQuery, [$username]);

    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $insertOtpQuery = "INSERT INTO otp_session (username, code, expires_at) VALUES (?, ?, ?)";
    mysql_prepared_execute($insertOtpQuery, [$username, $otp_code, $expiresAt]);

    $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
    $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(new GuzzleHttp\Client(), $config);

    $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail([
        'subject' => 'קוד אימות',
        'sender' => ['name' => 'Whatsapp Clone', 'email' => $fromEmail],
        'to' => [['email' => $toEmail]],
        'htmlContent' => "<html><body><h1>קוד האימות שלך הוא: $otp_code</h1><p>הקוד תקף למשך 10 דקות</p></body></html>",
        'textContent' => "קוד האימות שלך הוא: $otp_code\nהקוד תקף למשך 10 דקות",

    ]);

    $result = $apiInstance->sendTransacEmail($sendSmtpEmail);

    echo json_encode(true);
} catch (Exception $e) {
    error_log("ERROR 6214503641418744836871071764666657540768");
    echo json_encode(false);
    die();
}
