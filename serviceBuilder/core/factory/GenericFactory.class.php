<?php
namespace be\edge\serviceBuilder\core\factory;

class GenericFactory {
    
    private $object;
    private $objectProperties = array();
    
    public function __construct($object){
        $this->object = $object;
    }
    
    public function setObject($object){
        $this->object = $object;
        $this->getObjectProperties();
    }
    
    public function toArray($dataArray, $propertySourceQueries = NULL){
        $objects = array();
        
        if(is_array($dataArray)){
            foreach($dataArray as $data){
                $objects[] = $this->toObject($data, $propertySourceQueries);
            }
        }else{
            //if object has been passed as an argument instead of an array
            $objects[] = $this->toObject($dataArray, $propertySourceQueries);
        }
        
        return $objects;
    }
    
    public function toObject($data, $propertySourceQueries = NULL){
        $object = clone $this->object;

        if(empty($this->objectProperties))
        $this->getObjectProperties();
        
        $properties = array_keys($this->objectProperties);
        
        //fill each property
        foreach($properties as $property){
            $object->$property = $this->fillProperty($property, $data, $object);
            
            //fill property by sourceQuery if provided
            if(!empty($propertySourceQueries) && is_array($propertySourceQueries) && array_key_exists($property, $propertySourceQueries))
            $object->$property = $this->queryFillProperty($property, $data, $object, $propertySourceQueries[$property]);      
        }
        return $object;
    }
    
    private function fillProperty($property, $data, &$object){
        //try to fill property according to type if $value 
        
		if(is_array($data) && isset($data[$property])) $dataProperty = $data[$property];
        elseif(isset($data->$property)) $dataProperty = $data->$property;
        
        if(isset($dataProperty)){
           $value = $this->fillPropertyByType($dataProperty, $this->objectProperties[$property]);
           $object->$property = $value;
        }
        
        //after automaticly filling property, give chance to custom fill it
        //$data is entire row from resultset. For when the property is a composed property
        $value = $this->customFillProperty($property, $data, $object);
        
        return $value;
    }
    
    private function fillPropertyByType($value, $type){
        switch($type){
            case 'date':
                return $this->createDate($value);
                break;
            case 'array':
                return $this->createArray($value);
                break;
            case 'boolean':
                return $this->createBoolean($value);
                break;
            default: return (is_string($value) || is_numeric($value)) ? $value : null;
        }
    }
    
    /*override this function for custom property handling*/
    protected function customFillProperty($property, $data, &$object){
        return $object->$property;
    }
    
    /*override this function for filling properties from extra resultset*/
    protected function queryFillProperty($property, $data, &$object, $resultSet){
        return $object->$property;
    }
    
    private function getObjectProperties(){
        if(method_exists($this->object, 'getMetaData'))
            $this->objectProperties = $this->object->getMetaData()->propertyTypes;
        else
            $this->objectProperties = get_object_vars($this->object);
    }
    
    private function createDate($value){
        return \date($value);
    }
    
    private function createArray($value){
        return array($value);
    }
    
    private function createBoolean($value){
        if(empty($value) || $value == 0 || $value == false ||  (is_string($value) && (strtolower($value) == 'no' || strtolower($value) == 'false')))
        return FALSE;
        
        return TRUE;
    } 
}
?>
