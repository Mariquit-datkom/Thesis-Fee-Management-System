<?php
/**
 * This script is used ONLY ONCE to get the Refresh Token.
 * After you get the token, you can delete this file.
 */

require 'vendor/autoload.php';

use League\OAuth2\Client\Provider\Google;

// Replace these with the values from your Google Cloud Console
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirectUri = 'http://localhost/thesis/get_oauth_token.php';

$provider = new Google([
    'clientId'     => $clientId,
    'clientSecret' => $clientSecret,
    'redirectUri'  => $redirectUri,
]);

if (!isset($_GET['code'])) {
    // Step 1: Redirect user to Google for authorization
    $options = [
        'scope' => ['https://mail.google.com/'],
        'access_type' => 'offline' // CRITICAL: This gives you the refresh token
    ];
    $authUrl = $provider->getAuthorizationUrl($options);
    header('Location: ' . $authUrl);
    exit;
} else {
    // Step 2: Exchange authorization code for tokens
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    echo "<h1>Copy this Refresh Token:</h1>";
    echo "<textarea style='width:100%; height:50px;'>" . $token->getRefreshToken() . "</textarea>";
    echo "<p>Put this token into your mailHandler.php file.</p>";
}