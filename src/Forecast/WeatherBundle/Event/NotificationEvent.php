<?php
namespace Forecast\WeatherBundle\Event;

use Forecast\WeatherBundle\Entity\Location;
use Symfony\Component\EventDispatcher\Event as BaseEvent;



class NotificationEvent extends BaseEvent
{
    protected $date;
    protected $location;
    
    
    
    public function __construct(Location $location, \DateTime $date) {
        $this->location = $location;
        $this->date = $date;
    }
    
    /**
     * @return Location
     */
    public function getLocation() {
        return $this->location;
    }
    /**
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }
    
    
}