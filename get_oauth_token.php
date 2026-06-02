<?php
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// IMPORTANT: Fill these in from your Google Cloud Console
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirectUri = 'http://localhost/thesis/get_oauth_token.php';

$provider = new Google([
    'clientId'     => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri'  => $redirectUri,
]);

if (!isset($_GET['code'])) {
    // Step 1: Get the authorization URL
    $options = [
        'scope' => [
            'https://mail.google.com/'
        ],
        'access_type' => 'offline',
        'prompt' => 'consent' // Forces Google to provide a Refresh Token
    ];
    $authUrl = $provider->getAuthorizationUrl($options);
    header('Location: ' . $authUrl);
    exit;
} else {
    // Step 2: Exchange the code for a Refresh Token
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    echo "<h3>Success!</h3>";
    echo "Your Refresh Token is: <br><input style='width:100%; padding:10px;' value='" . $token->getRefreshToken() . "'>";
    echo "<br><br>Copy this into your .env file under GOOGLE_REFRESH_TOKEN";
}