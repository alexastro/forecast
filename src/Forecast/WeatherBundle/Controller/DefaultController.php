<?php

namespace Forecast\WeatherBundle\Controller;

use Doctrine\Common\Collections\Criteria;
use Forecast\WeatherBundle\Entity\Location;
use Forecast\WeatherBundle\Entity\Notification;
use Forecast\WeatherBundle\Entity\Weather;
use Forecast\WeatherBundle\Event\NotificationEvent;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Prefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View as FOSView;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Controller that provides Restfuls functions to display weather forecast.
 * 
 * @NamePrefix("weather_forecast_")
 
 */
class DefaultController extends Controller
{
    protected $locationClass = 'Forecast\WeatherBundle\Entity\Location';
    protected $weatherClass  = 'Forecast\WeatherBundle\Entity\Weather';
    
    /**
     * Returns the locations.
     * *  
     * @return FOSView
     * @ApiDoc()
     */
    public function getLocationsAction(){
        
        $em = $this->getDoctrine()->getManager();
        $locations = $em->getRepository($this->locationClass)->findBy(array(), array('name' => 'ASC'));
        
        $locationRecords = array();
        foreach ($locations as $location) {
            $locationRecords[] = $location->toArray();
        }
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData($locationRecords);
        
        return $view;
        
    }
    
    /**
     * Returns the weather records for location by date.
     * Using param_fetcher_listener: force
     * 
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @QueryParam(name="locationId", requirements="\d+", nullable=false, description="Location ID.")
     * @QueryParam(name="date", default=null, description="Date.")
     * *  
     * @return FOSView
     * @ApiDoc()
     */
    public function getAction(ParamFetcher $paramFetcher) {
        
        // parse filter
        $locationId = $paramFetcher->get('locationId');
        
        $dateStr = $paramFetcher->get('date');
     
        if (empty($dateStr)) {
            $dateStr = 'now';
        }
        
        $date = new \DateTime($dateStr);
        $dayStart = $date->setTime(0, 0, 0)->format('U');
        $dayEnd = $date->setTime(23, 59, 59)->format('U');
    
        // find location
        $em = $this->getDoctrine()->getManager();
        $location = $em->getRepository($this->locationClass)->find($locationId);
        
        // find weather by location, date/time
        $criteria = new Criteria();
        $expr = Criteria::expr();
        $criteria->where($expr->eq('location', $locationId))
                ->andWhere($expr->gte('timestamp', $dayStart))
                ->andWhere($expr->lt('timestamp', $dayEnd));
        $records = $em->getRepository($this->weatherClass)->matching($criteria);
        
        $weatherRecords = array();
        foreach ($records as $record) {
            $weatherRecords[] = $record->toArray(array(), array('location'));
        }
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData(array('location' => !is_null($location) ? $location->toArray() : array(), 'weather' => $weatherRecords));
        
        return $view;
    }
   
    /**
     * Save new weather record
     * @param ParamFetcher $paramFetcher ParamFetcher
     * @RequestParam(name="locationId", requirements="\d+", nullable=false, description="Location ID.")
     * @RequestParam(name="timestamp", nullable=false, description="Timestamp.")
     * @RequestParam(name="temp", default=null, description="Temperatuer.")
     * @RequestParam(name="windSpeed", default=null, description="Wind Speed.")
     * @RequestParam(name="windDirection", default=null, description="Wind Direction.")
     * @RequestParam(name="rainChance", default=null, description="Rain Chance.")
     * @RequestParam(name="humidity", default=null, description="Humidity.")
     * @RequestParam(name="oldTimestamp", nullable=true, default=null, description="Previous Timestamp.")
     * @return FOSView
     * @ApiDoc()
     */
    public function postWeatherAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        
        $location = $em->getRepository($this->locationClass)->find($paramFetcher->get('locationId'));
     
        if (is_null($location)) {
            throw new \Exception('The location does not exist. [ID='.$paramFetcher->get('locationId').']');
        }
        
        // save the weather record
        if ($paramFetcher->get('oldTimestamp')) {
            $oldTimestamp = $paramFetcher->get('oldTimestamp');
        }
        else {
            $oldTimestamp = $paramFetcher->get('timestamp');
        }
        // try to find existing record
        $record = $em->getRepository($this->weatherClass)->find(array('location' => $paramFetcher->get('locationId'), 'timestamp' => $oldTimestamp));
        
        if (empty($record)) {
            // otherwise create new one
            $record = new Weather();
        }    
        $record->setLocation($location);
        $record->setTimestamp($paramFetcher->get('timestamp'));
        $record->setTemp($paramFetcher->get('temp'));
        $record->setWindSpeed($paramFetcher->get('windSpeed'));
        $record->setWindDirection($paramFetcher->get('windDirection'));
        $record->setRainChance($paramFetcher->get('rainChance'));
        $record->setHumidity($paramFetcher->get('humidity'));
        
        $em->persist($record);
        $em->flush($record);
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData($record->toArray(array(), array('location')));
        
        /* @var $dispatcher EventDispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        // fire event
        $event = new NotificationEvent($location, \DateTime::createFromFormat('U', $paramFetcher->get('timestamp')));
        $dispatcher->dispatch(Notification::PROCESS_EVENT, $event);
        
        return $view;
    }
    
    /**
     * Save new location record
     * @param ParamFetcher $paramFetcher ParamFetcher
     * @RequestParam(name="id", requirements="\d+", nullable=true, description="Location ID.")
     * @RequestParam(name="name", nullable=false, description="Name.")
     * @RequestParam(name="lat", default=null, description="Latitude.")
     * @RequestParam(name="lng", default=null, description="Longitude.")
     * @return FOSView
     * @ApiDoc()
     */
    public function postLocationAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        $id = $paramFetcher->get('id');
        
        if ($id > 0) {
            $location = $em->getRepository($this->locationClass)->find($id);
        }
        if (empty($location)) {
            $location = new Location();
        }
      
        //check location name uniqueness
        $qb = $em->createQueryBuilder();
        
        $qb->select('l')
            ->from($this->locationClass, 'l')
            ->where($qb->expr()->eq('l.name', '?0'));
        
        if ($id > 0 ) {
            $qb->andWhere($qb->expr()->neq('l.id', '?1'))
               ->setParameter('1', $id);
        }
        
        $found = $qb->setParameter('0', $paramFetcher->get('name'))
                ->getQuery()->getResult();        
        
        if ($found) {
            throw new \Exception('The location ' .$paramFetcher->get('name'). ' already exists.');
        }
        
        // save the location
        $location->setName($paramFetcher->get('name'));
        $location->setLat($paramFetcher->get('lat'));
        $location->setLng($paramFetcher->get('lng'));
        
        $em->persist($location);
        $em->flush($location);
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData($location->toArray());
        
        return $view;
    }
    
    /**
     * Delete location record
     * @QueryParam(name="id", requirements="\d+", description="Location ID.")
     * @return FOSView
     * @ApiDoc()
     */
    public function deleteLocationAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        $location = $em->getRepository($this->locationClass)->find($paramFetcher->get('id'));
        
        if (!empty($location)) {
            $locations = $em->getRepository($this->locationClass)->findAll();
            if (count($locations)==1){
                throw new \Exception('There must be at least one location.');
            }

            $em->remove($location);
            $em->flush();
        }
        else {
            throw new \Exception('Nothing to delete.');
        }
        $view = FOSView::create();
        $view->setStatusCode(200)->setData(array('success' => 'true'));
        
        return $view;
    }
    
    /**
     * Delete weather record
     * @QueryParam(name="locationId", requirements="\d+", nullable=false, description="Location ID.")
     * @QueryParam(name="timestamp", requirements="\d+", nullable=false, description="Location ID.")
     * @return FOSView
     * @ApiDoc()
     */
    public function deleteWeatherAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        $record = $em->getRepository($this->weatherClass)->find(array('location' => $paramFetcher->get('locationId'), 'timestamp' => $paramFetcher->get('timestamp')));
        if (!empty($record)) {
            $em->remove($record);
            $em->flush();
        }
        else {
            throw new \Exception('Nothing to delete.');
        }
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData(array('success' => 'true'));
        
        return $view;
    }
    
}

