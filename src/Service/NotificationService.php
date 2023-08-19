<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NotificationService extends AbstractController
{

    /**
     * Add a custom flash message
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    public function addCustomFlash(string $type, string $message): void
    {   
        $type = $type ?? 'success';
        $message = $message ?? 'Opération effectuée avec succès';

        $this->addFlash($type, $message);

    }

}
