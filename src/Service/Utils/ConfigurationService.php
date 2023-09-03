<?php

namespace App\Service\Utils;

use App\Entity\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class ConfigurationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Singleton to get configuration parameters from database
     * If no configuration parameters are found in database, create a new one
     * Else return the existing one
     *
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {   

        $configuration = $this->entityManager->getRepository(Configuration::class)->find(1);

        if (!$configuration) {
            $configuration = new Configuration();
            $this->entityManager->persist($configuration);
            $this->entityManager->flush();
            return $configuration;
        }

        return $configuration;
    }

}
