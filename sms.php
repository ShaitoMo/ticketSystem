<?php

require_once 'vendor/autoload.php';

use Twilio\Rest\Client;

$sid    = "ACf909be81510c3bd2fc9e32cfdd1122b3"; // Your Account SID
$token  = "b39c65e3b36824f9f5ef701c1796a868"; // Your Auth Token

try {
    $twilio = new Client($sid, $token);

    $message = $twilio->messages->create(
        "+96178838334", // Recipient phone number
        [
            "from" => "+17573514865", // Twilio phone number
            "body" => "why" // Message content
        ]
    );

    echo "Message SID: " . $message->sid;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
