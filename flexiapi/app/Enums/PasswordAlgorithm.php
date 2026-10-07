<?php

namespace App\Enums;

enum PasswordAlgorithm: string
{
    case MD5 = 'MD5';
    case SHA256 = 'SHA-256';

    public const DEFAULT = self::SHA256;

    public function hashFunction(): string
    {
        return match ($this) {
            self::SHA256 => 'sha256',
            self::MD5 => 'md5',
        };
    }

    public static function fromHashFunction(string $hash): self
    {
        foreach (self::cases() as $case) {
            if ($case->hashFunction() === $hash) {
                return $case;
            }
        }

        throw new \ValueError("No PasswordAlgorithm found for hash function '$hash'");
    }
}
