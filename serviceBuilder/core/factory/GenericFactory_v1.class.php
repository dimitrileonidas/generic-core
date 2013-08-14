<?php
    namespace core\factory;
    class GenericFactory {

        private $object;
        private $dao;

        protected function init($object){
            $this->object = $object;
        }

        protected function toArray($result, $object = NULL, $propertySourceQueries = NULL){
            if(empty($object)) $object = $this->object;
            return $this->_toArray($result, $object, $propertySourceQueries);
        }

        private function _toArray(&$_result, &$_object, &$_propertySourceQueries = NULL){
            $list = array();
            foreach ($_result as $record) {
                $list[] = $this->toObject($record, clone $_object, $_propertySourceQueries);
            }
            
            return $list;
        }

        protected function toObject($result, $object = NULL, $propertySourceQueries = NULL){
            
            if(empty($object)) $object = $this->object;
            return $this->_toObject($result, $object, $propertySourceQueries);
        }


        private function _toObject(&$_result, &$_object, &$_propertySourceQueries = NULL){
            if(!empty($_result)){
                foreach($_result as $key => $value){
                    if(!property_exists($_object, $key)){
                        $this->handleObjectPropertyException($_object, $key);
                        continue;
                    }
                    $type = $this->getPropertyType($_object, $key);
                    $this->inject($_object, $key, $value, $type);
                }
                if(!empty($_propertySourceQueries)) {
                    foreach($_propertySourceQueries as $key => $query){
                        if(!property_exists($_object, $key))  continue; //throw new \Exception("PropertySourceQuery property mismatch: $key");
                        $type = $this->getPropertyType($_object, $key);
                        $this->injectComplexFromQuery($_object, $key, $query, $type);
                    }
                }
                foreach($_object as $key => $value){
                    if(!array_key_exists($key, $_result) && (empty($_propertySourceQueries) || !property_exists($_propertySourceQueries, $key))){
                        $type = $this->getPropertyType($_object, $key);
                        $this->injectComplex($_object, $key, $_result, $type);
                    }
                }
            }
            return $_object;
        }
        
        //override to hide properties from result
        protected function handleObjectPropertyException($object, $key){
            throw new \Exception('Result property mismatch: '.$key);
        }
        
        protected function getPropertyType(&$object, &$key){
            if(property_exists($object, 'meta')) return $object->getPropertyType($key);
        }

        protected function inject($object, $key, $value, $type){
            switch($type){
                case 'date':
                    $this->createDate($object, $key, $value);
                    break;
                case 'int': case 'string': case 'dec':
                    $this->fillProperty($object, $key, $value);
                    break;
                case 'array':
                    $this->createArray($object, $key, $value);
                    break;
                case 'boolean':
                    $this->createBoolean($object, $key, $value);
                    break;
                default:
                    $this->createObject($object, $key, $value, $type);
                    break;
            }
        }
        
        protected function injectComplexFromQuery($object, $key, $query, $type){
            throw new \Exception("Override injectComplexFromQuery: $key , $type");
        }
        
        protected function injectComplex($object,$key, $result, $type){
            
            throw new \Exception("Override injectComplex: $key , $type");
        }
        
        protected function createDate($object, $key, $value){
            $object->$key = \date($value);
        }

        protected function createArray($object, $key, $value){
            $object->$key = array($value);
        }
        
        protected function createBoolean($object, $key, $value){
            if(empty($value) || $value == 0 || $value == false || 
               (is_string($value) && (strtolower($value) == 'no' || strtolower($value) == 'false'))) return FALSE;
            return TRUE;
        }

        protected function createObject($object, $key, $value, $type){
            $this->fillProperty($object, $key, $value);
        }

        protected function fillProperty($object, $key, $value){            
            $object->$key = $value;
        }
        
        protected function extractRecordFromResult($object, $query, $identifiers){   
            $isMatch = array();
            foreach ($query as $record) {
                foreach ($identifiers as $identifier) {
                    if(!property_exists($object, $identifier)) throw new \Exception("Object is missing identifier: $identifier");
                    if($record[$identifier] == $object->$identifier) $isMatch[] = TRUE;
                    else $isMatch[] = FALSE;
                }
                if($this->isMatching($isMatch)) return $record;
            }
            return NULL;
        }
        
        private function isMatching($array){
            foreach ($array as $value) {
                if($value == FALSE) return FALSE;
            }
            return TRUE;
        }

    }
?>
