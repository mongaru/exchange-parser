<?php
// application/modules/people/model/User.php

class People_Model_User
{
// Fields
    protected $_ID_user;
    protected $_user_name;
    protected $_user_firstname;
    protected $_user_middlename;
    protected $_user_surname;
    protected $_user_email;
    protected $_user_timezone;
    protected $_user_locale;
    protected $_user_is_deleted;
    protected $_user_permanently_deleted;
    protected $_user_auth;
    protected $_user_optional;
    protected $_mapper;

    public function __construct(array $options = null)
    {
        if (is_array($options))
        {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid user property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid user property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value)
        {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods))
            {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**rrdfd
     *
     * @return People_Model_UserMapper
     */
    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new People_Model_UserMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        if(is_numeric($this->ID_user)){
            return $this->ID_user;
        }
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function find($ID_user)
    {
        $this->getMapper()->find($ID_user, $this);
        return $this;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }
    
    public function fetchAllCompany($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAllCompany($where, $order, $count, $offset);
    }

    public function fetchLastLoggedUsers()
    {
        return $this->getMapper()->fetchLastLoggedUsers();
    }

    public function fetchNotifyList()
    {
        return $this->getMapper()->fetchNotifyList();
    }
    
    public function fetchDiscussionNotificationList()
    {
        return $this->getMapper()->fetchDiscussionNotificationList();
    }

    public function fetchAllByDeparment($department)
    {
        return $this->getMapper()->fetchAllByDeparment($department);
    }

    public function fetchAllByClient()
    {
        return $this->getMapper()->fetchAllByClient();
    }

    public function completlyArrayData(){
        $array =array(
            'ID_user' => $this->_ID_user,
            'user_name' => $this->_user_name,
            'user_firstname' => $this->_user_firstname,
            'user_surname' => $this->_user_surname,
            'user_email' => $this->_user_email,
            'user_timezone' => $this->_user_timezone,
            'user_locale' => $this->_user_locale,
            'user_is_deleted' => $this->_user_is_deleted,
            'user_permanently_deleted' => $this->_user_permanently_deleted                   
        );
        if($this->_user_optional !== null ){
            if($this->getuser_optionalin('user_phone')!==null){
                $array['user_phone'] = $this->getuser_optionalin('user_phone');
            }
            if($this->getuser_optionalin('user_mobile')!==null){
                $array['user_mobile'] = $this->getuser_optionalin('user_mobile');
            }
            if($this->getuser_optionalin('ID_optional')!==null){
                $array['ID_optional'] = $this->getuser_optionalin('ID_optional');
            }            
            if($this->getuser_optionalin('user_department')!==null){
                $array['user_department'] = $this->getuser_optionalin('user_department');
            }                        
        }
        if($this->_user_auth !== null){
              $array['user_level'] = $this->get_user_authlin('user_level') ;
              $array['ID_auth'] = $this->get_user_authlin('ID_auth') ;
              $array['user_avatar_file'] = $this->get_user_authlin('user_avatar_file') ;
        
        }


 
        return $array;
    }


    public function toArray() 
    {
        $array =array(
            'ID_user' => $this->_ID_user,
            'user_name' => $this->_user_name,
            'user_firstname' => $this->_user_firstname,
            'user_surname' => $this->_user_surname,
            'user_email' => $this->_user_email,
            'user_timezone' => $this->_user_timezone,
            'user_locale' => $this->_user_locale,
            'user_is_deleted' => $this->_user_is_deleted,
            'user_permanently_deleted' => $this->_user_permanently_deleted                   
        );
        return $array;
    }

    /**
     *
     * @param integer $ID_user
     * @return <type>
     */
    public function getUserCompany($ID_user)
    {
        return $this->getMapper()->getUserCompany($ID_user);
    }

    /**
     * Returns all users filtered by group and role/user level
     * 
     * @param string|null $group
     * @param string|null $role
     * @return array
     */
    public function fetchAllFilteredByGroupAndRole($group = null, $role = null)
    {
        if (is_null($group))
            $group = (string)Zend_Auth::getInstance()->getIdentity()->user_department;
        if (is_null($role))
            $role = (string)Zend_Auth::getInstance()->getIdentity()->user_level;
        return $this->getMapper()->fetchAllFilteredByGroupAndRole($group, $role);
    }

    /**
     * @param $_user_permanently_deleted the $_user_permanently_deleted to set
     */
    public function setUser_permanently_deleted($_user_permanently_deleted)
    {
        $this->_user_permanently_deleted = $_user_permanently_deleted;
        return $this;
    }

    /**
     * @return the $_user_permanently_deleted
     */
    public function getuser_permanently_deleted()
    {
        return $this->_user_permanently_deleted;
    }


    /**
     * @param $_user_is_deleted the $_user_is_deleted to set
     */
    public function setUser_is_deleted($_user_is_deleted)
    {
        $this->_user_is_deleted = $_user_is_deleted;
        return $this;
    }

    /**
     * @return the $_user_is_deleted
     */
    public function getuser_is_deleted()
    {
        return $this->_user_is_deleted;
    }

    /**
     * @param $_user_locale the $_user_locale to set
     */
    public function setUser_locale($_user_locale)
    {
        $this->_user_locale = $_user_locale;
        return $this;
    }

    /**
     * @return the $_user_locale
     */
    public function getuser_locale()
    {
        return $this->_user_locale;
    }

    /**
     * @param $_user_timezone the $_user_timezone to set
     */
    public function setUser_timezone($_user_timezone)
    {
        $this->_user_timezone = $_user_timezone;
        return $this;
    }

    /**
     * @return the $_user_timezone
     */
    public function getuser_timezone()
    {
        return $this->_user_timezone;
    }

    /**
     * @param $_user_email the $_user_email to set
     */
    public function setUser_email($_user_email)
    {
        $this->_user_email = $_user_email;
        return $this;
    }

    /**
     * @return the $_user_email
     */
    public function getuser_email()
    {
        return $this->_user_email;
    }

    /**
     * @param $_user_surname the $_user_surname to set
     */
    public function setUser_surname($_user_surname)
    {
        $this->_user_surname = $_user_surname;
        return $this;
    }

    /**
     * @return the $_user_surname
     */
    public function getuser_surname()
    {
        return $this->_user_surname;
    }

    /**
     * @param $_user_middlename the $_user_middlename to set
     */
    public function setUser_middlename($_user_middlename)
    {
        $this->_user_middlename = $_user_middlename;
        return $this;
    }

    /**
     * @return the $_user_middlename
     */
    public function getuser_middlename()
    {
        return $this->_user_middlename;
    }

    /**
     * @param $_user_firstname the $_user_firstname to set
     */
    public function setUser_firstname($_user_firstname)
    {
        $this->_user_firstname = $_user_firstname;
        return $this;
    }

    /**
     * @return the $_user_firstname
     */
    public function getuser_firstname()
    {
        return $this->_user_firstname;
    }

    /**
     * @param $_user_name the $_user_name to set
     */
    public function setUser_name($_user_name)
    {
        $this->_user_name = $_user_name;
        return $this;
    }

    /**
     * @return the $_user_name
     */
    public function getuser_name()
    {
        return $this->_user_name;
    }

    /**
     * @param $_ID_user the $_ID_user to set
     */
    public function setID_user($_ID_user)
    {
        $this->_ID_user = $_ID_user;
        return $this;
    }

    /**
     * @return the $_ID_user
     */
    public function getID_user()
    {
        return $this->_ID_user;
    }

    /**
     * @param $_user_auth the $_user_auth to set
     */
    public function setuser_auth($_user_auth)
    {
        $this->_user_auth = $_user_auth;
        return $this;
    }

    /**
     * @return the $_user_auth
     */
    public function getuser_auth()
    {
        return $this->_user_auth;
    }

    /**
     * @param $_user_optional the $_user_optional to set
     */
    public function setuser_optional($_user_optional)
    {
        $this->_user_optional = $_user_optional;
        return $this;
    }

    /**
     * @return the $_user_optional
     */
    public function getuser_optional()
    {
        return $this->_user_optional;
    }

    /**
     * @return the $user_ auth value
     */
    public function get_user_authlin($key)
    {
        if(isset($this->_user_auth[$key]) && $this->_user_auth[$key]!==null){
            return $this->_user_auth[$key];
        } 
        return  null;

    }

    /**
     * @return the $_user_optional value
     */
    public function getuser_optionalin($key)
    {
        if(isset($this->_user_optional[$key]) && $this->_user_optional[$key]!==null){
            return $this->_user_optional[$key];
        } 
        return  null;

    }



    public function getLastLogin($pack_ID)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
        //get bids        
        $resultPacks = $dbAdapter->select('*')
                           ->from('bid')
                           ->where("ID_Pack= $pack_ID ");
        
        $queryPacks = $resultPacks->query()->fetchAll();
        
       //go over bids 
       for ($i = 0; $i < count($queryPacks); $i++) {
        
            //get last logout of the pack user
            $result = $dbAdapter->select('*')
                                                ->from('activity')
                                                ->where("activity_type = 'logout'")
                                                ->where("activity_result = 'success'")
                                                ->where("ID_USER=".intval($queryPacks[$i]['ID_user']))
                                                ->order('id_activity DESC')
                                                ->limit(1);
                        
            
            $last_logout = $result->query()->fetchAll();
             
            //if the bid is created after the last logout, return true            
            if ( (count($last_logout) > 0) and ($last_logout[0]['activity_date'] < $queryPacks[$i]['bid_created']) )
                return true; 
       } 
               
      return false;
    }
    
    public function getLastActuallyLogin($ID_user)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
            //get last user login
            $result = $dbAdapter->select('*')
                ->from('activity')
                ->where("activity_type = 'login'")
                ->where("activity_result = 'success'")
                ->where("ID_USER=".intval($ID_user))
                ->order('id_activity DESC')
                ->limit(1);
                
            $last_login = $result->query()->fetchAll();
            
        
            //get last user logout
            $result_logout = $dbAdapter->select('*')
                ->from('activity')
                ->where("activity_type = 'logout'")
                ->where("activity_result = 'success'")
                ->where("ID_USER=".intval($ID_user))
                ->order('id_activity DESC')
                ->limit(1);
                
            $last_logout = $result_logout->query()->fetchAll();
                        
            if ($last_login[0]["activity_date"] > $last_logout[0]["activity_date"])
                return false;
            
             
          return true;  
       } 
               
}