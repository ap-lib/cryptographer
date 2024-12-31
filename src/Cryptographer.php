<?php declare(strict_types=1);

namespace AP\Cryptographer;

use UnexpectedValueException;

readonly class Cryptographer
{
    public int  $required_iv_length;
    public bool $allowed_metadata;

    public CipherAlgo $cipher_algo;
    public int        $tag_length;

    /**
     * @param CipherAlgo|string $cipher_algo The cipher method. For a list of available cipher methods, use
     *                                      {@see CipherAlgo} and {@see openssl_get_cipher_methods()}
     * @param string $passphrase The secret key
     * @param int $options options is a bitwise disjunction of the flags OPENSSL_RAW_DATA and OPENSSL_ZERO_PADDING.
     * @param int $tag_length Works only for CCM, GCM, and OCB cipher algo
     *                  The longer the key, the more reliable the integrity check becomes.
     *                  However, this also means that more additional bytes must be stored with each encrypted record.
     */
    public function __construct(
        CipherAlgo|string                    $cipher_algo,
        #[\SensitiveParameter] public string $passphrase,
        public int                           $options = OPENSSL_RAW_DATA,
        int                                  $tag_length = 16
    )
    {
        if (is_string($cipher_algo)) {
            $cipher_algo = CipherAlgo::from(strtolower($cipher_algo));
        }
        $this->cipher_algo = $cipher_algo;

        if (!in_array($cipher_algo->value, openssl_get_cipher_methods())) {
            throw new UnsupportedAlgorithmException(
                'The cipher algorithm "' . $cipher_algo->value .
                '" is not available. Ensure the algorithm is supported by your OpenSSL installation.'
            );
        }

        try {
            if (!$this->cipher_algo->isAllowedTagLength($tag_length)) {
                throw new UnexpectedValueException(
                    "invalid tag length, for the cipher algo `{$this->cipher_algo->value}` allowed length are: " .
                    implode(", ", $this->cipher_algo->getAllowedTagLength())
                );
            }
            $this->tag_length = $tag_length;
        } catch (TagNotUsedException) {
            $this->tag_length = 0;
        }

        $this->required_iv_length = $this->cipher_algo->iv_length();
        $this->allowed_metadata   = $this->cipher_algo->isAllowedMetadata();
    }

    public function random_iv(): string
    {
        return $this->required_iv_length == 0 ? "" : openssl_random_pseudo_bytes($this->required_iv_length);
    }

    /**
     * @param string $original_body The plaintext data to be encrypted.
     * @param string|null $iv The Initialization Vector (IV) for the encryption process.
     *                        Highly recommended to set this parameter to null to automatically
     *                        generate a unique IV for each encryption operation, ensuring maximum
     *                        security. Providing a custom IV is not recommended unless you have
     *                        specific requirements and fully understand the security implications.
     * @param string $metadata Additional authenticated data (AAD).
     * *                    AAD is not encrypted but is included in the authentication process to ensure its integrity.
     * *                    It serves as metadata or auxiliary information tied to the ciphertext and must be stored in clear text.
     * *                    The same AAD must be provided during decryption to verify the authenticity and integrity
     * *                    of both the encrypted data and the associated AAD.
     *
     * @return EncryptedPayload Returns an instance of Result containing the encrypted data and the used IV.
     * @throws UnexpectedValueException
     */
    public function encrypt(
        #[\SensitiveParameter] string $original_body,
        ?string                       $iv = null,
        string                        $metadata = "",
    ): EncryptedPayload
    {
        if (is_null($iv)) {
            $iv = $this->random_iv();
        }

        if (strlen($iv) != $this->required_iv_length) {
            throw new UnexpectedValueException(
                "for the cipher algo `{$this->cipher_algo->value}` iv length must be $this->required_iv_length"
            );
        }

        if (!$this->allowed_metadata && $metadata != "") {
            throw new UnexpectedValueException(
                "for the cipher algo `{$this->cipher_algo->value}` metadata is no allowed"
            );
        }

        $tag            = "";
        $encrypted_body = openssl_encrypt(
            data: $original_body,
            cipher_algo: $this->cipher_algo->value,
            passphrase: $this->passphrase,
            options: $this->options,
            iv: $iv,
            tag: $tag,
            aad: $metadata,
            tag_length: $this->tag_length,
        );

        if (false === $encrypted_body) {
            throw new UnexpectedValueException('Encrypting failed: ' . openssl_error_string());
        }

        return new EncryptedPayload(
            encrypted_body: $encrypted_body,
            iv: $iv,
            tag: $tag,
            metadata: $metadata,
        );
    }

    /**
     * @param string $encrypted_body
     * @param string $iv
     * @param string|null $tag The authentication tag generated during encryption (required for AEAD ciphers like GCM).
     * @param string $metadata isn't encrypted but is included in the authentication process to ensure its integrity.
     *                     It can be a metadata or any auxiliary information tied to the ciphertext,
     *                     but it must match the metadata used during encryption.
     * @return string
     *
     * @throws DecryptingFailed original body
     */
    public function decrypt(
        string  $encrypted_body,
        string  $iv,
        ?string $tag = null,
        string  $metadata = "",
    ): string
    {
        if (!$this->allowed_metadata && $metadata != "") {
            throw new UnexpectedValueException(
                "for the cipher algo `{$this->cipher_algo->value}` metadata is no allowed"
            );
        }

        $res = openssl_decrypt(
            data: $encrypted_body,
            cipher_algo: $this->cipher_algo->value,
            passphrase: $this->passphrase,
            options: $this->options,
            iv: $iv,
            tag: $tag,
            aad: $metadata,
        );

        if (false === $res) {
            $message = openssl_error_string();
            throw new DecryptingFailed(is_string($message) ? $message : "");
        }

        return $res;
    }

    /**
     * @param EncryptedPayload $payload
     * @return string
     * @throws UnexpectedValueException
     */
    public function decryptPayload(EncryptedPayload $payload): string
    {
        return $this->decrypt(
            encrypted_body: $payload->encrypted_body,
            iv: $payload->iv,
            tag: $payload->tag,
            metadata: $payload->metadata
        );
    }

    public function decryptSerializedPayload(string $serialized_payload, string $meta_data = ""): string
    {
        return $this->decryptPayload(
            EncryptedPayload::deserialize(
                $this->cipher_algo,
                $serialized_payload,
                $meta_data
            )
        );
    }
}
