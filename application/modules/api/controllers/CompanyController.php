<?php

class Api_CompanyController extends Zend_Rest_Controller
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
        // Version Node
        $versionElement = $this->response->createElement('version');
        $versionElement->appendChild($this->response->createTextNode($this->version));
        $this->response->rootElement->appendChild($versionElement);

        /* Get Companies */
        $company = new Company_Model_Company();
        $companies = $company->fetchAll("company_is_deleted = 'no'", 'company_name asc');

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
         * Total Row Count
         */
        $totalElement = $this->response->createElement('totalRowCount');
        $totalElement->appendChild($this->response->createTextNode(count($companies)));
        $payloadElement->appendChild($totalElement);
        
        $this->response->rootElement->appendChild($payloadElement);
        
        $companiesElement = $this->response->createElement('companies');
        if (count($companies) > 0)
        {
            foreach($companies as $company)
            {
                $companyElement = $this->response->createElement('company');
                
                $nodeElement = $this->response->createElement('companyID');
                $nodeElement->appendChild($this->response->createTextNode($company->ID_company));
                $companyElement->appendChild($nodeElement);
                
                $nodeElement = $this->response->createElement('companyType');
                $nodeElement->appendChild($this->response->createTextNode(ucwords($company->company_type)));
                $companyElement->appendChild($nodeElement);
                
                $nodeElement = $this->response->createElement('companyName');
                $nodeElement->appendChild($this->response->createTextNode($company->company_name));
                $companyElement->appendChild($nodeElement);
                
                $companiesElement->appendChild($companyElement);
            }
        }
        $payloadElement->appendChild($companiesElement);

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($this->response->saveXML());
    }

    public function getAction()
    {
        $this->getResponse();
        $this->_forward('index');
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