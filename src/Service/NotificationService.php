<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class NotificationService extends AbstractController
{

    public function addCustomFlash(): void
    {   
        $type = 'success';
        $message = 'Flash message de test';

        $this->addFlash($type, $message);
    }
}
