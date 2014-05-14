<?php

namespace Forecast\WeatherBundle\Event;

use Doctrine\Common\Collections\Criteria;
use Forecast\WeatherBundle\Entity\Location;
use Forecast\WeatherBundle\Entity\Notification;
use Forecast\WeatherBundle\Model\Forecast;
use Swift_Message;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListener  extends ContainerAware
{

    protected $container;
    
    const TOO_HOT_TEMP = 30; // deg
    const TOO_COLD_TEMP = -10; //deg 
    const STRONG_WIND = 15; // meter per second
    
    protected $notifications;
    protected $notificationClass = 'Forecast\WeatherBundle\Entity\Notification';
    protected $weatherClass = 'Forecast\WeatherBundle\Entity\Weather';
    
    public function __construct($container)
    {
        $this->setContainer($container);
    }
    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
    /**
     * Listen to forecast.process_notification event
     */
    public function processNotifications(NotificationEvent $event){
        
        $em = $this->container->get('doctrine')->getManager();
        
        $location = $event->getLocation();
        $date = $event->getDate();
        $dayStart = $date->setTime(0, 0, 0)->format('U');
        $dayEnd = $date->setTime(23, 59, 59)->format('U');
    
        // find weather by location, date/time
        $criteria = new Criteria();
        $expr = Criteria::expr();
        $criteria->where($expr->eq('location', $location->getId()))
                ->andWhere($expr->gte('timestamp', $dayStart))
                ->andWhere($expr->lt('timestamp', $dayEnd));
        $weathers = $em->getRepository($this->weatherClass)->matching($criteria);
        
        $forecast = new Forecast($location, $weathers, $date);
        
        
        foreach ($this->getNotifications($location) as $notification) {
            
            $found = ($notification->getToHot() && $forecast->getMaxTempWeather() && $forecast->getMaxTempWeather()->getTemp() >= self::TOO_HOT_TEMP) ||
                    ($notification->getToCold() && $forecast->getMinTempWeather() && $forecast->getMinTempWeather()->getTemp() <= self::TOO_COLD_TEMP) ||
                    ($notification->getStrongWind() && $forecast->getStrongWindWeather() && $forecast->getStrongWindWeather()->getWindSpeed() <= self::STRONG_WIND);
            
            if ($found) {
                // send notification
                $this->send($forecast, $notification);
            }
        }
        
        return $event;
    }
     
    /**
     * @return array Array of Notification
     */
    public function getNotifications(Location $location){
        
        if (!$this->notifications) {
            // find notifications
            $em = $this->container->get('doctrine')->getManager();
            $this->notifications = $em->getRepository($this->notificationClass)->findBy(array('location' => $location->getId()));
        }
        
        return $this->notifications;
    }
    
    /***
     * Send email
     * @param Forecast $forecast
     * @param Notification $notification
     */
    public function send(Forecast $forecast, Notification $notification){
        
        $message = Swift_Message::newInstance()
            ->setSubject('Weather changes in '.$forecast->getLocation()->getName())
            ->setFrom('weather@forecast.com')
            ->setTo($notification->getEmail())
            ->setBody(
                $this->renderView(
                    'ForecastWeatherBundle:Email:notify.html.twig',
                    array('forecast'    => $forecast,
                          'notification'=> $notification)
                )
        );
        $this->container->get('mailer')->send($message);

        return $this;
    }
    
    public function renderView($view, $parameters){
        
        return $this->container->get('templating')->render($view, $parameters);
    }
}

