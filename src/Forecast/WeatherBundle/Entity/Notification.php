<?php

namespace Forecast\WeatherBundle\Entity;

use Forecast\WeatherBundle\Model\Base;

/**
 * Notification
 */
class Notification extends Base
{   
    
    const PROCESS_EVENT = 'forecast.process_notification';
    
    /**
     * @var string
     */
    protected $email;

    /**
     * @var integer
     */
    protected $to_hot;

    /**
     * @var integer
     */
    protected $to_cold;

    /**
     * @var integer
     */
    protected $strong_wind;

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var \Forecast\WeatherBundle\Entity\Location
     */
    protected $location;
    
    /**
     * @var integer
     */
    protected $locationId;

    /**
     * Set email
     *
     * @param string $email
     * @return Notification
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set to_hot
     *
     * @param integer $toHot
     * @return Notification
     */
    public function setToHot($toHot)
    {
        $this->to_hot = $toHot;
    
        return $this;
    }

    /**
     * Get to_hot
     *
     * @return integer 
     */
    public function getToHot()
    {
        return $this->to_hot;
    }

    /**
     * Set to_cold
     *
     * @param integer $toCold
     * @return Notification
     */
    public function setToCold($toCold)
    {
        $this->to_cold = $toCold;
    
        return $this;
    }

    /**
     * Get to_cold
     *
     * @return integer 
     */
    public function getToCold()
    {
        return $this->to_cold;
    }

    /**
     * Set strong_wind
     *
     * @param integer $strongWind
     * @return Notification
     */
    public function setStrongWind($strongWind)
    {
        $this->strong_wind = $strongWind;
    
        return $this;
    }

    /**
     * Get strong_wind
     *
     * @return integer 
     */
    public function getStrongWind()
    {
        return $this->strong_wind;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set location
     *
     * @param \Forecast\WeatherBundle\Entity\Location $location
     * @return Notification
     */
    public function setLocation(\Forecast\WeatherBundle\Entity\Location $location = null)
    {
        $this->location = $location;
        $this->setLocationId($location->getId());
        
        return $this;
    }
    
    /**
     * Set locationId
     *
     * @param integer $locationId
     * @return Weather
     */
    public function setLocationId($locationId){
        $this->locationId = $locationId;
        
        return $this;
    }
    
    /**
     * Get location
     *
     * @return \Forecast\WeatherBundle\Entity\Location 
     */
    public function getLocation()
    {
        return $this->location;
    }
    
    public function getLocationId()
    {
        return $this->locationId;
    }
}