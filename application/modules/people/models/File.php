<?php
// application/modules/people/model/File.php

class People_Model_File
{
    // Fields
    protected $_ID_file;
    protected $_ID_user;
    protected $_file_title;
    protected $_file_description;
    protected $_file_display;
    protected $_file_views;
    protected $_file_is_image;
    protected $_file_name;
    protected $_file_type;
    protected $_file_path;
    protected $_file_full_path;
    protected $_file_raw_name;
    protected $_file_orig_name;
    protected $_file_extension;
    protected $_file_size;
    protected $_file_image_width;
    protected $_file_image_height;
    protected $_file_image_type;
    protected $_file_uploaded_date;
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
            throw new Exception('Invalid file property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid file property');
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

    /**
     *
     * @return People_Model_FileMapper
     */
    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new People_Model_FileMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function delete()
    {
        $this->getMapper()->delete($this);
    }

    public function find($ID_file)
    {
        $this->getMapper()->find($ID_file, $this);
        return $this;
    }

    public function findAvatar($ID_user)
    {
        $this->getMapper()->findAvatar($ID_user, $this);
        return $this;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }
    /**
     * @param $_file_image_type the $_file_image_type to set
     */
    public function setFile_image_type($_file_image_type)
    {
        $this->_file_image_type = $_file_image_type;
        return $this;
    }

    /**
     * @return the $_file_image_type
     */
    public function getfile_image_type()
    {
        return $this->_file_image_type;
    }

    /**
     * @param $_file_image_height the $_file_image_height to set
     */
    public function setFile_image_height($_file_image_height)
    {
        $this->_file_image_height = $_file_image_height;
        return $this;
    }

    /**
     * @return the $_file_image_height
     */
    public function getfile_image_height()
    {
        return $this->_file_image_height;
    }

    /**
     * @param $_file_image_width the $_file_image_width to set
     */
    public function setFile_image_width($_file_image_width)
    {
        $this->_file_image_width = $_file_image_width;
        return $this;
    }

    /**
     * @return the $_file_image_width
     */
    public function getfile_image_width()
    {
        return $this->_file_image_width;
    }

    /**
     * @param $_file_size the $_file_size to set
     */
    public function setFile_size($_file_size)
    {
        $this->_file_size = $_file_size;
        return $this;
    }

    /**
     * @return the $_file_size
     */
    public function getfile_size()
    {
        return $this->_file_size;
    }

    /**
     * @param $_file_extension the $_file_extension to set
     */
    public function setFile_extension($_file_extension)
    {
        $this->_file_extension = $_file_extension;
        return $this;
    }

    /**
     * @return the $_file_extension
     */
    public function getfile_extension()
    {
        return $this->_file_extension;
    }

    /**
     * @param $_file_orig_name the $_file_orig_name to set
     */
    public function setFile_orig_name($_file_orig_name)
    {
        $this->_file_orig_name = $_file_orig_name;
        return $this;
    }

    /**
     * @return the $_file_orig_name
     */
    public function getfile_orig_name()
    {
        return $this->_file_orig_name;
    }

    /**
     * @param $_file_raw_name the $_file_raw_name to set
     */
    public function setFile_raw_name($_file_raw_name)
    {
        $this->_file_raw_name = $_file_raw_name;
        return $this;
    }

    /**
     * @return the $_file_raw_name
     */
    public function getfile_raw_name()
    {
        return $this->_file_raw_name;
    }

    /**
     * @param $_file_full_path the $_file_full_path to set
     */
    public function setFile_full_path($_file_full_path)
    {
        $this->_file_full_path = $_file_full_path;
        return $this;
    }

    /**
     * @return the $_file_full_path
     */
    public function getfile_full_path()
    {
        return $this->_file_full_path;
    }

    /**
     * @param $_file_path the $_file_path to set
     */
    public function setFile_path($_file_path)
    {
        $this->_file_path = $_file_path;
        return $this;
    }

    /**
     * @return the $_file_path
     */
    public function getfile_path()
    {
        return $this->_file_path;
    }

    /**
     * @param $_file_type the $_file_type to set
     */
    public function setFile_type($_file_type)
    {
        $this->_file_type = $_file_type;
        return $this;
    }

    /**
     * @return the $_file_type
     */
    public function getfile_type()
    {
        return $this->_file_type;
    }

    /**
     * @param $_file_name the $_file_name to set
     */
    public function setFile_name($_file_name)
    {
        $this->_file_name = $_file_name;
        return $this;
    }

    /**
     * @return the $_file_name
     */
    public function getfile_name()
    {
        return $this->_file_name;
    }

    /**
     * @param $_file_is_image the $_file_is_image to set
     */
    public function setFile_is_image($_file_is_image)
    {
        $this->_file_is_image = $_file_is_image;
        return $this;
    }

    /**
     * @return the $_file_is_image
     */
    public function getfile_is_image()
    {
        return $this->_file_is_image;
    }

    /**
     * @param $_file_views the $_file_views to set
     */
    public function setFile_views($_file_views)
    {
        $this->_file_views = $_file_views;
        return $this;
    }

    /**
     * @return the $_file_views
     */
    public function getfile_views()
    {
        return $this->_file_views;
    }

    /**
     * @param $_file_display the $_file_display to set
     */
    public function setFile_display($_file_display)
    {
        $this->_file_display = $_file_display;
        return $this;
    }

    /**
     * @return the $_file_display
     */
    public function getfile_display()
    {
        return $this->_file_display;
    }

    /**
     * @param $_file_description the $_file_description to set
     */
    public function setFile_description($_file_description)
    {
        $this->_file_description = $_file_description;
        return $this;
    }

    /**
     * @return the $_file_description
     */
    public function getfile_description()
    {
        return $this->_file_description;
    }

    /**
     * @param $_file_title the $_file_title to set
     */
    public function setFile_title($_file_title)
    {
        $this->_file_title = $_file_title;
        return $this;
    }

    /**
     * @return the $_file_title
     */
    public function getfile_title()
    {
        return $this->_file_title;
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
     * @param $_ID_file the $_ID_file to set
     */
    public function setID_file($_ID_file)
    {
        $this->_ID_file = $_ID_file;
        return $this;
    }

    /**
     * @return the $_ID_file
     */
    public function getID_file()
    {
        return $this->_ID_file;
    }

    /**
     * @return the $_file_uploaded_date
     */
    public function getfile_uploaded_date()
    {
        return $this->_file_uploaded_date;
    }

    /**
     * @param $_file_uploaded_date the $_file_uploaded_date to set
     */
    public function setFile_uploaded_date($_file_uploaded_date)
    {
        $this->_file_uploaded_date = $_file_uploaded_date;
        return $this;
    }





}