<?php

require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

$sid   
$token  // Your Auth Token

try {
    $twilio = new Client($sid, $token);

    $message = $twilio->messages->create(
        "", // Recipient phone number
        [
            "from" => "", // Twilio phone number
            "body" => "why" // Message content
        ]
    );

    echo "Message SID: " . $message->sid;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
