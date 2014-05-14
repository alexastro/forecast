<?php


namespace Forecast\WeatherBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Forecast\WeatherBundle\Model\Base;

/**
 * Entity that persists the location information
 *
 */
class Location extends Base
{

    
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $lat;

    /**
     * @var string
     */
    protected $lng;

    /**
     * @var integer
     */
    protected $id;


    /**
     * Set name
     *
     * @param string $name
     * @return Location
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set lat
     *
     * @param string $lat
     * @return Location
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return string 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param string $lng
     * @return Location
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    
        return $this;
    }

    /**
     * Get lng
     *
     * @return string 
     */
    public function getLng()
    {
        return $this->lng;
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
     * @return ArrayCollection
     */
    public function getWeather($date) {
        
        
        
        return $result;        
    }
    
    public function __toString() {
        return $this->getName();
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $weathers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->weathers = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add weathers
     *
     * @param \Forecast\WeatherBundle\Entity\Weather $weathers
     * @return Location
     */
    public function addWeather(\Forecast\WeatherBundle\Entity\Weather $weathers)
    {
        $this->weathers[] = $weathers;
    
        return $this;
    }

    /**
     * Remove weathers
     *
     * @param \Forecast\WeatherBundle\Entity\Weather $weathers
     */
    public function removeWeather(\Forecast\WeatherBundle\Entity\Weather $weathers)
    {
        $this->weathers->removeElement($weathers);
    }

    /**
     * Get weathers
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWeathers()
    {
        return $this->weathers;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $notifications;


    /**
     * Add notifications
     *
     * @param \Forecast\WeatherBundle\Entity\Notification $notifications
     * @return Location
     */
    public function addNotification(\Forecast\WeatherBundle\Entity\Notification $notifications)
    {
        $this->notifications[] = $notifications;
    
        return $this;
    }

    /**
     * Remove notifications
     *
     * @param \Forecast\WeatherBundle\Entity\Notification $notifications
     */
    public function removeNotification(\Forecast\WeatherBundle\Entity\Notification $notifications)
    {
        $this->notifications->removeElement($notifications);
    }

    /**
     * Get notifications
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotifications()
    {
        return $this->notifications;
    }
}