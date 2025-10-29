<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Digital Signature Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for digital signature system including
    | QR code generation, verification, and expiration settings.
    |
    */

    'qr_code' => [

        /*
        |----------------------------------------------------------------------
        | Maximum QR Code Lifetime (Years)
        |----------------------------------------------------------------------
        |
        | Default maximum lifetime for QR codes in years. This serves as a cap
        | to prevent indefinitely valid QR codes.
        |
        | Note: Actual expiration will be the MINIMUM of this value or the
        | DigitalSignature's valid_until date.
        |
        */
        'max_lifetime_years' => env('QR_MAX_LIFETIME_YEARS', 5),

        /*
        |----------------------------------------------------------------------
        | Respect Digital Signature Expiry
        |----------------------------------------------------------------------
        |
        | When true, QR code expiration will never exceed the associated
        | DigitalSignature's valid_until date. This ensures QR codes expire
        | when the signing key expires.
        |
        | Recommended: true (for security and logical consistency)
        |
        */
        'respect_signature_expiry' => env('QR_RESPECT_SIGNATURE_EXPIRY', true),

        /*
        |----------------------------------------------------------------------
        | Minimum Validity Period (Days)
        |----------------------------------------------------------------------
        |
        | Minimum number of days a DigitalSignature must be valid before
        | allowing QR code generation. This prevents creating QR codes for
        | signatures that will expire very soon.
        |
        | Set to 0 to disable this check.
        |
        */
        'min_validity_days' => env('QR_MIN_VALIDITY_DAYS', 30),

        /*
        |----------------------------------------------------------------------
        | QR Code Size & Quality
        |----------------------------------------------------------------------
        |
        | Default dimensions and quality settings for generated QR codes.
        | With shorter URLs, you can reduce size while maintaining scannability.
        |
        */
        'default_size' => env('QR_DEFAULT_SIZE', 300),
        'default_margin' => env('QR_DEFAULT_MARGIN', 10),
        'default_format' => env('QR_DEFAULT_FORMAT', 'png'),

        /*
        |----------------------------------------------------------------------
        | Rate Limiting
        |----------------------------------------------------------------------
        |
        | Maximum verification attempts per hour for a single QR code.
        | This helps prevent brute force attacks and abuse.
        |
        */
        'max_attempts_per_hour' => env('QR_MAX_ATTEMPTS_PER_HOUR', 10),

        /*
        |----------------------------------------------------------------------
        | Cleanup Settings
        |----------------------------------------------------------------------
        |
        | Configuration for automatic cleanup of expired QR code mappings.
        |
        */
        'cleanup' => [
            // Days after expiration before permanent deletion
            'retention_days' => env('QR_CLEANUP_RETENTION_DAYS', 365),

            // Schedule for automatic cleanup (cron expression)
            // Default: Monthly on 1st day at 2:00 AM
            'schedule' => env('QR_CLEANUP_SCHEDULE', '0 2 1 * *'),
        ],

    ],

    'digital_signature' => [

        /*
        |----------------------------------------------------------------------
        | Default Key Settings
        |----------------------------------------------------------------------
        |
        | Default settings for RSA key pair generation.
        |
        */
        'default_key_length' => env('DIGITAL_SIGNATURE_KEY_LENGTH', 2048),
        'default_algorithm' => env('DIGITAL_SIGNATURE_ALGORITHM', 'RSA-SHA256'),

        /*
        |----------------------------------------------------------------------
        | Default Validity Period (Years)
        |----------------------------------------------------------------------
        |
        | Default validity period for newly created digital signatures.
        |
        */
        'default_validity_years' => env('DIGITAL_SIGNATURE_VALIDITY_YEARS', 1),

        /*
        |----------------------------------------------------------------------
        | Expiration Warning Threshold (Days)
        |----------------------------------------------------------------------
        |
        | Number of days before expiration to show warnings.
        |
        */
        'expiration_warning_days' => env('DIGITAL_SIGNATURE_EXPIRATION_WARNING_DAYS', 30),

    ],

    'verification' => [

        /*
        |----------------------------------------------------------------------
        | Allow Verification After Signature Expiry
        |----------------------------------------------------------------------
        |
        | If true, allows verification of documents signed with expired keys
        | (for archival purposes). The verification will show the signature
        | was valid at the time of signing.
        |
        */
        'allow_expired_verification' => env('VERIFICATION_ALLOW_EXPIRED', true),

        /*
        |----------------------------------------------------------------------
        | Grace Period After Key Revocation (Days)
        |----------------------------------------------------------------------
        |
        | Number of days after key revocation where verification is still
        | allowed with a warning. Set to 0 to disallow immediately.
        |
        */
        'revocation_grace_period_days' => env('VERIFICATION_REVOCATION_GRACE_DAYS', 0),

    ],

];
