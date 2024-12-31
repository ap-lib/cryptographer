[Home](..%2FREADME.md) / **aes-256-gcm**

> suitable to get encrypted with integrity check

### load library and passphrase

```php
use AP\Cryptographer\Cryptographer;

// IMPORTANT: Ensure this passphrase is stored in a secure location (e.g., environment variables, secrets manager)
// ATTENTION: Don't use this passphrase in production
$passphrase    = base64_decode("HlngqpWdmYx4QmYuiO4zCYpOX65qhpfmBPqlAgQuxAk=");

```

### encrypt:
```php
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
    "user_ip" => "123.1.2.3",
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
    metadata: $metadata, // if no need metadata, no add this line
);


$serialized_payload = $payload->serialize();

// save $serialized_payload + (optional if used)$metadata

```

### decrypt from serialized payload:
```php
// get $serialized_payload + (optional if used) $metadata
$serialized_payload = $serialized_payload;

$restored_message = $cryptographer->decryptSerializedPayload(
    serialized_payload: $serialized_payload,
    meta_data: $metadata,  // if no need metadata, no add this line
);

echo $restored_message;
```

### decrypt from separate saved data:
```php
// get saved $encrypted_body, $iv, $tag, (optional) $metadata
$encrypted_body = $payload->encrypted_body;
$iv             = $payload->iv;
$tag            = $payload->tag;
$metadata       = $metadata;


$restored_message = $cryptographer->decryptPayload(
    payload: new EncryptedPayload(
        encrypted_body: $encrypted_body,
        iv: $iv,
        tag: $tag,
        metadata: $metadata, // if no need metadata, no add this line
    )
);

echo $restored_message;
```