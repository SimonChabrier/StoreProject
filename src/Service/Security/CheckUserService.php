<?php

namespace App\Service\Security;

use Symfony\Component\Security\Core\Security;

class CheckUserService
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @return object|null
     * Vérifie que l'utilisateur est bien connecté et renvoie l'utilisateur
     * sinon renvoie null (ou effectue une redirection)
     */
    public function getUserIfAuthenticatedFully(): ?object
    {
        $user = $this->security->getUser();

        if (!$user) {
            return null;
        }

        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $user;
        }

        return null;
    }
}
