<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Define application ini file constant
defined('INI_FILE')
    || define('INI_FILE', (getenv('INI_FILE') ? getenv('INI_FILE') : APPLICATION_PATH . '/configs/application.ini'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Creating application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

// Bootstrapping resources
$bootstrap = $application->bootstrap()->getBootstrap();
$bootstrap->bootstrap(array('Doctrine'));

// create a instance of EntityManager
$em = Zend_Registry::get('doctrine')->getEntityManager();

use Amedia\Entity\Operation\ClientComment;


//************** added by Aob *****************************************                 
//************** here start the check email process************** 
 
 

//get data from table parameters
$qb = $em->createQueryBuilder();

$qb->add('select', 'p')
   ->add('from', 'Amedia\Entity\Params p');
   
$queryPar = $qb->getQuery();
$resultParams = $queryPar->getArrayResult();

//que the last saved email counter on the param table
$last_count_email  = $resultParams[0]["param_count_email"];


/********Imap Email Configuration*/
$mail = new Zend_Mail_Storage_Imap(array('host'     => 'imap.gmail.com',
                                     'port'     => 993,
                                     'user'     => 'its_tms@amediacreative.com',
                                     'password' => 'am3di41ts', 
                                     'ssl'      => 'SSL'
                                     ));   

$email_count = $mail->countMessages();                                      
                                        
        
if ($email_count > $last_count_email){
    /*parse emai*/
    
        $message = $mail->getMessage($email_count); //get Last email 
        
        $email_subject = $message->subject; //get email  subject
        
        /*Get Status ID from the subject title*/
        $status_id = parse_status_id_from_subject($email_subject, "{", "}");
        $status_id = substr($status_id, 1); //delete 1st chart (#) 
        
        //get from/sender email
        $header = imap_rfc822_parse_headers($mail->getRawHeader($email_count));
        $from_email = $header->from[0]->mailbox . "@" . $header->from[0]->host; 
   
        
        /*****Get Email content*/
        
        $part = $message;
        
        while ($part->isMultipart()) {
            $part = $message->getPart(1);
        }
   
        $pos = strpos($part->getContent(), '--REPLY.ABOVE.THIS.LINE--');  //get key postion
        $content = substr($part->getContent(), 0, $pos);  //get all the content above the key
        
        
   /************** Save count email on params table*****************/
        save_comment($status_id, $from_email, $content, $em); 
   /***************************************************************/
   
    /************** Save count email on params table*****************/
        save_count_email($email_count, $em);
    /***************************************************************/
        
}


function save_count_email($email_count, $em){
    
    
             //get data from table parameters
    $qb = $em->createQueryBuilder();
    
    $qb->add('select', 'p')
       ->add('from', 'Amedia\Entity\Params p');
       
    $queryPar = $qb->getQuery();
    $resultParams = $queryPar->getArrayResult();
    
    $paramData = array();
      
    $params = $em->find('Amedia\Entity\Params', "1");
    
    $paramData[] = array('ID_params'=>$resultParams[0]["ID_params"], 
                    'param_date'=>$resultParams[0]["param_date"], 
                    'param_email'=>$resultParams[0]["param_email"], 
                    'param_email2'=>$resultParams[0]["param_email2"],
                    'param_is_busy'=>$resultParams[0]["param_is_busy"], 
                    'param_count_email'=>$resultParams[0]["param_count_email"]); 
    
    $values = $paramData;
      
    $params->param_count_email = $email_count; //getData
    $params->setData($values);
    $em->persist($params);
    $em->flush();
    
}


function parse_status_id_from_subject($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return substr($string,$ini,$len);
}


function save_comment($id_status, $from_email, $client_comment, $em){
     
    
    $user_id = get_user_id($from_email, $em); // get user ID
   
    
    if (!(isset($user_id))){
        $user_id = 0;
    }else{
        $from_email = "";
    }
    
    
    $comment = new ClientComment();
  
    $comment->ID_status = $id_status;
    $comment->ID_user = $user_id;
    $comment->client_comment = $client_comment;
    $comment->client_email_from = $from_email;
    $comment->client_datetime =  new DateTime("now");
    $comment->client_comment_is_deleted = "no";
    
            
    $em->persist($comment);
    $em->flush();
    
}


function get_user_id($from_email, $em){
     //create sql sentence
    $sentencia = "select ID_user from user where user_email = '". $from_email."'";
    
    $rsm = new Doctrine\ORM\Query\ResultSetMapping;
    $rsm->addEntityResult('Amedia\Entity\User', 'c');
    $rsm->addFieldResult('c', 'ID_user', 'ID_user');
    
    $query = $em->createNativeQuery($sentencia, $rsm);
    
    $userResult = $query->getArrayResult();
    
    return $userResult[0]["ID_user"];
}




 echo 'email saved... '; die();
