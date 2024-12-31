<?php

namespace AP\Cryptographer;

use UnexpectedValueException;

readonly class EncryptedPayload
{
    public function __construct(
        public string  $encrypted_body,
        public string  $iv,
        public ?string $tag = null,
        public string  $metadata = "",
    )
    {
    }

    /**
     * NOTE: Metadata no serialized to binary, you need to save or serialize it separately
     *
     * @return string
     */
    public function serialize(): string
    {
        return is_string($this->tag) ?
            chr(strlen($this->tag)) . $this->encrypted_body . $this->iv . $this->tag :
            $this->encrypted_body . $this->iv;
    }

    public static function deserialize(
        CipherAlgo $cipher_algo,
        string     $serialized_payload,
        string     $meta_data = "",
    ): EncryptedPayload
    {
        if ($meta_data != "" && !$cipher_algo->isAllowedMetadata()) {
            throw new UnexpectedValueException(
                "for the cipher algo `{$cipher_algo->value}` metadata is no allowed"
            );
        }

        $iv_len = $cipher_algo->iv_length();
        if ($cipher_algo->isRequiredTag()) {
            $tag_len  = ord(substr($serialized_payload, 0, 1));
            $data_len = strlen($serialized_payload) - 1 - $iv_len - $tag_len;
            return new EncryptedPayload(
                encrypted_body: substr($serialized_payload, 1, $data_len),
                iv: substr($serialized_payload, 1 + $data_len, $iv_len),
                tag: substr($serialized_payload, 1 + $data_len + $iv_len),
                metadata: $meta_data,
            );
        } else {
            return new EncryptedPayload(
                encrypted_body: substr($serialized_payload, 0, -$iv_len),
                iv: substr($serialized_payload, -$iv_len),
                tag: null,
                metadata: $meta_data,
            );
        }
    }
}
