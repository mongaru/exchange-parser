<?php
/*
 * Uploader Controllers
 * Provides SWAP's like file uploader functionality
 */
class UploaderController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
        if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/login');
        $this->translate = Zend_Registry::get('translate');
    }

    public function indexAction()
    {
        // Sets separate Uploader layout
        // $this->_helper->layout()->setLayout('uploader');

        $this->_helper->layout()->disableLayout();

        // Use base url or current url for reset url?
        $baseReset = $this->_request->getParam('base', 'no');
        if ($baseReset == 'yes')
        {
            $patterns = array();
            $patterns[] = '/id\/([0-9]+)\//';
            $patterns[] = '/base\/yes/';

            $replacements = array();
            $replacements[] = '';
            $replacements[] = 'base/no';

            $url = preg_replace($patterns, $replacements, $this->view->serverUrl($this->getRequest()->getRequestUri()));
            $this->view->baseUrlReset = "'" . $url . "'";
        }
        else
            $this->view->baseUrlReset = 'window.location.href';

        // Sets shows error message to false
        $this->view->showErrorMessage = false;

        // Sets file is uploaded to false
        $this->view->fileUploaded = false;

        // Instantiates HTTP File Transfer
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();
        // use Byte string for file sizes
        $fileAdapter->setOptions(array('useByteString' => false));
        // sets files destination
        $fileAdapter->setDestination('uploads');

        // Get File Id
        $storedFileId = $this->_request->getParam('id', 0);
        if (($storedFileId !== 0) && (trim($storedFileId) !== ''))
        {
        // If and Id was provided, try to mimic an already uploaded file
            $storedFile = new People_Model_File();
            $storedFile->find($storedFileId);
            $this->view->fileUploaded = true;
            $this->view->fileId = $storedFile->getID_file();
            $this->view->fileFullName = $storedFile->getfile_name();
            $this->view->fileName = $storedFile->getfile_name();
            if (strlen($this->view->fileName) > 23)
                $this->view->fileName = substr($this->view->fileName, 0, 20) . '...';
            $this->view->fileUploadedMessage = '';
        }

        // Javascript Parent Update Callback
        $this->view->jsCallback = $this->_request->getParam('ucb', '');

        // Javascript Parent Reset Callback
        $this->view->jsResetCallback = $this->_request->getParam('rcb', '');

        $this->view->jsParameterCallback = $this->_request->getParam('jparam', '');

    	/*
     	* File Validations
     	*/
        // Set a min size of 20 and a max size of 8000000 bytes
        $fileAdapter->addValidator('Size', false, array('min' => 20, 'max' => 8000000));

        // Limits the file extensions
        $fileAdapter->addValidator('Extension', false, array('xls', 'xlsx', 'xlsm', 'pdf', 'doc', 'docx', 'jpg', 'gif', 'png', 'sty', 'mar'));

        $files = $fileAdapter->getFileInfo();

        foreach($files as $file => $info)
        {
            // file uploaded?
            if ( ! $fileAdapter->isUploaded($file))
            {
                $this->view->showErrorMessage = true;
                $this->view->errorMessage = $this->translate->_('Please select a file to upload.');
                continue;
            }

            // validators are ok?
            if ( ! $fileAdapter->isValid($file))
            {
                $this->view->showErrorMessage = true;
                $this->view->errorMessage = $this->translate->_('Please select a valid file to upload.');
                continue;
            }

            // rename the file
            $extension = pathinfo($info['name']);
            $fileAdapter->addFilter('Rename', array('target' => 'uploads/' . $extension['filename'] . '_' . Zend_Auth::getInstance()->getIdentity()->ID_user . '_' . date('Ymdhis') . '.' . $extension['extension'], 'overwrite' => true), $file);

            if ($fileAdapter->receive($file))
            {
                $fileData = $fileAdapter->getFileInfo($file);
                $fileInfo = array();
                $fileExtension                 = explode('.', $fileAdapter->getFileName($file, false));
                $isImage                       = in_array(strtolower($fileExtension[1]), array('jpg', 'gif', 'png')) == true ? 'yes' : 'no';
                if ($isImage == 'yes')
                {
                    $imageData = getimagesize($fileAdapter->getFileName($file));
                }
                $fileInfo['ID_user']           = Zend_Auth::getInstance()->getIdentity()->ID_user;
                $fileInfo['file_title']        = $file;
                $fileInfo['file_description']  = $file;
                $fileInfo['file_display']      = 'yes';
                $fileInfo['file_views']        = 0;
                $fileInfo['file_is_image']     = $isImage;
                $fileInfo['file_name']         = $fileAdapter->getFileName($file, false);
                $fileInfo['file_type']         = $fileAdapter->getMimeType($file);
                $fileInfo['file_hash']         = $fileAdapter->getHash('crc32', $file);
                $fileInfo['file_path']         = str_replace($fileAdapter->getFileName($file, false), '', $fileAdapter->getFileName($file));
                $fileInfo['file_full_path']    = $fileAdapter->getFileName($file);
                $fileInfo['file_raw_name']     = $fileAdapter->getFileName($file, false);
                $fileInfo['file_orig_name']    = $fileAdapter->getFileName($file, false);
                $fileInfo['file_extension']    = $fileExtension[count($fileExtension) - 1];
                $fileInfo['file_size']         = $fileAdapter->getFileSize($file);
                $fileInfo['file_image_width']  = 0;
                $fileInfo['file_image_height'] = 0;
                $fileInfo['file_image_type']   = '';
                if ($isImage == 'yes')
                {
                    $fileInfo['file_image_width']  = $imageData[0];
                    $fileInfo['file_image_height'] = $imageData[1];
                    $fileInfo['file_image_type']   = $imageData['mime'];
                }
                $fileInfo['file_uploaded_date']= date('Y-m-d H:i:s');
                $fileModel = new People_Model_File($fileInfo);
                $fileId = $fileModel->save();
                $this->view->fileUploaded = true;
                $this->view->fileId = $fileId;
                $this->view->fileFullName = $fileInfo['file_name'];
                $this->view->fileName = $fileInfo['file_name'];
                if (strlen($fileInfo['file_name']) > 23)
                    $this->view->fileName = substr($fileInfo['file_name'], 0, 20) . '...';
                $this->view->fileUploadedMessage = $this->translate->_('Uploaded.');
                if (trim($this->view->jsParameterCallback) == '')
                    $this->view->jsParameterCallback = $fileId;

                // Log Activity
                $activity = new People_Model_Activity();
                $activity->setID_object($fileId);
                $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
                $activity->setActivity_object_type('user');
                $activity->setActivity_type('upload_file');
                $activity->setActivity_result('success');
                $activity->setActivity_date(date('Y-m-d H:i:s'));
                $activity->save();
            }
        }
    }

    public function uploadAvatarAction()
    {     
        
        // Instantiates HTTP File Transfer
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();
        // use Byte string for file sizes
        $fileAdapter->setOptions(array('useByteString' => false));
        // sets files destination
        $fileAdapter->setDestination('uploads');
        $errorMessage = '';
        $fileId = '';
        /*
         * File Validations
         */
        // Set a min size of 20 and a max size of 8000000 bytes
        $fileAdapter->addValidator('Size', false, array('min' => 20, 'max' => 8000000));

        // Limits the file extensions
        $fileAdapter->addValidator('Extension', false, array('xls', 'xlsx', 'pdf', 'doc', 'docx', 'jpg', 'gif', 'png', 'sty', 'mar'));
        $files = $fileAdapter->getFileInfo();
        
        foreach($files as $file => $info)
        {
            // file uploaded?
            if ( ! $fileAdapter->isUploaded($file))
            {
                $errorMessage = $this->translate->_('Please select a file to upload.');
                continue;
            }

            // validators are ok?
            if ( ! $fileAdapter->isValid($file))
            {
                $errorMessage = $this->translate->_('Please select a valid file to upload.');
                continue;
            }

            // rename the file
            $extension = pathinfo($info['name']);
            $fileAdapter->addFilter('Rename', array('target' => 'uploads/' . $extension['filename'] . '_' . Zend_Auth::getInstance()->getIdentity()->ID_user . '_' . date('Ymdhis') . '.' . $extension['extension'], 'overwrite' => true), $file);

            if ($fileAdapter->receive($file))
            {
                $fileData = $fileAdapter->getFileInfo($file);
                $fileInfo = array();
                $fileExtension                 = explode('.', $fileAdapter->getFileName($file, false));
                $isImage                       = in_array(strtolower($fileExtension[1]), array('jpg', 'gif', 'png')) == true ? 'yes' : 'no';
                if ($isImage == 'yes')
                {
                    $imageData = getimagesize($fileAdapter->getFileName($file));
                }
                $fileInfo['ID_user']           = Zend_Auth::getInstance()->getIdentity()->ID_user;
                $fileInfo['file_title']        = $file;
                $fileInfo['file_description']  = $file;
                $fileInfo['file_display']      = 'yes';
                $fileInfo['file_views']        = 0;
                $fileInfo['file_is_image']     = $isImage;
                $fileInfo['file_name']         = $fileAdapter->getFileName($file, false);
                $fileInfo['file_type']         = $fileAdapter->getMimeType($file);
                $fileInfo['file_hash']         = $fileAdapter->getHash('crc32', $file);
                $fileInfo['file_path']         = str_replace($fileAdapter->getFileName($file, false), '', $fileAdapter->getFileName($file));
                $fileInfo['file_full_path']    = $fileAdapter->getFileName($file);
                $fileInfo['file_raw_name']     = $fileAdapter->getFileName($file, false);
                $fileInfo['file_orig_name']    = $fileAdapter->getFileName($file, false);
                $fileInfo['file_extension']    = $fileExtension[count($fileExtension) - 1];
                $fileInfo['file_size']         = $fileAdapter->getFileSize($file);
                $fileInfo['file_image_width']  = 0;
                $fileInfo['file_image_height'] = 0;
                $fileInfo['file_image_type']   = '';
                
                $avatarFile = '/images/client128.gif';
                if ($isImage == 'yes')
                {
                    $fileInfo['file_image_width']  = $imageData[0];
                    $fileInfo['file_image_height'] = $imageData[1];
                    $fileInfo['file_image_type']   = $imageData['mime'];

                    $imageLib = Amedia_ImageLib::getInstance();
                    $imageLib->setSourceFile($fileInfo['file_full_path'], $fileInfo['file_name']);
                    $avatarFile = $this->view->serverUrl($imageLib->resize('128x128')->getUrl());
                }

                $fileInfo['file_uploaded_date'] = date('Y-m-d H:i:s');
                
                $fileModel = new People_Model_File($fileInfo);
                $fileId = $fileModel->save();

                if ($this->_request->getPost('ID_user_avatar') != '')
                {
                    $user = new People_Model_UserAuth();
                    $user->find($this->_request->getPost('ID_user_avatar'));
                        
                    $user->user_avatar_file = $fileId;
                    $user->save();
                }

                //$fileId = $fileId;
                //$this->view->fileFullName = $fileInfo['file_name'];
                //$this->view->fileName = $fileInfo['file_name'];                

                // Log Activity
                $activity = new People_Model_Activity();
                $activity->setID_object($fileId);
                $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
                $activity->setActivity_object_type('user');
                $activity->setActivity_type('upload_file');
                $activity->setActivity_result('success');
                $activity->setActivity_date(date('Y-m-d H:i:s'));
                $activity->save();

                // Log Activity
                //$activity = Activity::createNowActivity(Zend_Auth::getInstance()->getIdentity()->ID_user, $fileId, 'user', 'upload_file', 'success');
                //$this->em->persist($activity);
                //$this->em->flush();
            }
        }

        if ($errorMessage == '')
            $this->_helper->json(array('status' => 'ok', 'data' => array('ID_file' => $fileId, 'file_path' => $avatarFile)));
        else
            $this->_helper->json(array('status' => 'error', 'message' => $errorMessage));
    }
}

