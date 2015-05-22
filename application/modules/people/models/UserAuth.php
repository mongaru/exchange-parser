<?php
// application/modules/people/model/UserAuth.php

class People_Model_UserAuth
{
// Fields
    protected $_ID_auth;
    protected $_ID_user;
    protected $_user_salt;
    protected $_user_password;
    protected $_user_status;
    protected $_user_level;
    protected $_user_created;
    protected $_user_updated;
    protected $_user_invalid_logins;
    protected $_user_banned_until;
    protected $_user_secret_question;
    protected $_user_secret_answer;
    protected $_user_last_login;
    protected $_user_avatar_file;
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
            throw new Exception('Invalid user auth property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid user auth property');
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

    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new People_Model_UserAuthMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
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

    /**
     * @param $_user_avatar_file the $_user_avatar_file to set
     */
    public function setUser_avatar_file($_user_avatar_file)
    {
        $this->_user_avatar_file = $_user_avatar_file;
        return $this;
    }

    /**
     * @return the $_user_avatar_file
     */
    public function getUser_avatar_file()
    {
        return $this->_user_avatar_file;
    }

    /**
     * @param $_user_last_login the $_user_last_login to set
     */
    public function setUser_last_login($_user_last_login)
    {
        $this->_user_last_login = $_user_last_login;
        return $this;
    }

    /**
     * @return the $_user_last_login
     */
    public function getuser_last_login()
    {
        return $this->_user_last_login;
    }

    /**
     * @param $_user_secret_answer the $_user_secret_answer to set
     */
    public function setUser_secret_answer($_user_secret_answer)
    {
        $this->_user_secret_answer = $_user_secret_answer;
        return $this;
    }

    /**
     * @return the $_user_secret_answer
     */
    public function getuser_secret_answer()
    {
        return $this->_user_secret_answer;
    }

    /**
     * @param $_user_secret_question the $_user_secret_question to set
     */
    public function setUser_secret_question($_user_secret_question)
    {
        $this->_user_secret_question = $_user_secret_question;
        return $this;
    }

    /**
     * @return the $_user_secret_question
     */
    public function getuser_secret_question()
    {
        return $this->_user_secret_question;
    }


    public function fetchAllByUserLevel($userLevel)
    {
        return $this->getMapper()->fetchAllByUserLevel($userLevel);
    }

    /**
     * @param $_user_banned_until the $_user_banned_until to set
     */
    public function setUser_banned_until($_user_banned_until)
    {
        $this->_user_banned_until = $_user_banned_until;
        return $this;
    }

    /**
     * @return the $_user_banned_until
     */
    public function getuser_banned_until()
    {
        return $this->_user_banned_until;
    }

    /**
     * @param $_user_invalid_logins the $_user_invalid_logins to set
     */
    public function setUser_invalid_logins($_user_invalid_logins)
    {
        $this->_user_invalid_logins = $_user_invalid_logins;
        return $this;
    }

    /**
     * @return the $_user_invalid_logins
     */
    public function getuser_invalid_logins()
    {
        return $this->_user_invalid_logins;
    }

    /**
     * @param $_user_updated the $_user_updated to set
     */
    public function setUser_updated($_user_updated)
    {
        $this->_user_updated = $_user_updated;
        return $this;
    }

    /**
     * @return the $_user_updated
     */
    public function getuser_updated()
    {
        return $this->_user_updated;
    }

    /**
     * @param $_user_created the $_user_created to set
     */
    public function setUser_created($_user_created)
    {
        $this->_user_created = $_user_created;
        return $this;
    }

    /**
     * @return the $_user_created
     */
    public function getuser_created()
    {
        return $this->_user_created;
    }

    /**
     * @param $_user_level the $_user_level to set
     */
    public function setUser_level($_user_level)
    {
        $this->_user_level = $_user_level;
        return $this;
    }

    /**
     * @return the $_user_level
     */
    public function getuser_level()
    {
        return $this->_user_level;
    }

    /**
     * @param $_user_status the $_user_status to set
     */
    public function setUser_status($_user_status)
    {
        $this->_user_status = $_user_status;
        return $this;
    }

    /**
     * @return the $_user_status
     */
    public function getuser_status()
    {
        return $this->_user_status;
    }

    /**
     * @param $_user_password the $_user_password to set
     */
    public function setUser_password($_user_password)
    {
        $this->_user_password = $_user_password;
        return $this;
    }

    /**
     * @return the $_user_password
     */
    public function getuser_password()
    {
        return $this->_user_password;
    }

    /**
     * @param $_user_salt the $_user_salt to set
     */
    public function setUser_salt($_user_salt)
    {
        $this->_user_salt = $_user_salt;
        return $this;
    }

    /**
     * @return the $_user_salt
     */
    public function getuser_salt()
    {
        return $this->_user_salt;
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
     * @param $_ID_auth the $_ID_auth to set
     */
    public function setID_auth($_ID_auth)
    {
        $this->_ID_auth = $_ID_auth;
        return $this;
    }

    /**
     * @return the $_ID_auth
     */
    public function getID_auth()
    {
        return $this->_ID_auth;
    }

    public function generatePassword($length = 8, $strength = 0)
    {
        $vowels = 'aeuy';
        $consonants = 'bdghjmnpqrstvz';
        if ($strength & 1)
        {
            $consonants .= 'BDGHJLMNPQRSTVWXZ';
        }
        if ($strength & 2)
        {
            $vowels .= "AEUY";
        }
        if ($strength & 4)
        {
            $consonants .= '23456789';
        }
        if ($strength & 8)
        {
            $consonants .= '@#$%';
        }

        $password = '';
        $alt = time() % 2;
        for ($i = 0; $i < $length; $i++)
        {
            if ($alt == 1)
            {
                $password .= $consonants[(rand() % strlen($consonants))];
                $alt = 0;
            }
            else
            {
                $password .= $vowels[(rand() % strlen($vowels))];
                $alt = 1;
            }
        }

        return $password;

    }

}