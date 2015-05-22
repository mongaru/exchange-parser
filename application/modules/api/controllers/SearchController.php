<?php

class Api_SearchController extends Zend_Rest_Controller
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
        $this->getResponse()
            ->setHttpResponseCode(200);
        $this->_forward('index');
    }

    public function postAction()
    {
        $queryTerm = $this->_request->getParam('query', '');
        $page = $this->_request->getParam('page', 1);
        $orderType = $this->_request->getParam('order_type', 'desc');
        $orderBy = 'created ' . $orderType;

        $searchOptions['query'] = $queryTerm;

        $searchOptions['module_yield'] = 'yes';
        $searchOptions['module_archivedyield'] = 'no';
        $searchOptions['module_discussion'] = 'no';
        $searchOptions['module_comment'] = 'no';
        $searchOptions['module_saleskit'] = 'no';
        $searchOptions['module_archivedsaleskit'] = 'no';
        $searchOptions['module_costing'] = 'no';
        $searchOptions['period'] = '';
        $searchOptions['role'] = '';

        $search = new Search_Model_Search();
        $paginator = Zend_Paginator::factory($search->fetchForPagination($searchOptions, $orderBy));
        $paginator->setCache(Zend_Registry::get('fileCache'));
        $paginator->setItemCountPerPage(50);
        $paginator->setCurrentPageNumber($page);

        // Status Node
        $statusElement = $this->response->createElement('status');
        $statusElement->appendChild($this->response->createTextNode('success'));
        $this->response->rootElement->appendChild($statusElement);

        // Message Node
        $messageElement = $this->response->createElement('message');
        $messageElement->appendChild($this->response->createTextNode($queryTerm));
        $this->response->rootElement->appendChild($messageElement);

        // Version Node
        $versionElement = $this->response->createElement('version');
        $versionElement->appendChild($this->response->createTextNode($this->version));
        $this->response->rootElement->appendChild($versionElement);

        // Payload Root Node
        $payloadElement = $this->response->createElement('payload');

        // Payload / Row Count node
        $totalElement = $this->response->createElement('currentRowCount');
        $totalElement->appendChild($this->response->createTextNode($paginator->getCurrentItemCount()));
        $payloadElement->appendChild($totalElement);
        
        $totalElement = $this->response->createElement('currentPageNumber');
        $totalElement->appendChild($this->response->createTextNode($paginator->getCurrentPageNumber()));
        $payloadElement->appendChild($totalElement);
        
        $totalElement = $this->response->createElement('defaultRowCountPerPage');
        $totalElement->appendChild($this->response->createTextNode($paginator->getDefaultItemCountPerPage()));
        $payloadElement->appendChild($totalElement);
        
        $totalElement = $this->response->createElement('rowCountPerPage');
        $totalElement->appendChild($this->response->createTextNode($paginator->getItemCountPerPage()));
        $payloadElement->appendChild($totalElement);
        
        $totalElement = $this->response->createElement('totalRowCount');
        $totalElement->appendChild($this->response->createTextNode($paginator->getTotalItemCount()));
        $payloadElement->appendChild($totalElement);
        
        $totalElement = $this->response->createElement('totalPages');
        $totalElement->appendChild($this->response->createTextNode($paginator->count()));
        $payloadElement->appendChild($totalElement);
        
        $this->response->rootElement->appendChild($payloadElement);

        // Payload / Rows root node
        $rowsElement = $this->response->createElement('rows');

        foreach ($paginator as $result)
        {
            $rowElement = $this->response->createElement('row');

            $rowID = $this->response->createElement('ID');
            $rowID->appendChild($this->response->createTextNode($result['ID']));
            $rowElement->appendChild($rowID);

            $rowType = $this->response->createElement('type');
            $rowType->appendChild($this->response->createTextNode($result['type']));
            $rowElement->appendChild($rowType);

            $rowTitle = $this->response->createElement('title');
            $rowTitle->appendChild($this->response->createTextNode($result['title']));
            $rowElement->appendChild($rowTitle);

            if (strlen($result['description']) > 200)
                $result['description'] = substr(strip_tags($this->view->escape($result['description'])), 0, 200);

            $rowBody = $this->response->createElement('body');
            $rowBody->appendChild($this->response->createTextNode($result['description']));
            $rowElement->appendChild($rowBody);

            $resultCreated = new Zend_Date($result['created'], 'YYYY-MM-dd HH:mm:ss');
            $resultCreated->setTimezone('America/Los_Angeles');
            $resultCreated->setLocale('en_US');
            if ($resultCreated->isToday())
                $rowDate = 'Today';
            elseif ($resultCreated->isYesterday())
                $rowDate = 'Yesterday';
            else
                //$rowDate = $this->view->timespan($resultCreated->getTimestamp()) . ' ago';
                $rowDate = $resultCreated->get(Zend_Date::DATE_MEDIUM) . ' at ' . $resultCreated->get(Zend_Date::TIME_MEDIUM);

            $userFullName = strlen($result['user_fullname']) > $this->fullNameLenght ? substr($result['user_fullname'], 0, $this->fullNameLenght - 3) . '...' : $result['user_fullname'];
            $rowTimespan = $this->response->createElement('timespan');
            $rowTimespan->appendChild($this->response->createTextNode($rowDate . ' by ' . $userFullName));
            $rowElement->appendChild($rowTimespan);
            
            $rowDateTime = $this->response->createElement('datetime');
            $rowDateTime->appendChild($this->response->createTextNode($resultCreated->get(Zend_Date::TIME_SHORT)));
            $rowElement->appendChild($rowDateTime);

            // User
            //$rowUser = $this->response->createElement('user');
            
            $rowUserID = $this->response->createElement('userID');
            $rowUserID->appendChild($this->response->createTextNode($result['ID_user']));
            //$rowUser->appendChild($rowUserID);
            $rowElement->appendChild($rowUserID);
            
            //$userFullName = strlen($result['user_fullname']) > $this->fullNameLenght ? substr($result['user_fullname'], 0, $this->fullNameLenght - 3) . '...' : $result['user_fullname'];
            $rowUserFullName = $this->response->createElement('userFullName');
            $rowUserFullName->appendChild($this->response->createTextNode($userFullName));
            //$rowUser->appendChild($rowUserFullName);
            $rowElement->appendChild($rowUserFullName);
            
            //$rowElement->appendChild($rowUser);

            $rowsElement->appendChild($rowElement);
        }
        $payloadElement->appendChild($rowsElement);

        $this->getResponse()
            ->setHttpResponseCode(200)
            ->appendBody($this->response->saveXML());
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