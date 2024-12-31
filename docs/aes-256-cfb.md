[Home](..%2FREADME.md) / **aes-256-cfb**

> suitable to get encrypted with no check integrity


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
    cipher_algo: CipherAlgo::aes_256_cfb,
    passphrase: $passphrase,
);

$original_message = json_encode([
    "user_id" => 12345,
    "action" => "update_profile",
    "timestamp" => time(),
]);

// Encrypt message to payload
// (you can use data from this object:
// 1. $payload->encrypted_body
// 2. $payload->iv
// or serialize it to get one binary string)
$payload = $cryptographer->encrypt(original_body: $original_message);

$serialized_payload = $payload->serialize();

```

### decrypt from serialized payload:
```php
// get $serialized_payload
$serialized_payload = $serialized_payload;

$restored_message = $cryptographer->decryptSerializedPayload(
    serialized_payload: $serialized_payload
);

echo $restored_message;
```

### decrypt from separate saved data:
```php
// get saved $encrypted_body and $iv
$encrypted_body = $payload->encrypted_body;
$iv             = $payload->iv;


$restored_message = $cryptographer->decryptPayload(
    payload: new EncryptedPayload(
        encrypted_body: $payload->encrypted_body,
        iv: $iv
    )
);

echo $restored_message;
```