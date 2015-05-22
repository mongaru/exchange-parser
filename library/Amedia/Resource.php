<?php 

/**
* 
*/
class Amedia_Resource 
{



	protected $AlowResources = array();
	protected $rol;


	function __construct($rol,array $resources)
	{
		
		$this->rol = $rol;
		$this->AlowResources = $resources;
		
	}


	function save(){
		$privilegesModel = new Amedia_Model_Privileges($this);
        $privilegesModel->save();
	}

	
	function getparents(){
		return $this->parent;
	}

	function getAlowResources(){
		return $this->AlowResources;
	}
	function getAlowResourcesfor($index){
		if(isset($this->AlowResources[$index])){
			return $this->AlowResources[$index];
		} else {
			return null;
		}
	}
	function getRol(){
		return $this->rol;
	}


}
 ?>