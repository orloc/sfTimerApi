<?php

namespace EQT\Api\Security\Core;

use EQT\Api\Entity\User;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use \Firebase\JWT\JWT;

class JWTEncoder
{

    /**
     * Secret key for tokens encode and decode
     *
     * @var string
     */
    private $secretKey;

    /**
     * Life time tokens
     *
     * @var int
     */
    private $lifeTime;

    /**
     * Allowed algorithms array
     *
     * @link https://github.com/firebase/php-jwt#200--2015-04-01
     * @link http://jwt.io
     *
     * @var string
     */
    private $allowed_algs;

    public function __construct($secretKey, $lifeTime, $allowed_algs)
    {
        $this->secretKey = $secretKey;
        $this->lifeTime = $lifeTime;
        $this->allowed_algs = $allowed_algs;
    }

    /**
     * Encoded data
     *
     * @param mixed $data
     *
     * @return string
     */
    public function encode(User $user)
    {
        $data = [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'profile_name' => $user->getProfileName(),
            'role' => $user->getRoles()
        ];
        $data['exp'] = time() + $this->lifeTime;

        return JWT::encode($data, $this->secretKey);
    }

    /**
     * Token for decoding
     *
     * @param string $token
     * @return array
     *
     * @throws AccessDeniedException
     */
    public function decode($token) {
        return (Array)JWT::decode(
            $token,
            $this->secretKey,
            $this->allowed_algs
        );
    }
}

