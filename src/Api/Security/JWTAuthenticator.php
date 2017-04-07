<?php

namespace EQT\Api\Security;;

use EQT\Api\Utility;
use Firebase\JWT\JWT;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTAuthenticator extends AbstractGuardAuthenticator
{
    private $jwt_config;

    public function __construct(Array $jwt_config) {
        $this->jwt_config = $jwt_config;
    }

    public function getCredentials(Request $request) {
        if (!$token = $request->headers->get($this->jwt_config['options']['header_name'])) {
            return;
        }

        $prefix = $this->jwt_config['options']['token_prefix'];

        if (strpos($token, $prefix) !== 0){
            return;
        }

        return JWT::decode(
            trim(substr($token, strlen($prefix))),
            $this->jwt_config['secret_key'],
            $this->jwt_config['algorithm']
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider) {
        return $userProvider->loadUserByUsername($credentials->username);
    }

    public function checkCredentials($credentials, UserInterface $user) {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // maybe we do something here with the active token
        return;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = array(
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        );

        return new JsonResponse($data, 403);
    }

    /**
     * Called when authentication is needed, but it's not sent
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return Utility::JsonResponse([ 'message' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}