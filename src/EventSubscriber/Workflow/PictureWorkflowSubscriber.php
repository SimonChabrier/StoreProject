<?php 

namespace App\EventSubscriber\Workflow;

use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PictureWorkflowSubscriber implements EventSubscriberInterface
{   

// TODO class en attente, permet de faire des actions lors de la transition d'un workflow
// TODO à voir si on en a besoin pour le moment on ne fait rien

    /**
     * Pour agir sur les transitions du workflow 'picture_publishing'
     * @return array
     */
    public static function getSubscribedEvents() : array
    {
        return [
            'workflow.picture_publishing.guard.process' => 'onGuardProcess', // sur la transition 'process' du workflow 'picture_publishing' on exécute la méthode onGuardProcess
            'workflow.picture_publishing.guard.done' => 'onGuardDone', // sur la transition 'done' du workflow 'picture_publishing' on exécute la méthode onGuardDone
        ];
    }

    public function onGuardProcess(GuardEvent $event)
    {
        // Code à exécuter lorsque la transition 'process' est guardée sur une Entité Picture

    }

    public function onGuardDone(GuardEvent $event)
    {
        // Code à exécuter lorsque la transition 'done' est guardée sur une Entité Picture
    }
}
