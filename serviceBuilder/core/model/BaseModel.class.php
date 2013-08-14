<?php
    namespace be\edge\serviceBuilder\core\model;
	use be\edge\serviceBuilder\core as core;
    require_once('Meta.class.php');
    class BaseModel {
        protected $meta;
        
        public function __construct() {
            $this->meta = new core\model\Meta();
        }
        
        public function getMetaData(){
            return $this->meta;
        }
        
        public function getPropertyTypes(){
            if(property_exists($this->meta, 'propertyTypes')) return $this->meta->propertyTypes;
            return array();
        }
        
        public function getPropertyType($key){
            if(property_exists($this->meta, 'propertyTypes') && array_key_exists($key, $this->meta->propertyTypes)) return $this->meta->propertyTypes[$key];
            return NULL;
        }
    }
?>
