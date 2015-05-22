<?php
/*
 * library/my/Acl.php
 * Extends Zend_Acl
 *
 */
class Amedia_Acl extends Zend_Acl
{
    public function __construct()
    {
        /**
         * Set Roles
         */
        $this->addRole(new Zend_Acl_Role('guest'));
        $this->addRole(new Zend_Acl_Role('user'));
        $this->addRole(new Zend_Acl_Role('pattern_maker'));
        $this->addRole(new Zend_Acl_Role('superadmin'));

        $this->LoadDefaultResources();
        $this->setDefaultPermissions();
        $this->allow('superadmin');

        if (Zend_Auth::getInstance()->hasIdentity())
          $this->assignPermissions();

        return $this;
    }

    public static function createRole($role, array $resources)
    {
        $data = null;
        $data['privileges_rol_data']= $resources;
        $data['privileges_rol_name']= $role;
        $privilegesModel = new Amedia_Model_Privileges($data);

        return $privilegesModel;
    }

    public function assignPermissions()
    {
        $this->setDefaultPermissions();
        $privilegesModel = new Amedia_Model_Privileges();
        $all =  $privilegesModel->fetchAll();

        for ($i=0; $i < count($all) ; $i++)
        {
            $rol = $all[$i]->privileges_rol_name;
            $this->addRole($rol, 'user');

            $rol =$all[$i]->id_privileges;
            $data_resources = $all[$i]->privileges_rol_data;

            $this->addRole(new Zend_Acl_Role($rol),'user');
            $this->defaultPermissions($rol);

            if (isset($data_resources->checks_user))
                $this->setUserAndGroupsPermissions($rol,$data_resources);

            if (isset($data_resources->checks_setup))
                $this->setSetUpPermissions($rol,$data_resources);

            if (isset($data_resources->checks_products))
                $this->setProductsPermissions($rol,$data_resources);

            if (isset($data_resources->checks_coupons))
                $this->setCouponsPermissions($rol,$data_resources);

            if (isset($data_resources->checks_auctions))
                $this->setAuctionsPermissions($rol,$data_resources);

        }
    }

    public function defaultPermissions($rol)
    {
        $this->allow($rol, 'frontend_index');
        $this->allow($rol, 'frontend_auth');
        $this->allow($rol, 'frontend_search');
        $this->allow($rol, 'frontend_account');
        $this->allow($rol, 'frontend_auction');
        $this->allow($rol, 'frontend_product');
        $this->allow($rol, 'frontend_page');

        $this->allow(array($rol, 'pattern_maker'), array(
            'default_error',    // Default Error Page
            'default_uploader', // Default Uploader Page
            'people_auth',      // People Auth (Login/Logout)
            'default_index'     // Dashboard Page
        ));

        $this->allow($rol, 'default_lang');

        // permissions for json functions > yield
        $this->allow($rol, 'product_manage', array(
            'get-product-data', 'getjson-stats' , 'stats-list'
        ));

        // permissions for json functions user activiy > index
        //$this->allow($rol, 'default_index', array( 'useractstats-list'));

        // permissions for json functions > costing pack
        $this->allow($rol, 'coupon_manage', array( 'get-coupons-by-merchant', 'getjson-stats',  'year-costingdata','deletecomment','updatecomment', 'getdocumentcontainer','getphotocontainer'));
    }

    public function LoadDefaultResources()
    {
        /** Add Resources **/

        // Default
        $this->add(new Zend_Acl_Resource('default_error'));
        $this->add(new Zend_Acl_Resource('default_index'));
        $this->add(new Zend_Acl_Resource('default_uploader'));
        $this->add(new Zend_Acl_Resource('default_lang'));

        // Dashboard
        $this->add(new Zend_Acl_Resource('dashboard_index'));

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
        $this->add(new Zend_Acl_Resource('api_entidad'));

        // Cost Sheet Module
        $this->add(new Zend_Acl_Resource('costsheet_manage'));
        $this->add(new Zend_Acl_Resource('costsheet_programnumber'));

        // Products Module
        $this->add(new Zend_Acl_Resource('product_manage'));
        $this->add(new Zend_Acl_Resource('product_merchant'));
        $this->add(new Zend_Acl_Resource('product_category'));

        // Auction Module
        $this->add(new Zend_Acl_Resource('auction_manage'));

        // Coupon Module
        $this->add(new Zend_Acl_Resource('coupon_manage'));

        $this->add(new Zend_Acl_Resource('frontend_index'));
        $this->add(new Zend_Acl_Resource('frontend_auth'));
        $this->add(new Zend_Acl_Resource('frontend_account'));
        $this->add(new Zend_Acl_Resource('frontend_auction'));
        $this->add(new Zend_Acl_Resource('frontend_search'));
        $this->add(new Zend_Acl_Resource('frontend_product'));
        $this->add(new Zend_Acl_Resource('frontend_page'));
    }

    public function setDefaultPermissions()
    {
        /**
         * Guest Access Level Permissions
         */
        $this->allow('guest', 'default_error');
        $this->allow('guest', 'default_uploader');
        $this->allow('guest', 'default_lang');
        $this->allow('guest', 'people_auth');
        $this->allow('guest', 'api_auth');
        $this->allow('guest', 'api_search');
        $this->allow('guest', 'api_company');
        $this->allow('guest', 'api_entidad');
        $this->allow('guest', 'frontend_index');
        $this->allow('guest', 'frontend_auth');
        $this->allow('guest', 'frontend_account');
        $this->allow('guest', 'frontend_auction');
        $this->allow('guest', 'frontend_search');
        $this->allow('guest', 'frontend_product');
        $this->allow('guest', 'frontend_page');
        /**
         * User Access Level Permissions
         */
        $this->allow('user', 'default_index');
        $this->allow('user', 'default_error');
        $this->allow('user', 'default_uploader');
        $this->allow('user', 'default_lang');
        $this->allow('user', 'people_auth');
        $this->allow('user', 'search_index');
        $this->allow('user', 'frontend_search');
        $this->allow('user', 'frontend_product');
        $this->allow('user', 'frontend_page');
        $this->allow('user', 'people_user', array(
            'my',               // View My Details Page
            'view',             // View User Details Page
            'changepassword',   // Change Password Page
            'updateavatar',     // Update Avatar Lightbox
            'getavatar',        // Get Avatar JSON Call
            'get-addr-avatar','setavatar',        // Set Avatar JSON Call
            'removeavatar'      // Remove Avatar JSON Call
        ));

    /*    $this->allow('user', 'company_manage', array(
            'index',            // Company List Page
            'view'             // Company View Page
        ));*/
    }

    public function setUserAndGroupsPermissions($rol,$userAndGroups)
    {
        $userAndGroups = $userAndGroups->checks_user;
        if($userAndGroups !== null)
        {
            foreach ($userAndGroups as $key => $value)
            {
                switch ($value)
                {
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

    public function setProductsPermissions($rol, $setUp)
    {
        if(isset($setUp->checks_products))
            $setUp = $setUp->checks_products;
        else
            $setUp = null;

        if($setUp !== null)
        {
            foreach ($setUp as $key => $value)
            {
                switch ($value) {
                    case 'manage_product':
                        $this->allow($rol, 'product_manage');
                        $this->allow($rol, 'product_category');
                    break;

                    default:
                        //nothing to do
                        break;
                }
            }
        }
    }


    public function setAuctionsPermissions($rol, $setUp)
    {
        if(isset($setUp->checks_auctions))
            $setUp = $setUp->checks_auctions;
        else
            $setUp = null;

        if($setUp !== null)
        {
            foreach ($setUp as $key => $value)
            {
                switch ($value) {
                    case 'manage_auctions':
                            $this->allow($rol, 'auction_manage');
                    break;

                    default:
                        //nothing to do
                        break;
                }
            }
        }
    }


    public function setCouponsPermissions($rol, $setUp)
    {
        if(isset($setUp->checks_coupons))
            $setUp = $setUp->checks_coupons;
        else
            $setUp = null;

        if($setUp !== null)
        {
            foreach ($setUp as $key => $value)
            {
                switch ($value) {
                    case 'manage_coupons':
                            $this->allow($rol, 'coupon_manage');
                            $this->allow($rol, 'product_merchant');
                    break;

                    default:
                        //nothing to do
                        break;
                }
            }
        }
    }


    public function setSetUpPermissions($rol, $setUp)
    {
        if(isset($setUp->checks_setup))
            $setUp = $setUp->checks_setup;
        else
            $setUp = null;

        if($setUp !== null)
        {
            foreach ($setUp as $key => $value)
            {
                switch ($value) {
                    case 'general_settings':
                        break;

                    case 'roles':
                        $this->allow($rol, 'people_roles');
                        break;

                    case 'yield':
                        $this->allow($rol, 'yield_manage');
                        break;

                    default:
                        //nothing to do
                        break;
                }
            }
        }
    }

    public function imVendor()
    {
        if(Zend_Auth::getInstance()->getIdentity()!==null){
        $company_model = new Company_Model_Company();
        $Company = $company_model->find(Zend_Auth::getInstance()->getIdentity()->ID_company);
        if($Company->company_type=="vendor") {
            return true;
        } else {
            return false;
        }

        }
        return false;
    }
}
?>