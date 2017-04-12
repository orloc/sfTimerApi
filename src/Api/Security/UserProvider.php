<?php

namespace EQT\Api\Security;

use Doctrine\DBAL\Connection;
use EQT\Api\Entity\User;
use EQT\Api\Utility;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface {
    
    private $conn;
    
    private $entity;
    
    public function __construct(Connection $conn, UserInterface $user) {
        $this->conn = $conn;
        $this->entity = $user;
    }

    public function loadUserByUsername($username)
    {
        $stmt = $this->conn->executeQuery('SELECT * FROM users WHERE username = ?', array(strtolower($username)));

        if (!$user = $stmt->fetch()) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        
        return Utility::mapRequest($user, $this->entity());
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());

    }

    public function supportsClass($class)
    {
        return 'EQT\Api\Entity\User';
    }
}