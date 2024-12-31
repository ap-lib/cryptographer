<?php

use AP\Cryptographer\CipherAlgo;
use AP\Cryptographer\Cryptographer;
use AP\Cryptographer\EncryptedPayload;

include "../vendor/autoload.php";

// Later use the saved passphrase to initialize the cryptographer
// ATTENTION: Don't use this passphrase in production
$passphrase = base64_decode("HlngqpWdmYx4QmYuiO4zCYpOX65qhpfmBPqlAgQuxAk=");

$cryptographer = new Cryptographer(
    cipher_algo: CipherAlgo::aes_128_ccm,
    passphrase: $passphrase,
    tag_length: 16
);

$original_message = json_encode([
    "user_id"   => 12345,
    "action"    => "update_profile",
    "timestamp" => time(),
]);

$metadata = "hello world";

// Encrypt and serialize the message
$payload = $cryptographer->encrypt(
    original_body: $original_message,
    metadata: $metadata,
);

$serialized_payload = $payload->serialize();

// save $serialized_payload
// ------------------
// get $serialized_payload

$restored_payload = EncryptedPayload::deserialize(
    cipher_algo: $cryptographer->cipher_algo,
    serialized_payload: $serialized_payload,
    meta_data: $metadata . "1"
);

// Deserialize and decrypt the message
$restored_message = $cryptographer->decryptPayload(
    payload: $restored_payload
);

echo $serialized_payload . "\r\n\r\n";
echo $restored_message;