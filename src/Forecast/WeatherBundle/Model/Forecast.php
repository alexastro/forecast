<?php

namespace Forecast\WeatherBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Forecast\WeatherBundle\Entity\Location;
use Forecast\WeatherBundle\Entity\Weather;

/**
 * Entity that persists the location information
 *
 */
class Forecast extends Base
{
    
    protected $location;
    protected $weathers;
    
    /**
     * @var mixed (int | null) 
     */
    protected $maxTempId = null;
    /**
     * @var mixed (int | null) 
     */
    protected $minTempId = null;
    /**
     * @var mixed (int | null) 
     */
    protected $strongWindId = null;

    /**
     * @var \DateTime
    */
    protected $date;

    public function __construct(Location $location, ArrayCollection $weathers, \DateTime $date) {
        
        $this->setLocation($location);

        $this->setDate($date);

        foreach ($weathers as $weather) {
            $this->addWeather($weather);
        }
    }

     /**
     * Set location
     *
     * @param Location $location
     * @return $this
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Add weather
     *
     * @param Weather $weather
     * @return $this
     */
    public function addWeather(Weather $weather)
    {
        if (is_null($this->maxTempId) || $this->weathers[$this->maxTempId]->getTemp()<$weather->getTemp()) {
            $this->maxTempId = count($this->weathers);
        }
        if (is_null($this->minTempId) || $this->weathers[$this->minTempId]->getTemp()>$weather->getTemp()) {
            $this->minTempId = count($this->weathers);
        }
        if (is_null($this->strongWindId) || $this->weathers[$this->strongWindId]->getWindSpeed()<$weather->getWindSpeed()) {
            $this->strongWindId = count($this->weathers);
        }

        $this->weathers[] = $weather;

        return $this;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return $this
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Remove weather
     *
     * @param Weather $weather
     */
    public function removeWeather(Weather $weather)
    {
        $this->weathers->removeElement($weather);
    }

    /**
     * Get weathers
     *
     * @return Collection 
     */
    public function getWeathers()
    {
        return $this->weathers;
    }
    
    /**
     * @return Weather
     */
    public function getMaxTempWeather() {
        
        if (!is_null($this->maxTempId)) {
            return $this->weathers[$this->maxTempId];
        }
        else {
            return null;
        }
    }
    
    /**
     * @return Weather 
     */
    public function getMinTempWeather(){
        
        if (!is_null($this->minTempId)) {
            return $this->weathers[$this->minTempId];
        }
        else {
            return null;
        }
    }
    
    /**
     * @return Weather 
     */
    public function getStrongWindWeather(){
        
        if (!is_null($this->strongWindId)) {
            return $this->weathers[$this->strongWindId];
        }
        else {
            return null;
        }
    }
    
}