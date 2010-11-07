<?php
/*
 * Copyright 2010 Francisco Alvarez Alonso <sormes@gmail.com>
 *
 * This file is part of sfMondongoPlugin.
 *
 * sfMondongoPlugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * sfMondongoPlugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with sfMondongoPlugin. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * sfMondongoRoute.
 *
 * @package sfMondongoPlugin
 * @author  Francisco Alvarez Alonso <sormes@gmail.com>
 */
class sfMondongoRoute extends sfObjectRoute
{


  protected function getObjectForParameters($parameters)
  {
    $this->fixOptions();

    if (!isset($this->options['method']))
    {
      $this->options['method'] = isset($this->options['method_for_find']) ? $this->options['method_for_find'] : 'findOne';

      $className = $this->options['model'];

      $documentClass = $this->options['object_model'];

      $dataMap = $documentClass::getDataMap();

      $options = array('query' => array());

      $variables = $this->getRealVariables();

      if (!count($variables))
      {
        return false;
      }

      foreach ($variables as $variable)
      {
       
        if(isset($dataMap['fields'][$variable]))
        {
        
         $options['query'][$variable] = $parameters[$variable]; 
          
        } 
  
      }
      
    }

    $mondongo = sfContext::getInstance()->get('mondongo');

    $method = $this->options['method'];
    
    return $mondongo->getRepository($documentClass)->$method($options);
    
  }


  protected function fixOptions()
  {
    if (!isset($this->options['object_model']))
    {
      $this->options['object_model'] = $this->options['model'];
      $this->options['model'] = $this->options['model'].'Repository';
    }
  }



   protected function doConvertObjectToArray($object)
  {
    $this->fixOptions();

    if (isset($this->options['convert']) || method_exists($object, 'toParams'))
    {
      return parent::doConvertObjectToArray($object);
    }

    $className = $this->options['object_model'];

    $dataCamelCaseMap = $className::getDataCamelCaseMap();

    $parameters = array();
    
    foreach ($this->getRealVariables() as $variable)
    {

      if(isset($dataCamelCaseMap[$variable]) && method_exists($className,'get'.$dataCamelCaseMap[$variable]))
      {
        $method = 'get'.$dataCamelCaseMap[$variable];
      }
      else
      {
        $method = 'get'.sfInflector::camelize($variable);
      }
      
      $parameters[$variable] = $object->$method();
    }

    return $parameters;
  }
  
}