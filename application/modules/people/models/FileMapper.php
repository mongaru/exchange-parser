<?php
// application/modules/people/models/FileMapper.php

class People_Model_FileMapper
{
    protected $_dbTable;
    protected $_fileCache;

    public function __construct()
    {
        $this->_fileCache = Zend_Registry::get('fileCache');
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable))
        {
            $dbTable = new $dbTable();
        }
        if ( ! $dbTable instanceof Zend_Db_Table_Abstract)
        {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('People_Model_DbTable_File');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_File $file)
    {
        $data = array(
                'ID_file'   => $file->getID_file(),
                'ID_user'   => $file->getID_user(),
                'file_title' => $file->getfile_title(),
                'file_description' => $file->getfile_description(),
                'file_display' => $file->getfile_display(),
                'file_views' => $file->getfile_views(),
                'file_is_image' => $file->getfile_is_image(),
                'file_name' => $file->getfile_name(),
                'file_type' => $file->getfile_type(),
                'file_path' => $file->getfile_path(),
                'file_full_path' => $file->getfile_full_path(),
                'file_raw_name' => $file->getfile_raw_name(),
                'file_orig_name' => $file->getfile_orig_name(),
                'file_extension' => $file->getfile_extension(),
                'file_size' => $file->getfile_size(),
                'file_image_width' => $file->getfile_image_width(),
                'file_image_height' => $file->getfile_image_height(),
                'file_image_type' => $file->getfile_image_type(),
                'file_uploaded_date' => $file->getfile_uploaded_date()
        );

        if (null === ($id = $file->getID_file()))
        {
            unset($data['ID_file']);
            $this->getDbTable()->insert($data);
        } else
        {
            $this->getDbTable()->update($data, array('ID_file = ?' => $id));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('file'));
    }

    /**
     *
     * @param People_Model_File $file
     */
    public function delete(People_Model_File $file)
    {
        if (file_exists($file->file_full_path)) @unlink($file->file_full_path);
        $this->getDbTable()->delete(array('ID_file = ?' => $file->ID_file));
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('file'));
    }

    public function find($ID_file, People_Model_File $file)
    {
        $result = $this->getDbTable()->find($ID_file);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $file->setID_file($row->ID_file)
                ->setID_user($row->ID_user)
                ->setFile_title($row->file_title)
                ->setFile_description($row->file_description)
                ->setFile_display($row->file_display)
                ->setFile_views($row->file_views)
                ->setFile_is_image($row->file_is_image)
                ->setFile_name($row->file_name)
                ->setFile_type($row->file_type)
                ->setFile_path($row->file_path)
                ->setFile_full_path($row->file_full_path)
                ->setFile_raw_name($row->file_raw_name)
                ->setFile_orig_name($row->file_orig_name)
                ->setFile_extension($row->file_extension)
                ->setFile_size($row->file_size)
                ->setFile_image_width($row->file_image_width)
                ->setFile_image_height($row->file_image_height)
                ->setFile_image_type($row->file_image_type)
                ->setFile_uploaded_date($row->file_uploaded_date)
                ->setMapper($this);
        return $file;
    }

    public function findAvatar($ID_user, People_Model_File $file)
    {
        $row= $this->getDbTable()->fetchRow('ID_user = ' . $ID_user . ' AND file_title LIKE '. "'Avatar'" . ' AND file_display = '. "'yes'" . ' AND file_is_image = ' . "'yes'");
        if (0 == count($row))
        {
            return;
        }
        $file->setID_file($row->ID_file)
                ->setID_user($row->ID_user)
                ->setFile_title($row->file_title)
                ->setFile_description($row->file_description)
                ->setFile_display($row->file_display)
                ->setFile_views($row->file_views)
                ->setFile_is_image($row->file_is_image)
                ->setFile_name($row->file_name)
                ->setFile_type($row->file_type)
                ->setFile_path($row->file_path)
                ->setFile_full_path($row->file_full_path)
                ->setFile_raw_name($row->file_raw_name)
                ->setFile_orig_name($row->file_orig_name)
                ->setFile_extension($row->file_extension)
                ->setFile_size($row->file_size)
                ->setFile_image_width($row->file_image_width)
                ->setFile_image_height($row->file_image_height)
                ->setFile_image_type($row->file_image_type)
                ->setFile_uploaded_date($row->file_uploaded_date)
                ->setMapper($this);
        return $file;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'FileFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
            $files = array();
            foreach($resultSet as $row)
            {
                $file = new People_Model_File();
                $file->setID_file($row->ID_file)
                        ->setID_user($row->ID_user)
                        ->setFile_title($row->file_title)
                        ->setFile_description($row->file_description)
                        ->setFile_display($row->file_display)
                        ->setFile_views($row->file_views)
                        ->setFile_is_image($row->file_is_image)
                        ->setFile_name($row->file_name)
                        ->setFile_type($row->file_type)
                        ->setFile_path($row->file_path)
                        ->setFile_full_path($row->file_full_path)
                        ->setFile_raw_name($row->file_raw_name)
                        ->setFile_orig_name($row->file_orig_name)
                        ->setFile_extension($row->file_extension)
                        ->setFile_size($row->file_size)
                        ->setFile_image_width($row->file_image_width)
                        ->setFile_image_height($row->file_image_height)
                        ->setFile_image_type($row->file_image_type)
                        ->setFile_uploaded_date($row->file_uploaded_date)
                        ->setMapper($this);
                $files[] = $file;
            }
            $this->_fileCache->save($files, $cacheId, array('file', 'filefetchall'));
            return $files;
        }
        else return $cache;
    }

}