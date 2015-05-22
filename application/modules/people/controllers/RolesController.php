<?php 

/**
 * 
 */
class People_RolesController extends Zend_Controller_Action
{
	function init()
    {
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
        $this->acl = Zend_Registry::get('amediaAcl');
        $this->view->pageID = 'people';
        $this->view->mainRightRail = $this->view->whoIsOnline; // by default the whoIsOnline box is the right rail
	}

	function indexAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl('/assets/js/modules/role.js?' . KK_APP_VERSION), $type = "text/javascript");        
        
        $this->view->domReady.= "Role.init();\n";

		$RolesForm = new People_Form_RolAdd();
        $RolesForm->setAction($this->getRequest()->getRequestUri()."add");
  
		$this->view->form = $RolesForm;
        
		if($this->getRequest()->isPost())
        {
			$this->saveRol();
		}

		$roles = new Amedia_Model_Privileges();
		$roles = $roles->fetchAll();
		$this->view->roles  = $roles;
	}


	function saveRol(){

        $rolform = new People_Form_RolAdd();

        $request = $this->getRequest();
        $post = $request->getPost();
        $rs = array();
		

        if ($request->isPost() && ($rolform->isValid($post)) )
        {
        	$rol = $post['iRoleName'];
        	if(isset($post['checks_user'])){
	        	$rs['checks_user'] = $post['checks_user'];
        	}
        	if(isset($post['checks_costing'])){
	        	$rs['checks_costing'] = $post['checks_costing'];
        	}
        	if(isset($post['checks_costsheet'])){
	        	$rs['checks_costsheet'] = $post['checks_costsheet'];
        	}
        	if(isset($post['checks_setup'])){
	        	$rs['checks_setup'] = $post['checks_setup'];
        	}
        	if(isset($post['checks_discussions'])){
	        	$rs['checks_discussions'] = $post['checks_discussions'];
        	} 
        	if(isset($post['checks_sales'])){
	        	$rs['checks_sales'] = $post['checks_sales'];
        	}             	   
            if(isset($post['yields'])){
                $rs['yields'] = $post['yields'];
            }                  
            $roledata = array('privileges_rol_data'=> $rs,
                'privileges_is_deleted' => 'no',
                'privileges_rol_name'=> $post['iRoleName'],
                'id_privileges'=> $post['id_privileges'],
                'department'=> $post['department']               
                );

            $role = new Amedia_Model_Privileges($roledata);
			
            $role->save();    
            $this->_helper->json(array('status' => 'ok', 'message' => 'Role information saved. Page will be refreshed.'));                	
        } else {
            $this->_helper->json(array('status' => 'error', 'message' => 'Error while saving company.', 'errors' => $rolform->getMessages() )); 
        } 

	}
	
    public function editAction()
    {
		$role = new Amedia_Model_Privileges();
		$ID = $this->_getParam('id', 0);		
		$role->find($ID);
        
		echo $this->_helper->json(array("status" => "ok", "data" => $role->toArray()));
	}
    
	public function deleteAction()
    {
		$this->_changeStatus("yes");
	}

	public function restoreAction()
    {
		$this->_changeStatus("no");
	}

    private function _changeStatus($deleted)
    {
        $id_privileges = $this->_getParam('id', 0);
        if ($id_privileges <= 0)
            return $this->_redirect('/people/roles');

        $cl = new Amedia_Model_Privileges();
        $cl->find($id_privileges);
        if (!$cl->id_privileges)
            return $this->_redirect('/people/roles');


        $cl->privileges_is_deleted = $deleted;
        $cl->save();

        // Log Activity
        $ID_user = Zend_Auth::getInstance()->getIdentity()->ID_user;
        $action = ($deleted == 'yes')? 'trash':'restore';
        $activity = People_Model_Activity::createNowActivity($ID_user, $id_privileges, 'roles', $action, 'success');
        $activity->save();
        return $this->_redirect('/people/roles');
    }
}
 ?>