<?php

namespace Forecast\WeatherBundle\Controller;
//use Forecast\WeatherBundle\Entity\Notification;


use Doctrine\Common\Collections\Criteria;
use Forecast\WeatherBundle\Entity\Location;
use Forecast\WeatherBundle\Entity\Notification;
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
use Symfony\Component\Finder\Expression\Expression;

/**
 * Controller that provides Restful functions to manage weather notifications.
 * 
 * @NamePrefix("weather_notification_")
 
 */
class NotificationController extends Controller
{   
    protected $notificationClass = 'Forecast\WeatherBundle\Entity\Notification';
    protected $locationClass = 'Forecast\WeatherBundle\Entity\Location';
    protected $weatherClass  = 'Forecast\WeatherBundle\Entity\Weather';
    
    /**
     * Returns the notifications list.
     * *  
     * @param integer $locationId
     * @return FOSView
     * @ApiDoc()
     */
    public function getNotificationsAction($locationId){
        
        $em = $this->getDoctrine()->getManager();
        $notifications = $em->getRepository($this->notificationClass)->findBy(array('location' => $locationId));
        
        $notificationRecords = array();
        foreach ($notifications as $notification) {
            $record = $notification->toArray(array(), array('location'));
            $record['locationId'] = $notification->getLocation()->getId();
            $notificationRecords[] = $record;
        }
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData($notificationRecords);
        
        return $view;
        
    }
    
    /**
     * Save new location record
     * @param ParamFetcher $paramFetcher ParamFetcher
     * @RequestParam(name="id", requirements="\d+", nullable=true, description="Notification ID.")
     * @RequestParam(name="locationId", requirements="\d+", default=null, description="Location ID.")
     * @RequestParam(name="email", nullable=false, description="Email.")
     * @RequestParam(name="to_hot", default="0", description="Latitude.")
     * @RequestParam(name="to_cold", default="0", description="Longitude.")
     * @RequestParam(name="strong_wind", default="0", description="Longitude.")
     * @return FOSView
     * @ApiDoc()
     */
    public function postNotificationAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        $id = $paramFetcher->get('id');
        
        $location = $em->getRepository($this->locationClass)->find($paramFetcher->get('locationId'));
        
        if (is_null($location)) {
            throw \Exception('The location does not exist. [ID='.$paramFetcher->get('locationId').']');
        }
        
        // try to find existing record
        if ($id > 0) {
            $record = $em->getRepository($this->notificationClass)->find($id);
        }
        
        if (empty($record)) {
            // otherwise create new one
            $record = new Notification();
        }    
        
        // save the notification
        $record->setLocation($location);
        $record->setEmail($paramFetcher->get('email'));
        $record->setToHot($paramFetcher->get('to_hot') == '1' || $paramFetcher->get('to_hot')=='true');
        $record->setToCold($paramFetcher->get('to_cold')== '1' || $paramFetcher->get('to_cold')=='true');
        $record->setStrongWind($paramFetcher->get('strong_wind') == '1' || $paramFetcher->get('strong_wind')=='true');
        
        $em->persist($record);
        $em->flush($record);
        
        $view = FOSView::create();
        $view->setStatusCode(200)->setData($record->toArray(array(), array('location')));
        
        
        /* @var $dispatcher EventDispatcher */
        $dispatcher = $this->container->get('event_dispatcher');
        // fire event
        $event = new NotificationEvent($location, new \DateTime());
        $dispatcher->dispatch(Notification::PROCESS_EVENT, $event);
        
        return $view;
    }
    
    /**
     * Delete location record
     * @QueryParam(name="id", requirements="\d+", nullable=false, description="Notification ID.")
     * @return FOSView
     * @ApiDoc()
     */
    public function deleteNotificationAction(ParamFetcher $paramFetcher){
        
        $em = $this->getDoctrine()->getManager();
        $notification = $em->getRepository($this->notificationClass)->find($paramFetcher->get('id'));
        
        if (!empty($notification)) {
            $em->remove($notification);
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

