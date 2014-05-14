<?php

namespace Forecast\WeatherBundle\Services;

use Forecast\WeatherBundle\Entity\Notification;
use Forecast\WeatherBundle\Event\NotificationEvent;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class NotificationCommand extends ContainerAwareCommand {
    
    protected $locationClass = 'Forecast\WeatherBundle\Entity\Location';
    
    protected function configure()
    {
        $this
            ->setName('forecast:notify')
            ->setDescription('Send notifications to subscribers about weather extreme indicators')
            ->addArgument(
                'location',
                InputArgument::OPTIONAL,
                'Location Name. If set, process only notification for this location.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $em = $this->getContainer()->get('doctrine')->getManager();
        $location = $input->getArgument('location');
        if ($location) {
            $locations = $em->getRepository($this->locationClass)->findBy(array('name' => $location), array('name' => 'ASC'));
        }
        else {
            //get the list of all locations
            $locations = $em->getRepository($this->locationClass)->findBy(array(), array('name' => 'ASC'));
        }
        
        /* @var $dispatcher EventDispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
            
        foreach ($locations as $location) {
            // fire event
            $event = new NotificationEvent($location, new \DateTime());
            $dispatcher->dispatch(Notification::PROCESS_EVENT, $event);
        }
    }
}