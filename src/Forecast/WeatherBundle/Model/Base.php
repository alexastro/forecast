<?php


namespace Forecast\WeatherBundle\Model;

/**
 * Entity that persists the location information
 *
 */
abstract class Base
{
    
    public function toArray($keys  = array(), $excludeKeys = array()){
        
        $reflect = new \ReflectionClass($this);
        $properties   = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        
        $result = array();
      
        foreach ($properties as &$prop) {
            
            if ((empty($excludeKeys) || !in_array($prop->getName(), $excludeKeys)) && (empty($keys) || in_array($prop->getName(), $keys))) {
                
                $prop->setAccessible(true);
                $value = $prop->getValue($this);
                                
                if (is_object($value) && method_exists($value, 'toArray')) {
                    $result[$prop->getName()] = $value->toArray(); 
                }
                else {
                    $result[$prop->getName()] = $value; 
                }    
            }
        }
        return $result;
    }
    
}