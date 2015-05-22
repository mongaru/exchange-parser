<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment, (I am only running this in development)
define('APPLICATION_ENV', 'development');

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

$em = Zend_Registry::get('doctrine')->getEntityManager();

$qb = $em->createQueryBuilder();

$qb->add('select', 'c')
   ->add('from', 'Amedia\Entity\Company\Carrier c')
   ->add('where', 'c.carrier_lat is null')
   ->add('where', 'c.carrier_lng is null')
   //->add('orderBy', 'u.name ASC');
   //->setParameter('identifier', 100)
   ->setMaxResults(10);

$query = $qb->getQuery();
$r = $query->getArrayResult();

echo '<pre>'; var_dump($r); echo '</pre>'; die();

//$a = new Amedia\Entity\Company\Company();

//echo '<pre>'; var_dump($a->getData()); echo '</pre>'; die();