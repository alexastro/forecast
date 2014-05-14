<?php

namespace Forecast\WeatherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class FrontController extends Controller
{   
    protected $locationClass = 'Forecast\WeatherBundle\Entity\Location';
    
    public function indexAction()
    {   
        // fetch locations list
        $em = $this->getDoctrine()->getManager();
        $locations = $em->getRepository($this->locationClass)->findBy(array(), array('name' => 'ASC'));
        
        $locationRecords = array();
        foreach ($locations as $location) {
            $locationRecords[] = $location->toArray();
        }
        
        return $this->render('ForecastWeatherBundle:Front:index.html.twig', array('locations' => $locationRecords));
    }
    
}
