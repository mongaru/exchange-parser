<?php
/*
 * library/my/Acl.php
 * Extends Zend_Acl
 *
 */
class Amedia_Acl extends Zend_Acl
{

    public static function createRole($role ,array $resources){
        $data = null;
        $data['privileges_rol_data']= $resources;
        $data['privileges_rol_name']= $role;        
        $privilegesModel = new Amedia_Model_Privileges($data);       
        return $privilegesModel;
    }


    public function assignPermissions() {

        $this->setDefaultPermissions();
        $privilegesModel = new Amedia_Model_Privileges();
        $all =  $privilegesModel->fetchAll();
        
        for ($i=0; $i < count($all) ; $i++) {
            $rol_id =$all[$i]->privileges_rol_name;
          //  $this->addRole($rol_id, 'user'); 

            $rol =$all[$i]->id_privileges;
            $data_resources = $all[$i]->privileges_rol_data;
            $this->addRole(new Zend_Acl_Role($rol),'user');
            $this->defaultPermissions($rol);
            if (isset($data_resources->checks_user)){
                $this->setUserAndGroupsPermissions($rol,$data_resources);   
            }
            if (isset($data_resources->checks_setup)){
                $this->setSetUpPermissions($rol,$data_resources);   
            }
            if (isset($data_resources->checks_discussions)){
                $this->setDiscussionsPermissions($rol,$data_resources);
            }
            if (isset($data_resources->checks_sales)){
                $this->setSalesPermissions($rol,$data_resources);   
            }
            if (isset($data_resources->checks_costing)){
                $this->setCostingPackPermissions($rol,$data_resources); 
            }                                    
            if (isset($data_resources->checks_costsheet)){
                $this->setCostsheetPermissions($rol,$data_resources);  
            }         
            if (isset($data_resources->yields)){
                $this->setYieldsPermissions($rol,$data_resources);  
            }         
            if(!Zend_Acl::hasRole($rol_id)) {
                $this->addRole(new Zend_Acl_Role($rol_id), $rol);
            }

      }
    }

    function defaultPermissions($rol){

/*
        $this->allow(array($rol, 'pattern_maker'), array(
            'default_error',    // Default Error Page
            'default_uploader', // Default Uploader Page
            'people_auth',      // People Auth (Login/Logout)
            'default_index'     // Dashboard Page
        ));*/
        $this->allow($rol, 'default_lang');

/*
        $this->allow(array($rol, 'pattern_maker'), 'people_user', array(
            'my',               // My Details Page
            'view',             // User View Page
            'changepassword',   // Change Password Page
            'updateavatar',     // Update Avatar JSON Call
            'getavatar',        // Get Avatar JSON Call
            'setavatar',        // Set Avatar JSON Call
            'removeavatar'      // Remove Avatar JSON Call
        ));*/
//        permissions for json functions > yield
        $this->allow($rol, 'yield_manage', array(
            'getjson-stats-for-year', 'getjson-stats' , 'stats-list' 
        ));        
//        permissions for json functions user activiy > index
        $this->allow($rol, 'default_index', array( 'useractstats-list'));                
//        permissions for json functions > costing pack
        $this->allow($rol, 'costing_manage', array( 'costing-list', 'getjson-stats',  'year-costingdata','deletecomment','updatecomment', 'getdocumentcontainer','getphotocontainer','download-all'));                
        $this->allow($rol, 'people_user', array( 'get-addr-avatar'));       

    }

    public function __construct()
    {    
//        var_dump(Zend_Auth::getInstance()->getIdentity()->user_level);
//        var_dump(Zend_Auth::getInstance()->getIdentity()->ID_user);
        /**
         * Set Roles
         */
        //get role and set the resources for te role
        // Guest|
        $this->addRole(new Zend_Acl_Role('guest'));

        // View Only Access
        $this->addRole(new Zend_Acl_Role('user'));
 //       $this->addRole(new Zend_Acl_Role('pattern_maker'));
/*        // User Access
        $this->addRole(new Zend_Acl_Role('yielder'));

        $this->addRole(new Zend_Acl_Role('sales_staff'));
        $this->addRole(new Zend_Acl_Role('client_company_member'));
        $this->addRole(new Zend_Acl_Role('costing_staff'));
        $this->addRole(new Zend_Acl_Role('vendor_staff'));

        // Full Access
        $this->addRole(new Zend_Acl_Role('pattern_maker_manager'), 'pattern_maker');
        $this->addRole(new Zend_Acl_Role('yielder_manager'), 'yielder');
        $this->addRole(new Zend_Acl_Role('sales_manager'), 'sales_staff');
        $this->addRole(new Zend_Acl_Role('client_company_manager'), 'client_company_member');
        $this->addRole(new Zend_Acl_Role('costing_manager'), 'costing_staff');
        $this->addRole(new Zend_Acl_Role('vendor_manager'), 'vendor_staff');
        // Admins
        $this->addRole(new Zend_Acl_Role('yield_admin'), array('pattern_maker_manager', 'yielder_manager'));
        $this->addRole(new Zend_Acl_Role('sales_admin'), array('sales_manager', 'client_company_manager'));      */
        $this->addRole(new Zend_Acl_Role('superadmin'));

        $this->LoadDefaultResources();
        $this->allow('superadmin');
        if (Zend_Auth::getInstance()->hasIdentity()){
          $this->assignPermissions();
        }

        return $this;
    }

    function LoadDefaultResources(){
                /** Add Resources **/

        // Default
        $this->add(new Zend_Acl_Resource('default_error'));
        $this->add(new Zend_Acl_Resource('default_index'));
        $this->add(new Zend_Acl_Resource('default_uploader'));
        $this->add(new Zend_Acl_Resource('default_lang'));

        // Search Module
        $this->add(new Zend_Acl_Resource('search_index'));

        // Auth Module
        $this->add(new Zend_Acl_Resource('people_auth'));
        $this->add(new Zend_Acl_Resource('people_user'));
        $this->add(new Zend_Acl_Resource('people_roles'));        
        $this->add(new Zend_Acl_Resource('users_changepassword'));
        $this->add(new Zend_Acl_Resource('company_manage'));

        // Sales Kit Module
        $this->add(new Zend_Acl_Resource('saleskit_manage'));
        $this->add(new Zend_Acl_Resource('saleskit_refinement'));

        // Yield Module
        $this->add(new Zend_Acl_Resource('yield_manage'));
        $this->add(new Zend_Acl_Resource('yield_refinement'));
        $this->add(new Zend_Acl_Resource('yield_managebodies'));
        $this->add(new Zend_Acl_Resource('yield_managestyles'));

        // Costing Pack Module
        $this->add(new Zend_Acl_Resource('costing_manage'));
        $this->add(new Zend_Acl_Resource('costing_bid'));
        $this->add(new Zend_Acl_Resource('costing_refinement'));

        // Discussions Module
        $this->add(new Zend_Acl_Resource('discussion_manage'));

        // Comment Module
        $this->add(new Zend_Acl_Resource('comment_index'));
        $this->add(new Zend_Acl_Resource('comment_yield'));
        $this->add(new Zend_Acl_Resource('comment_saleskit'));
        $this->add(new Zend_Acl_Resource('comment_costing'));
        $this->add(new Zend_Acl_Resource('comment_bid'));
        
        // API Module
        $this->add(new Zend_Acl_Resource('api_auth'));
        $this->add(new Zend_Acl_Resource('api_search'));
        $this->add(new Zend_Acl_Resource('api_yield'));
        $this->add(new Zend_Acl_Resource('api_company'));
        
        // Cost Sheet Module
        $this->add(new Zend_Acl_Resource('costsheet_manage'));
        $this->add(new Zend_Acl_Resource('costsheet_programnumber'));
    }

    function setDefaultPermissions(){
        /**
         * Assign Default Permissions
         */

        /**
         *
         * Guest Access Level Permissions
         *
         */
        $this->allow('guest', 'default_error');
        $this->allow('guest', 'default_uploader');
        $this->allow('guest', 'default_lang');
        $this->allow('guest', 'people_auth');
        $this->allow('guest', 'api_auth');
        $this->allow('guest', 'api_search');
        $this->allow('guest', 'api_company');

        /**
         *
         * User Access Level Permissions
         *
         */
        $this->allow('user', 'default_index');
        $this->allow('user', 'default_error');
        $this->allow('user', 'default_uploader');
        $this->allow('user', 'default_lang');
        $this->allow('user', 'people_auth');
        $this->allow('user', 'search_index');
        $this->allow('user', 'people_user', array(
                    
            'my',               // View My Details Page
            'view',             // View User Details Page
//            'changepassword',   // Change Password Page
            'updateavatar',     // Update Avatar Lightbox
            'getavatar',        // Get Avatar JSON Call
            'setavatar',        // Set Avatar JSON Call
            'removeavatar'      // Remove Avatar JSON Call
        ));
    
/*        $this->allow('user', 'yield_manage', array(
            'index',            // Yield List Page
            'view',             // Yield View Page
            'stats',            // Yield Stats Dashboard Box
            'pending',          // Yield Pending Dashboard Box
            'selecteddownload'  // Yield Download Selected Dropdown
        ));

        $this->allow('user', 'yield_refinement');*/
        $this->allow('user', 'discussion_manage', array(
            'index',            // Discussion List Page
            'view',             // Discussion View Page
            'stats',            // Discussion Stats Dashboard Box
            'pending'           // Discussion Stats Pending Box
        ));
   //     $this->allow('user', 'company_manage', array(
   //         'index',            // Company List Page
    //        'view'             // Company View Page
     //   ));

    }

    function setUserAndGroupsPermissions($rol,$userAndGroups){

        $userAndGroups = $userAndGroups->checks_user;
        if($userAndGroups !== null){        
            foreach ($userAndGroups as $key => $value) {
                switch ($value) {
                    case 'manage_users':
                        $this->allow($rol, 'people_user');                     
                    break;

                    case 'manage_groups':
                           $this->allow($rol, 'company_manage');
                    break;                

                    case 'read_users':
                        $this->allow($rol, 'people_user', array(
                            'my',               // View My Details Page
                            'view',             // View User Details Page
                            'index'
                        ));
                    break;

                    case 'add_users':
                        $this->allow($rol, 'people_user', array(
                            'add',               // View My Details Page
                            'view',             // View User Details Page
                            'index'
                        ));
                    break;           

                    case 'add_groups':
                        $this->allow($rol, 'company_manage', array(
                            'add',               // View My Details Page
                            'view',             // View User Details Page
                            'index'
                        ));
                    break;
                    
                    case 'read_groups':
                        $this->allow($rol, 'company_manage', array(
                            'index',            // Company List Page
                            'view'             // Company View Page
                        ));                    

                        break;

                    default:
                        //nothing to do 
                        break;
                }

            }
        }
    }




    function setYieldsPermissions($rol,$setUp){
        if(isset($setUp->yields)){
            $setUp = $setUp->yields;
        } else {
            $setUp = null;
        }

        if($setUp !== null){
            foreach ($setUp as $key => $value) {
                switch ($value) {
                    case 'yields_add':
                            $this->allow($rol, 'yield_manage', array('add', 'edit', 'index'));

                    break;
                    case 'yields_complete':
                            $this->allow($rol, 'yield_manage', array('complete'));

                    break;
                    
                    case 'yields_archived':
                            $this->allow($rol, 'yield_manage', array('archived'));

                    break; 

                    case 'yields_view':
                    //Permissions for People/roles/*
                            $this->allow($rol, 'yield_manage', array('view', 'index'));
                        break;

                    case 'yields_manage':
                    //Permissions for yield/manage/*
                            $this->allow($rol, 'yield_manage');
                        break;
                    default:
                        //nothing to do 
                        break;
                }
            }

        }
    } 


    function setSetUpPermissions($rol,$setUp){
        if(isset($setUp->checks_setup)){
            $setUp = $setUp->checks_setup;
        } else {
            $setUp = null;
        }
  
        if($setUp !== null){
            foreach ($setUp as $key => $value) {
                switch ($value) {
                    case 'general_settings':
                    //Permissions for company/setup/view
                       // $this->allow($rol, '?');

                    break;
                    case 'roles':
                    //Permissions for People/roles/*
                            $this->allow($rol, 'people_roles');
                        break;

                    case 'yield':
                    //Permissions for yield/manage/*
                            $this->allow($rol, 'yield_manage');
                        break;
                    default:
                        //nothing to do 
                        break;
                }
            }

        }
    } 


    function setDiscussionsPermissions($rol,$Discussion){
        if(isset($Discussion->checks_discussions)){
            $Discussion = $Discussion->checks_discussions;
        } else {
            $Discussion = null;
        }

        
        if($Discussion !== null){
            foreach ($Discussion as $key => $value) {
                switch ($value) {
                    case 'discussion_view':
                    //Permissions for discussion_manage/view
                      $this->allow($rol, 'discussion_manage', array('view', 'index'));

                    break;
                    case 'discussions_add':
                    //Permissions for discussion_manage/*
                            $this->allow($rol, 'discussion_manage', array('add', 'edit'));
                        break;

                    case 'discussions_manage':
                    //Permissions for discussion_manage/*
                            $this->allow($rol, 'discussion_manage');
                        break;
                    default:
                        //nothing to do 
                        break;
                }
            }

        }
    } 
    function setSalesPermissions($rol,$sales){
        if(isset($sales->checks_sales)){
            $sales = $sales->checks_sales;
        } else {
            $sales = null;
        }
       

        if($sales !== null){
            foreach ($sales as $key => $value) {
                switch ($value) {
                    case 'sales_view':

                      $this->allow($rol, 'saleskit_manage', array('view', 'index'));

                    break;
                    case 'sales_add':
 
                            $this->allow($rol, 'saleskit_manage', array('add', 'edit'));
                        break;

                    case 'sales_manage':
                    //Permissions for saleskit_manage/*
                            $this->allow($rol, 'saleskit_manage');
                        break;

                    default:
                        //nothing to do 
                        break;
                }
            }

        }
    } 

    function isVendor($user_level){
        if($this->isAllowed(Zend_Auth::getInstance()->getIdentity()->user_level, 'discussion_manage', 'isVendor')) {return true;} else {return false;} 
    }
    function setCostingPackPermissions($rol,$Costing){
        if(isset($Costing->checks_costing)){
            $Costing = $Costing->checks_costing;
        } else {
            $Costing = null;
        }        
        if($Costing !== null){
        foreach ($Costing as $key => $value) {
            switch ($value) {
                case 'isVendor':
                //Permissions for costing/manage/index
                    $this->allow($rol, 'costing_manage', array( 'index','view','print','requestrecost','deletecomment'));
                    $this->allow($rol, 'costing_bid', array('view','submit','my'));
                    $this->allow($rol, 'discussion_manage', array('isVendor')); 
                break;

                case 'cp_list':
                //Permissions for costing/manage/index
                        $this->allow($rol, 'costing_manage', array(
                        'index', 'view' ));
                break;
                
                case 'cp_add' :
                //Permissions for costing/manage/add
                        $this->allow($rol, 'costing_manage', array(
                        'add'));
                break;


                case 'cp_copy' :
                //Permissions for costing/manage/add
                        $this->allow($rol, 'costing_manage', array(
                        'add'));
                break;                
                               
                
                case 'cp_edit':
                //Permissions for costing/manage/edit
                        $this->allow($rol, 'costing_manage', array(
                        'edit'));
                break;  
                case 'cp_delete':
                //Permissions for costing/manage/edit
                        $this->allow($rol, 'costing_manage', array(
                        'trash', 'restore'));
                break;                  

                case 'cp_view':
                //Permissions for costing/manage/view
                        $this->allow($rol, 'costing_manage', array(
                        'view'));
                break;
                case 'cp_manage':
                //Permissions for costing/manage/trash
                        $this->allow($rol, 'costing_manage');
                break;
                case 'cp_print':
                //Permissions for costing/manage/print
                        $this->allow($rol, 'costing_manage', array(
                        'print'));
                break;
                case 'cp_print_all':
                //Permissions for costing/manage/print
                        $this->allow($rol, 'costing_manage', array(
                        'print-bids'));                    
                
                break;

                case 'cp_print_bid':
                //Permissions for costing/manage/add/copyasnew+id
                        $this->allow($rol, 'costing_bid', array(
                        'print'));

                break;


                case 'cp_view_bid':
                //Permissions for costing/manage/add/copyasnew+id
                        $this->allow($rol, 'costing_bid', array(
                        'view'));

                break;

                case 'cp_manage_bid':
                 //Permissions for costing/manage/add/copyasnew+id
                        $this->allow($rol, 'costing_bid', array(
                        'view',
                        'my',                        
                        'edit',
                        'print',
                        'accept'));  
                break;

                case 'cp_accept':
                    $this->allow($rol, 'costing_bid', array(
                        'accept',             // Accept Costing Pack Bid
                        'unaccept'            // UnAccept Costing Pack Bid
                    ));
                break;
                case 'cp_submit_bid' :
                //Permissions for costing/bid/submit
                        $this->allow($rol, 'costing_bid', array(
                        'submit'));
                break; 
                case 'cp_sendTovendor':
                    $this->allow($rol, 'costing_manage', array('sendtovendors'));
                break;


                case 'cp_archive':
                    $this->allow($rol, 'costing_manage', array(
                        'archive',          // Archive Costing Pack
                        'trash',            // Trash Costing Pack
                        'restore',          // Restore Costing Pack
                        'restorearchived',  // Restore Costing Pack from Archive
                        'sendtovendors',    // Send Costing Pack to Vendors AJAX
                        'requestrecost'     // Request Bid Recost AJAX
                    ));
                break;

                default:
                    //nothing to do 
                    break;
            }
        }
    }   
        }   

    function setCostsheetPermissions($rol,$costsheet){
        if(isset($costsheet->checks_costsheet)){
            $costsheet = $costsheet->checks_costsheet;
        } else {
            $costsheet = null;
        }

        if($costsheet !== null){
        foreach ($costsheet as $key => $value) {
            switch ($value) { // costsheet_manager
                case 'costsheet_list_colorlayout':
                    $this->allow($rol, 'costsheet_manage', array(
                        'index', 'view'
                    ));
                break;

                case 'costsheet_manage_colorlayout': //add edit trash restore
                    $this->allow($rol, 'costsheet_manage');
                break;

                case 'costsheet_manage_layout14':
                    $this->allow($rol, 'costsheet_manage', array(
                        'layout','printlayout'
                    ));
                break;

                case 'costsheet_manage_import':
                    $this->allow($rol, 'costsheet_manage', array(
                        'import','printimport'
                    ));
                break;

                case 'costsheet_manage_mexico':
                    $this->allow($rol, 'costsheet_manage', array(
                        'mexico','printmexico'
                    ));
                break;                   
                default:
                    //nothing to do 
                    break;
            }
        }
    }
}
}
?>