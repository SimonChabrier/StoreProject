<?php

namespace App\Command;

use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RemoveExpiredCartsCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    protected static $defaultName = 'app:remove-expired-carts';

    /**
     * RemoveExpiredCartsCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Removes carts that have been inactive for a defined period')
            ->addArgument(
                'days',
                InputArgument::OPTIONAL,
                'The number of days a cart can remain inactive',
                2
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $days = $input->getArgument('days');

        if ($days <= 0) {
            $io->error('The number of days should be greater than 0.');

            return Command::FAILURE;
        }

        // Soustrait le nombre de jours à partir de la date actuelle.
        $limitDate = new \DateTime("- $days days");
        $expiredCartsCount = 0;

        while ($carts = $this->orderRepository->findCartsNotModifiedSince($limitDate)) {
            foreach ($carts as $cart) {
                // on supprime les items en cascade
                $this->entityManager->remove($cart);
            }

            $this->entityManager->flush(); // On exécute 
            $this->entityManager->clear(); // On détache les objets pour éviter les fuites mémoire

            $expiredCartsCount += count($carts); // On compte le nombre de paniers supprimés
        }

        if ($expiredCartsCount) {
            $io->success("$expiredCartsCount panier(s) ont été suppimé(s).");
        } else {
            $io->info('Pas de panier expiré trouvé.');
        }

        return Command::SUCCESS;
    }
}
