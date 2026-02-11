<?php

namespace App\Services\KlingApi;

use Firebase\JWT\JWT;

class KlingAuthService
{
    public function generateToken(): string
    {
        $accessKey = config('services.kling.access_key');
        $secretKey = config('services.kling.secret_key');

        $now = time();
        $payload = [
            'iss' => $accessKey,
            'exp' => $now + 1800,
            'nbf' => $now - 5,
            'iat' => $now,
        ];

        return JWT::encode($payload, $secretKey, 'HS256', null, ['typ' => 'JWT']);
    }
}
