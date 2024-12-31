# The Cryptographer
## Generate the passphrase once and save it securely in your configuration
> **IMPORTANT:** Ensure this passphrase is stored in a secure location (e.g., environment variables, secrets manager)

###  PHP:
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

- [**aes-256-cfb** :: suitable to get encrypted with no check integrity](docs%2Faes-256-cfb.md)
- [**aes-256-gcm** - suitable to get encrypted with integrity check](docs%2Faes-256-gcm.md)