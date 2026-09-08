<?php

namespace App\Services;

/**
 * Verifies PayMongo webhook signatures before any database write.
 *
 * PayMongo signs webhooks in the `Paymongo-Signature` header as
 * `t=<timestamp>,te=<test signature>,li=<live signature>` where each
 * signature is `HMAC-SHA256("<timestamp>.<raw body>", webhook_secret)`.
 */
class PayMongoSignatureVerifier
{
    public function verify(string $rawBody, ?string $header, ?string $secret): bool
    {
        if ($secret === null || $secret === '' || $header === null || $header === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $segment) {
            $kv = explode('=', trim($segment), 2);
            if (count($kv) === 2) {
                $parts[$kv[0]] = $kv[1];
            }
        }

        if (! isset($parts['t'])) {
            return false;
        }

        $signed = $parts['t'] . '.' . $rawBody;
        foreach (['te', 'li'] as $key) {
            if (isset($parts[$key]) && hash_equals(hash_hmac('sha256', $signed, $secret), $parts[$key])) {
                return true;
            }
        }

        return false;
    }
}
