<?php
namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{

    public function __construct(UserRepository $userRepository){
        $this->userRepository = $userRepository;
    }
    /**
     * Refreshes the user.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the user is not supported
     * @throws UserNotFoundException    if the user is not found
     */
    public function refreshUser(UserInterface $user){
        return $user;
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @return bool
     */
    public function supportsClass(string $class){
        return true;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Load user by username or email
        $user = $this->userRepository->loadUserByUsernameOrEmail($identifier);

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }
}