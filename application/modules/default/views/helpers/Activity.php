<?php
/**
 *
 * Log Activity Helper
 *
 * @author  Ever Daniel Barreto Rojas
 * @version $Id$
 */
require_once 'Zend/View/Interface.php';
/**
 * Log Activity helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Activity
{

/**
 * @var Zend_View_Interface
 */
    public $view;
    private $fullNameLenght = 25;
    private $bodyLenght = 200;

    /**
     *
     */
    public function activity($activity, $className, $timeOnly = true)
    {
        // Data array for partial view
        $dataPartial = array();

        // Activity row class
        $dataPartial['className'] = trim($className);

        // Show all items by default
        $dataPartial['showTime']       = true;
        $dataPartial['showIcon']       = true;
        $dataPartial['showHead']       = true;
        $dataPartial['showUser']       = true;
        $dataPartial['showAction']     = true;
        $dataPartial['showObject']     = true;
        $dataPartial['showObjectType'] = true;
        $dataPartial['showBody']       = true;

        /**
         * Log Time
         */
        $logTime = new Zend_Date($activity->activity_date, 'YYYY-MM-dd HH:mm:ss');
        $logTime->setTimezone($this->view->userData->user_timezone);
        $logTime->setLocale($this->view->userData->user_locale);
        if ($timeOnly)
            $dataPartial['logTime'] = $logTime->get(Zend_Date::TIME_SHORT);
        else
            $dataPartial['logTime'] = $logTime->get(Zend_Date::DATETIME_SHORT);

        /**
         * Define default Icon (might be override in the object type switch)
         */
        switch ($activity->activity_type)
        {
            case 'add':
                $dataPartial['logIcon'] = 'add';
                $dataPartial['logAction'] = $this->view->translate->_('added');
                break;

            case 'change_avatar':
                $dataPartial['logIcon'] = 'edit-user';
                $dataPartial['logAction'] = $this->view->translate->_('changed his avatar');
                break;

            case 'complete':
                $dataPartial['logIcon'] = 'resolved';
                $dataPartial['logAction'] = $this->view->translate->_('completed');
                break;

            case 'edit':
                $dataPartial['logIcon'] = 'edit';
                $dataPartial['logAction'] = $this->view->translate->_('edited');
                break;

            case 'archive':
                $dataPartial['logIcon'] = 'archive';
                $dataPartial['logAction'] = $this->view->translate->_('archived');
                break;

            case 'lock':
                $dataPartial['logIcon'] = 'lock';
                $dataPartial['logAction'] = $this->view->translate->_('locked');
                break;

            case 'login':
                $dataPartial['logIcon'] = 'sort-dsc';
                $dataPartial['logAction'] = $this->view->translate->_('logged in.');
                break;

            case 'logout':
                $dataPartial['logIcon'] = 'sort-asc';
                $dataPartial['logAction'] = $this->view->translate->_('logged out.');
                break;

            case 'restore':
                $dataPartial['logIcon'] = 'restore';
                $dataPartial['logAction'] = $this->view->translate->_('restored');
                break;

            case 'restorearchived':
                $dataPartial['logIcon'] = 'restore';
                $dataPartial['logAction'] = $this->view->translate->_('restored from the Archive the');
                break;

            case 'subscription':
                $dataPartial['logIcon'] = 'link';
                $dataPartial['logAction'] = $this->view->translate->_('is now subscribed to');
                break;

            case 'trash':
                $dataPartial['logIcon'] = 'trash';
                $dataPartial['logAction'] = $this->view->translate->_('trashed');
                break;

            case 'unlock':
                $dataPartial['logIcon'] = 'unlock';
                $dataPartial['logAction'] = $this->view->translate->_('unlocked');
                break;

            case 'upload_file':
                $dataPartial['logIcon'] = 'add';
                $dataPartial['logAction'] = $this->view->translate->_('uploaded a file.');
                break;

            default:
                $dataPartial['logIcon'] = 'bubble';
                $dataPartial['logAction'] = '';
        }

        /**
         * User Info
         */
        $user = new People_Model_User();
        $user->find($activity->ID_user);
        $userName = $user->user_firstname . ' ' . $user->user_surname;
        $dataPartial['logUser'] = strlen($userName) > $this->fullNameLenght ? substr($userName, 0, $this->fullNameLenght - 3) . '...' : $userName;
        $dataPartial['userLink'] = $this->view->serverUrl('/people/user/view/id/' . $user->ID_user);

        /**
         * Object Type (Comment, Discussion, etc.)
         */
        switch ($activity->activity_object_type)
        {
            case 'comment':
                $comment = new Discussion_Model_Comment();
                $comment->find($activity->ID_object);
                $discussion = new Discussion_Model_Discussion();
                if ($activity->activity_type == 'add')
                {
                    $discussion->find($comment->ID_discussion);
                    $dataPartial['logIcon'] = 'bubble';
                    $dataPartial['logAction'] = $this->view->translate->_('commented on');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $comment->ID_discussion);
                    $dataPartial['logBody'] = strlen($comment->comment) > $this->bodyLenght ? substr($comment->comment, 0, $this->bodyLenght - 3) . '...' : $comment->comment;
                }
                if ($activity->activity_type == 'edit')
                {
                    $discussion->find($comment->ID_discussion);
                    $dataPartial['logAction'] = $this->view->translate->_('edited a comment in');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $comment->ID_discussion);
                    $dataPartial['logBody'] = strlen($comment->comment) > $this->bodyLenght ? substr($comment->comment, 0, $this->bodyLenght - 3) . '...' : $comment->comment;
                }
                if ($activity->activity_type == 'trash')
                {
                    $discussion->find($comment->ID_discussion);
                    $dataPartial['logAction'] = $this->view->translate->_('trashed a comment in');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $comment->ID_discussion);
                }
                break;

            case 'discussion':
                $discussion = new Discussion_Model_Discussion();
                $discussion->find($activity->ID_object);
                if ($activity->activity_type == 'add')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('started');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = strlen($discussion->discussion_description) > $this->bodyLenght ? substr($discussion->discussion_description, 0, $this->bodyLenght - 3) . '...' : $discussion->discussion_description;
                }
                if ($activity->activity_type == 'edit')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('edited');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = '';
                }
                if ($activity->activity_type == 'lock')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('locked');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = '';
                }
                if ($activity->activity_type == 'restore')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('restored');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = '';
                }
                if ($activity->activity_type == 'subscription')
                {
                    if ($activity->activity_result == 'add')
                    {
                        $dataPartial['logIcon'] = 'link';
                        $dataPartial['logAction'] = $this->view->translate->_('is now subscribed to');
                    }
                    if ($activity->activity_result == 'remove')
                    {
                        $dataPartial['logIcon'] = 'break';
                        $dataPartial['logAction'] = $this->view->translate->_('subscription was removed from');
                    }
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = '';
                }
                if ($activity->activity_type == 'trash')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('trashed');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = 'javascript://';
                }
                if ($activity->activity_type == 'unlock')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('unlocked');
                    $dataPartial['logObject'] = $discussion->discussion_summary;
                    $dataPartial['logObjectType'] = $this->view->translate->_('discussion');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/discussion/manage/view/id/' . $discussion->ID_discussion);
                    $dataPartial['logBody'] = '';
                }
                break;

            case 'user':
                $dataPartial['logObjectType'] = 'user';
                $dataPartial['showObject'] = false;
                $dataPartial['showObjectType'] = false;
                $dataPartial['showBody'] = false;
                break;

            case 'yield':
                $dataPartial['showBody'] = false;
                $yield = new Yield_Model_Yield();
                $yield->find($activity->ID_object);
                if ($activity->activity_type == 'add')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('started');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'complete')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('completed');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'edit')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('edited');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'archive')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('archived');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'lock')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('locked');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'restore')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('restored');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'restorearchived')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('restored from the Archive the');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'subscription')
                {
                    if ($activity->activity_result == 'add')
                    {
                        $dataPartial['logIcon'] = 'link';
                        $dataPartial['logAction'] = $this->view->translate->_('is now subscribed to');
                    }
                    if ($activity->activity_result == 'remove')
                    {
                        $dataPartial['logIcon'] = 'break';
                        $dataPartial['logAction'] = $this->view->translate->_('subscription was removed from');
                    }
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                if ($activity->activity_type == 'trash')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('trashed');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = 'javascript://';
                }
                if ($activity->activity_type == 'unlock')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('unlocked');
                    $dataPartial['logObject'] = $yield->yield_style_number;
                    $dataPartial['logObjectType'] = $this->view->translate->_('yield');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/yield/manage/view/id/' . $yield->ID_yield);
                }
                break;

            case 'saleskit':
                $dataPartial['showBody'] = false;
                $salesKit = new Saleskit_Model_Saleskit();
                $salesKit->find($activity->ID_object);
                if ($activity->activity_type == 'add')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('posted');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'edit')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('edited');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'archive')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('archived');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'restore')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('restored');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'restorearchived')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('restored from the Archive the');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'subscription')
                {
                    if ($activity->activity_result == 'add')
                    {
                        $dataPartial['logIcon'] = 'link';
                        $dataPartial['logAction'] = $this->view->translate->_('is now subscribed to');
                    }
                    if ($activity->activity_result == 'remove')
                    {
                        $dataPartial['logIcon'] = 'break';
                        $dataPartial['logAction'] = $this->view->translate->_('subscription was removed from');
                    }
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = $this->view->serverUrl('/saleskit/manage/view/id/' . $salesKit->ID_sales_kit);
                }
                if ($activity->activity_type == 'trash')
                {
                    $dataPartial['logAction'] = $this->view->translate->_('trashed');
                    $dataPartial['logObject'] = $salesKit->sales_kit_name;
                    $dataPartial['logObjectType'] = $this->view->translate->_('sales kit');
                    $dataPartial['logObjectLink'] = 'javascript://';
                }
                break;

            default:
                $dataPartial['logObjectType'] = '';
        }

        return $this->view->partial('partials/logActivity.phtml', $dataPartial);
    }

    /**
     * Sets the view field
     * @param $view Zend_View_Interface
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }
}
