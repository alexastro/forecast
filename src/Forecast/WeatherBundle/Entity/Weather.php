<?php

namespace Forecast\WeatherBundle\Entity;

use Forecast\WeatherBundle\Model\Base;

/**
 * Weather
 */
class Weather extends Base
{
    /**
     * @var integer
     */
    protected $temp;

    /**
     * @var float
     */
    protected $windSpeed;

    /**
     * @var string
     */
    protected $windDirection;

    /**
     * @var float
     */
    protected $rainChance;

    /**
     * @var float
     */
    protected $humidity;

    /**
     * @var integer
     */
    protected $timestamp;

    /**
     * @var \Forecast\WeatherBundle\Entity\Location
     */
    protected $location;
    
    /**
     * @var integer
     */
    protected $locationId;

    /**
     * Set temp
     *
     * @param integer $temp
     * @return Weather
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
    
        return $this;
    }

    /**
     * Get temp
     *
     * @return integer 
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Set windSpeed
     *
     * @param float $windSpeed
     * @return Weather
     */
    public function setWindSpeed($windSpeed)
    {
        $this->windSpeed = $windSpeed;
    
        return $this;
    }

    /**
     * Get windSpeed
     *
     * @return float 
     */
    public function getWindSpeed()
    {
        return $this->windSpeed;
    }

    /**
     * Set windDirection
     *
     * @param string $windDirection
     * @return Weather
     */
    public function setWindDirection($windDirection)
    {
        $this->windDirection = $windDirection;
    
        return $this;
    }

    /**
     * Get windDirection
     *
     * @return string 
     */
    public function getWindDirection()
    {
        return $this->windDirection;
    }

    /**
     * Set rainChance
     *
     * @param float $rainChance
     * @return Weather
     */
    public function setRainChance($rainChance)
    {
        $this->rainChance = $rainChance;
    
        return $this;
    }

    /**
     * Get rainChance
     *
     * @return float 
     */
    public function getRainChance()
    {
        return $this->rainChance;
    }

    /**
     * Set humidity
     *
     * @param float $humidity
     * @return Weather
     */
    public function setHumidity($humidity)
    {
        $this->humidity = $humidity;
    
        return $this;
    }

    /**
     * Get humidity
     *
     * @return float 
     */
    public function getHumidity()
    {
        return $this->humidity;
    }

    /**
     * Set timestamp
     *
     * @param integer $timestamp
     * @return Weather
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    
        return $this;
    }

    /**
     * Get timestamp
     *
     * @return integer 
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set location
     *
     * @param \Forecast\WeatherBundle\Entity\Location $location
     * @return Weather
     */
    public function setLocation(\Forecast\WeatherBundle\Entity\Location $location)
    {
        $this->location = $location;
        $this->setLocationId($location->getId());
        
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
    
    /**
     * Set locationId
     *
     * @param integer $locationId
     * @return Weather
     */
    public function setLocationId($locationId)
    {
        $this->locationId = $locationId;
    
        return $this;
    }

    /**
     * Get locationId
     *
     * @return integer 
     */
    public function getLocationId()
    {
        return $this->locationId;
    }
}