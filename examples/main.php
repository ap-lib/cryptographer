<?php

use AP\Cryptographer\CipherAlgo;
use AP\Cryptographer\Cryptographer;
use AP\Cryptographer\EncryptedPayload;

include "../vendor/autoload.php";

// Later use the saved passphrase to initialize the cryptographer
// ATTENTION: Don't use this passphrase in production
$passphrase = base64_decode("HlngqpWdmYx4QmYuiO4zCYpOX65qhpfmBPqlAgQuxAk=");
$cryptographer = new Cryptographer(
    cipher_algo: CipherAlgo::aes_256_gcm,
    passphrase: $passphrase,
    tag_length: 16, // for `gcm` you can use 16, 14, 12, 10, 8, 6, and 4
);

$original_message = json_encode([
    "user_id" => 12345,
    "action" => "update_profile",
    "timestamp" => time(),
]);


// Optional metadata
// isn't encrypted but is included in the authentication process to ensure its integrity.
// It can be a metadata or any auxiliary information tied to the ciphertext,
// but it must match the metadata used during encryption.
$metadata = json_encode([
    "user_ip" => "123.1.2.3"
]);

// Encrypt message to payload
// (you can use data from this object:
// 1. $payload->encrypted_body
// 2. $payload->iv
// 3. $payload->tag
// 4. (optional if used) $payload->metadata
// or serialize it to get one binary string)
$payload = $cryptographer->encrypt(
    original_body: $original_message,
    metadata: $metadata
);


$serialized_payload = $payload->serialize();

// save $serialized_payload + (optional if used)$metadata
// ------------------
// get $serialized_payload

//$restored_payload = EncryptedPayload::deserialize(
//    cipher_algo: $cryptographer->cipher_algo,
//    serialized_payload: $serialized_payload,
//    meta_data: $metadata
//);
//
//$restored_message = $cryptographer->decryptPayload($restored_payload);

//$restored_message = $cryptographer->decryptSerializedPayload(
//    serialized_payload: $serialized_payload,
//    meta_data: $metadata
//);

//// Deserialize and decrypt the message
$restored_message = $cryptographer->decryptPayload(
    payload: new EncryptedPayload(
        encrypted_body: $payload->encrypted_body,
        iv: $payload->iv,
        tag: $payload->tag,
        metadata: $metadata
    )
);

echo $serialized_payload . "\r\n\r\n";
echo $restored_message;