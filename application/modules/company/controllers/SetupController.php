<?php
class Company_SetupController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
        $this->view->pageID = 'setup';

        $this->_db = Zend_Db_Table::getDefaultAdapter();

        $this->view->mainRightRail .= " "; // to display main rail

        $this->view->moduleName = $this->getRequest()->getModuleName();
        $this->view->controllerName = $this->getRequest()->getControllerName();
        $this->view->actionName = $this->getRequest()->getActionName();

    }


    public function viewAction()
    {
       
        $setup = new Company_Model_Setup();
        $setup->find(1); //default

        $this->view->setupInfo = $setup;

        $userActionsDisplay = array(
            'showAdd'          => false,
            'showEdit'         => true,
            'showChangePass'   => false,
            'showTrash'        => false,
            'showChangeAvatar' => false,
            'showSetMaster'    => false
        );
        $this->view->pageTitle = 'Setup';
        $this->view->headTitle("Setup");


        $this->view->assign($userActionsDisplay);
        $this->view->mainRightRail .= $this->view->render('SetupActions.phtml');
        

    }



    public function editAction()
    {
        $setup_id = $this->_getParam('id', 0);
        if ($setup_id <= 0)
            return $this->_redirect('/company/manage');

        $setup = new Company_Model_Setup();
        $setup->find($setup_id);
        if (!$setup->setup_id)
            return $this->_redirect('/company/manage');


        $this->view->pageTitle = $this->translate->_('Edit Setup');
        $this->view->headTitle($this->translate->_('Edit Setup'));

        // Instantiate Setup Add form
        $formSetupAdd = new Company_Form_SetupAdd();
        

         // Gets Request Vars
        $request = $this->getRequest();

        if (($request->isPost()) && ($formSetupAdd->isValid($request->getPost())))
        {
        
            $setup->setOptions($formSetupAdd->getValues());
            $setup->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($setup_id);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('setup');
            $activity->setActivity_type('edit');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            return $this->_redirect('/company/setup/view');

        }
        else
        {
            $values = $setup->exportToArray();
            $formSetupAdd->populate($values);
        }

        $this->view->form = $formSetupAdd;
    }


  

}
