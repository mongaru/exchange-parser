<?php
/**
 *
 * @author Ever Daniel
 * @version 
 */
require_once 'Zend/View/Interface.php';

/**
 * Timespan helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_Timespan
{
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 *  
	 */
	public function timespan($seconds = 1, $time = '')
	{
		
		if ( ! is_numeric($seconds))
		{
			$seconds = 1;
		}
		
		if ( ! is_numeric($time))
		{
			$time = time();
		}
		
		if ($time <= $seconds)
		{
			$seconds = 1;
		}
		else
		{
			$seconds = $time - $seconds;
		}
		
		$str = '';
		$years = floor($seconds / 31536000);
	
		if ($years > 0)
		{	
			$str .= $years . ' ' . (($years	> 1) ? $this->view->translate('years') : $this->view->translate('year')) . ', ';
		}	
	
		$seconds -= $years * 31536000;
		$months = floor($seconds / 2628000);
	
		if ($years > 0 OR $months > 0)
		{
			if ($months > 0)
			{	
				$str .= $months . ' ' . (($months	> 1) ? $this->view->translate('months') : $this->view->translate('month')) . ', ';
			}	
	
			$seconds -= $months * 2628000;
		}

		$weeks = floor($seconds / 604800);
	
		if ($years > 0 OR $months > 0 OR $weeks > 0)
		{
			if ($weeks > 0)
			{	
				$str .= $weeks . ' ' . (($weeks	> 1) ? $this->view->translate('weeks') : $this->view->translate('week')) . ', ';
			}
		
			$seconds -= $weeks * 604800;
		}			

		$days = floor($seconds / 86400);
	
		if ($months > 0 OR $weeks > 0 OR $days > 0)
		{
			if ($days > 0)
			{	
				$str .= $days . ' ' . (($days	> 1) ? $this->view->translate('days') : $this->view->translate('day')) . ', ';
			}
	
			$seconds -= $days * 86400;
		}
	
		$hours = floor($seconds / 3600);
	
		if ($days > 0 OR $hours > 0)
		{
			if ($hours > 0)
			{
				$str .= $hours . ' ' . (($hours	> 1) ? $this->view->translate('hours') : $this->view->translate('hour')) . ', ';
			}
		
			$seconds -= $hours * 3600;
		}
	
		$minutes = floor($seconds / 60);
	
		if ($days > 0 OR $hours > 0 OR $minutes > 0)
		{
			if ($minutes > 0)
			{	
				$str .= $minutes . ' ' . (($minutes	> 1) ? $this->view->translate('minutes') : $this->view->translate('minute')) . ', ';
			}
		
			$seconds -= $minutes * 60;
		}
	
		if ($str == '')
		{
			$str .= $seconds . ' ' . (($seconds	> 1) ? $this->view->translate('seconds') : $this->view->translate('second')) . ', ';
		}
			
		return substr(trim($str), 0, -1);
		
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
