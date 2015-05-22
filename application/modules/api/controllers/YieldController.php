<?php

class Api_YieldController extends Zend_Rest_Controller
{

    /**
     *
     * @var DOMDocument
     */
    private $response;
    /**
     *
     * @var integer
     */
    private $fullNameLenght = 25;

    public function init()
    {
        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        //$bootstrap = $this->getInvokeArg('bootstrap');
        //$options = $bootstrap->getOption('resources');
        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('index', array('xml', 'json'))->initContext('xml');
        $contextSwitch->addActionContext('get', array('xml', 'json'))->initContext('xml');
        $contextSwitch->addActionContext('post', array('xml', 'json'))->initContext('xml');

        $this->version = "1.0";

        // Create DOM Response
        $this->response = new DOMDocument();
        $this->response->formatOutput = true;
        $this->response->rootElement = $this->response->createElement('response');
        $this->response->appendChild($this->response->rootElement);
    }

    public function indexAction()
    {
        $this->getResponse()
            ->appendBody($this->response->saveXML());
    }

    public function getAction()
    {


        // Version Node
        $versionElement = $this->response->createElement('version');
        $versionElement->appendChild($this->response->createTextNode($this->version));
        $this->response->rootElement->appendChild($versionElement);

        $yieldId = (int) $this->_request->getParam('id', 0);

        if ($yieldId > 0)
        {
            /* Get Yield Data */
            $yield = new Yield_Model_Yield();
            $yield->find($yieldId);

            // Subscribers
            $subscribers = new People_Model_Subscriber();
            $yieldSubscribers = $subscribers->fetchSubscribers($yieldId);

            // Mini Markers
            $miniMarkers = new Yield_Model_MiniMarker();
            $yieldMiniMarkers = $miniMarkers->fetchByYield($yieldId);

            // Status and Message Nodes
            $statusElement = $this->response->createElement('status');
            $statusElement->appendChild($this->response->createTextNode('success'));
            $this->response->rootElement->appendChild($statusElement);

            $messageElement = $this->response->createElement('message');
            $messageElement->appendChild($this->response->createTextNode(''));
            $this->response->rootElement->appendChild($messageElement);


            // Payload Root Node
            $payloadElement = $this->response->createElement('payload');

            // Payload Data

            /**
             * Created
             */
            $nodeElement = $this->response->createElement('createdOn');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_created));
            $payloadElement->appendChild($nodeElement);

            $createdOn = new Zend_Date($yield->yield_created, 'YYYY-mm-dd HH:mm:ss');
            $createdOn->setTimezone('America/Los_Angeles');
            $createdOn->setLocale('en_US');
            $createdBy = 'By ' . $yield->pattern_maker->user_firstname . ' ' . $yield->pattern_maker->user_surname . ' on ' . $createdOn->get(Zend_Date::DATE_MEDIUM) . ' at ' . $createdOn->get(Zend_Date::TIME_MEDIUM);
            $nodeElement = $this->response->createElement('createdBy');
            $nodeElement->appendChild($this->response->createTextNode($createdBy));
            $payloadElement->appendChild($nodeElement);

            $nodeElement = $this->response->createElement('updatedOn');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_updated));
            $payloadElement->appendChild($nodeElement);

            /**
             * Style Number
             */
            $nodeElement = $this->response->createElement('styleNumber');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_style_number));
            $payloadElement->appendChild($nodeElement);

            /**
             * Style Type
             */
            $styleType = new Yield_Model_StyleType();
            $styleType->find($yield->ID_style_type);
            $nodeElement = $this->response->createElement('styleType');
            $nodeElement->appendChild($this->response->createTextNode($styleType->style_name));
            $payloadElement->appendChild($nodeElement);

            /**
             * Body Type
             */
            $bodyType = new Yield_Model_BodyType();
            $bodyType->find($yield->ID_body_type);
            $nodeElement = $this->response->createElement('bodyType');
            $nodeElement->appendChild($this->response->createTextNode($bodyType->body_name));
            $payloadElement->appendChild($nodeElement);

            /**
             * User Responsible
             */
            $responsibleElement = $this->response->createElement('responsible');
            
            $nodeElement = $this->response->createElement('ID');
            $nodeElement->appendChild($this->response->createTextNode(strip_tags($yield->yield_responsible)));
            $responsibleElement->appendChild($nodeElement);

            $userResponsible = new People_Model_User();
            $userResponsible->find($yield->yield_responsible);
            $nodeElement = $this->response->createElement('fullname');
            $nodeElement->appendChild($this->response->createTextNode($userResponsible->user_firstname . ' ' . $userResponsible->user_surname));
            $responsibleElement->appendChild($nodeElement);
            
            $payloadElement->appendChild($responsibleElement);

            /**
             * Completed
             */
            $isCompleted = 'no';
            if ($yield->yield_status == 1)
                $isCompleted = 'yes';
            $nodeElement = $this->response->createElement('isCompleted');
            $nodeElement->appendChild($this->response->createTextNode($isCompleted));
            $payloadElement->appendChild($nodeElement);

            $nodeElement = $this->response->createElement('completedBy');
            if ($yield->yield_status == 1)
            {
                $completedUser = new People_Model_User();
                $completedUser->find($yield->yield_status_by);
                $nodeElement->appendChild($this->response->createTextNode($completedUser->user_firstname . ' ' . $completedUser->user_surname));
            }
            else
            {
                $nodeElement->appendChild($this->response->createTextNode(''));
            }
            $payloadElement->appendChild($nodeElement);

            $nodeElement = $this->response->createElement('completedOn');
            if ($yield->yield_status == 1)
            {
                $nodeElement->appendChild($this->response->createTextNode($yield->yield_status_changed));
            }
            else
            {
                $nodeElement->appendChild($this->response->createTextNode(''));
            }
            $payloadElement->appendChild($nodeElement);

            $nodeElement = $this->response->createElement('completedNotes');
            if ($yield->yield_status == 1)
            {
                $nodeElement->appendChild($this->response->createTextNode(strip_tags($yield->yield_note_completed)));
            }
            else
            {
                $nodeElement->appendChild($this->response->createTextNode(''));
            }
            $payloadElement->appendChild($nodeElement);

            /**
             * Locked
             */
            $nodeElement = $this->response->createElement('isLocked');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_is_locked));
            $payloadElement->appendChild($nodeElement);

            $nodeElement = $this->response->createElement('lockedBy');
            if ($yield->yield_is_locked == 'yes')
            {
                $lockedUser = new People_Model_User();
                $lockedUser->find($yield->yield_locked_by);
                $nodeElement->appendChild($this->response->createTextNode($lockedUser->user_firstname . ' ' . $lockedUser->user_surname));
            }
            else
            {
                $nodeElement->appendChild($this->response->createTextNode(''));
            }
            $payloadElement->appendChild($nodeElement);

            /**
             * Notes
             */
            $nodeElement = $this->response->createElement('notes');
            $nodeElement->appendChild($this->response->createTextNode(strip_tags($yield->yield_note)));
            $payloadElement->appendChild($nodeElement);

            $this->response->rootElement->appendChild($payloadElement);

            /**
             * Payload / Subscribers
             */
            $subscribersElement = $this->response->createElement('subscribers');

            $subscribers = new People_Model_Subscriber();
            $subcribers = $subscribers->fetchSubscribers($yieldId);

            foreach ($subcribers as $row)
            {
                $subscribedUser = new People_Model_User();
                $subscribedUser->find($row->ID_user);

                $subscriberElement = $this->response->createElement('subscriber');

                $rowID = $this->response->createElement('ID');
                $rowID->appendChild($this->response->createTextNode($subscribedUser->ID_user));
                $subscriberElement->appendChild($rowID);

                $rowType = $this->response->createElement('fullName');
                $rowType->appendChild($this->response->createTextNode($subscribedUser->user_firstname . ' ' . $subscribedUser->user_surname));
                $subscriberElement->appendChild($rowType);
                
                $responsible = ($yield->yield_responsible == $subscribedUser->ID_user) ? 'Responsible' : '';
                $rowResponsible = $this->response->createElement('responsible');
                $rowResponsible->appendChild($this->response->createTextNode($responsible));
                $subscriberElement->appendChild($rowResponsible);

                $subscribersElement->appendChild($subscriberElement);
            }
            $payloadElement->appendChild($subscribersElement);

            /**
             * Payload / Attachments
             */
            $minimarkersElement = $this->response->createElement('minimarkers');

            $miniMarkers = new Yield_Model_MiniMarker();
            $yieldMiniMarkers = $miniMarkers->fetchByYield($yieldId);

            $fileNameLength = 25;

            foreach ($yieldMiniMarkers as $row)
            {
                $miniMarkerFile = new People_Model_File();
                $miniMarkerFile->find($row->ID_file);

                $minimarkerElement = $this->response->createElement('minimarker');

                $rowID = $this->response->createElement('ID');
                $rowID->appendChild($this->response->createTextNode($row->ID_file));
                $minimarkerElement->appendChild($rowID);

                $rowMarkerEfficiency = $this->response->createElement('markerEfficiency');
                $rowMarkerEfficiency->appendChild($this->response->createTextNode($row->marker_efficiency));
                $minimarkerElement->appendChild($rowMarkerEfficiency);

                $miniMarkerFlag = $row->marker_efficiency >= 70 ? 'green' : 'red';
                $rowMarkerFlag = $this->response->createElement('markerFlag');
                $rowMarkerFlag->appendChild($this->response->createTextNode($miniMarkerFlag));
                $minimarkerElement->appendChild($rowMarkerFlag);

                $rowFileName = $this->response->createElement('fileName');
                $rowFileName->appendChild($this->response->createTextNode($miniMarkerFile->file_name));
                $minimarkerElement->appendChild($rowFileName);
                
                $rowFileExtension = $this->response->createElement('fileExtension');
                $rowFileExtension->appendChild($this->response->createTextNode($miniMarkerFile->file_extension));
                $minimarkerElement->appendChild($rowFileExtension);

                $rowFullPath = $this->response->createElement('fullPath');
                $rowFullPath->appendChild($this->response->createTextNode($this->view->serverUrl('/uploads/' . $miniMarkerFile->file_name)));
                $minimarkerElement->appendChild($rowFullPath);
                
                $uploadedBy = new People_Model_User();
                $uploadedBy->find($miniMarkerFile->ID_user);

                $uploadedOn = new Zend_Date($miniMarkerFile->file_uploaded_date, 'YYYY-MM-dd HH:mm:ss');
                $uploadedOn->setTimezone('America/Los_Angeles');
                $uploadedOn->setLocale('en_US');
                $when = $uploadedOn->get(Zend_Date::DATE_MEDIUM) . ' at ' . $uploadedOn->get(Zend_Date::TIME_SHORT);
                $uploadedBy = 'By ' . $uploadedBy->user_firstname . ' ' . $uploadedBy->user_surname . ' on ' . $when;
                
                $rowUploadedBy = $this->response->createElement('uploadedBy');
                $rowUploadedBy->appendChild($this->response->createTextNode($uploadedBy));
                $minimarkerElement->appendChild($rowUploadedBy);

                $minimarkersElement->appendChild($minimarkerElement);
            }
            $payloadElement->appendChild($minimarkersElement);

            /**
             * Pattern files
             */
            
            /**
             * Pattern Card
             */
            $patternCardElement = $this->response->createElement('patternCard');
            
            $patternCard = new People_Model_File();
            $patternCard->find($yield->yield_pattern_card);

            $uploadedBy = new People_Model_User();
            $uploadedBy->find($patternCard->ID_user);

            $uploadedOn = new Zend_Date($patternCard->file_uploaded_date, 'YYYY-MM-dd HH:mm:ss');
            $uploadedOn->setTimezone('America/Los_Angeles');
            $uploadedOn->setLocale('en_US');
            $when = $uploadedOn->get(Zend_Date::DATE_MEDIUM) . ' at ' . $uploadedOn->get(Zend_Date::TIME_SHORT);

            $nodeElement = $this->response->createElement('ID');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_pattern_card));
            $patternCardElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fileName');
            $nodeElement->appendChild($this->response->createTextNode($patternCard->file_name));
            $patternCardElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fileExtension');
            $nodeElement->appendChild($this->response->createTextNode($patternCard->file_extension));
            $patternCardElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fullPath');
            $nodeElement->appendChild($this->response->createTextNode($this->view->serverUrl('/uploads/' . $patternCard->file_name)));
            $patternCardElement->appendChild($nodeElement);

            $uploadedBy = 'By ' . $uploadedBy->user_firstname . ' ' . $uploadedBy->user_surname . ' on ' . $when;
            $nodeElement = $this->response->createElement('uploadedBy');
            $nodeElement->appendChild($this->response->createTextNode($uploadedBy));
            $patternCardElement->appendChild($nodeElement);
            
            $payloadElement->appendChild($patternCardElement);

            /**
             * Pattern
             */
            $patternElement = $this->response->createElement('pattern');
            
            $pattern = new People_Model_File();
            $pattern->find($yield->yield_pattern);

            $uploadedBy = new People_Model_User();
            $uploadedBy->find($pattern->ID_user);

            $uploadedOn = new Zend_Date($pattern->file_uploaded_date, 'YYYY-MM-dd HH:mm:ss');
            $uploadedOn->setTimezone('America/Los_Angeles');
            $uploadedOn->setLocale('en_US');
            $when = $uploadedOn->get(Zend_Date::DATE_MEDIUM) . ' at ' . $uploadedOn->get(Zend_Date::TIME_SHORT);

            $nodeElement = $this->response->createElement('ID');
            $nodeElement->appendChild($this->response->createTextNode($yield->yield_pattern_card));
            $patternElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fileName');
            $nodeElement->appendChild($this->response->createTextNode($pattern->file_name));
            $patternElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fileExtension');
            $nodeElement->appendChild($this->response->createTextNode($pattern->file_extension));
            $patternElement->appendChild($nodeElement);
            
            $nodeElement = $this->response->createElement('fullPath');
            $nodeElement->appendChild($this->response->createTextNode($this->view->serverUrl('/uploads/' . $pattern->file_name)));
            $patternElement->appendChild($nodeElement);

            $uploadedBy = 'By ' . $uploadedBy->user_firstname . ' ' . $uploadedBy->user_surname . ' on ' . $when;
            $nodeElement = $this->response->createElement('uploadedBy');
            $nodeElement->appendChild($this->response->createTextNode($uploadedBy));
            $patternElement->appendChild($nodeElement);
            
            $payloadElement->appendChild($patternElement);
            
        }
        else
        {
            // Status Node
            $statusElement = $this->response->createElement('status');
            $statusElement->appendChild($this->response->createTextNode('error'));
            $this->response->rootElement->appendChild($statusElement);

            // Message Node
            $messageElement = $this->response->createElement('message');
            $messageElement->appendChild($this->response->createTextNode('Invalid Yield ID provided.'));
            $this->response->rootElement->appendChild($messageElement);
        }


        $this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($this->response->saveXML());
    }

    public function postAction()
    {
        $this->getResponse();
        $this->_forward('index');
    }

    public function putAction()
    {
        $this->getResponse();
        $this->_forward('index');
    }

    public function deleteAction()
    {
        $this->getResponse();
        $this->_forward('index');
    }

}