# The Cryptographer

## Generate the passphrase once and save it securely in your configuration

> **IMPORTANT:** Ensure this passphrase is stored in a secure location (e.g., environment variables, secrets manager)

### PHP:

```php
$algo       = "aes-256-cfb";
$key_length = openssl_cipher_key_length();
$passphrase = openssl_random_pseudo_bytes($key_length);

// if you're planing to save this to the environment, better to make base64
$passphrase_base64 = base64_encode($passphrase);
```

### Bash:

```bash
echo $(openssl rand -base64 32)
```

## Use cases:

| Algorithm                                | Integrity Check | Overhead                                  | Usage Example                                                                                                                                                                                                                                    |
|------------------------------------------|-----------------|-------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| [**AES-256-GCM**](docs%2Faes-256-gcm.md) | ✅ Yes           | 16-28 bytes, IV 12 bytes + Tag 4-12 bytes | Encrypting sensitive messages where data integrity is critical, e.g., API tokens.<br/>Storing sensitive configuration data with integrity protection.<br/>Optional authenticated additional data (AAD), not included in ciphertext but verified. |
| [**AES-256-CFB**](docs%2Faes-256-cfb.md) | ❌ No            | 16 bytes, IV only                         | Streaming data encryption where low latency is required, e.g., video streaming.                                                                                                                                                                  |