<?php

/**
 * @file display_object.php
 *
 * Base object display class
 *
 */

require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');

/**
 * @brief Encapsulate an object that we display in potentially many formats (HTML is default)
 *
 */
class DisplayObject
{
	public $format = 'html';
	public $id = 0;
	public $object = NULL;
	public $display_disqus = true;
	public $callback = '';
	
	
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
		$this->GetId();
		$this->GetFormat();
	}
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['callback']))
		{
			$this->callback = $_GET['callback'];
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetFormat()
	{
		if (isset($_GET['format']))
		{
			switch ($_GET['format'])
			{
				case 'html':
					$this->format = 'html';
					break;
					
				case 'json':
					$this->format = 'json';
					break;
					
				case 'rdf':
					$this->format = 'rdf';
					break;
		
				default:
					$this->format = 'html';
					break;
			}
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function Display()
	{
		$this->Retrieve();
		if ($this->object == NULL)
		{
			$this->DisplayObjectNotFound();
		}
		else
		{
			$this->DisplayFormattedObject();
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayFormattedObject()
	{
		switch ($this->format)
		{
			case 'html':
				$this->DisplayHtml();
				break;

			case 'json':
				$this->DisplayJson();
				break;

			case 'rdf':
				$this->DisplayRdf();
				break;

			default:
				$this->DisplayHtml();
				break;
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayObjectNotFound()
	{
		switch ($this->format)
		{
			case 'json':
				$j = new stdclass;
				$j->error = "Object " . $this->id . " not found";
				header("Content-type: text/plain; charset=utf-8\n\n");
				if ($this->callback != '')
				{
					echo $this->callback . '(';
				}
				echo json_format(json_encode($j));
				if ($this->callback != '')
				{
					echo ')';
				}
				break;
		
			case 'html':
			default:
				echo "Object " . $this->id . " not found";
				break;
				
		}
	}

	//----------------------------------------------------------------------------------------------
	function DisplayHtml()
	{
		global $config;
		
		header("Content-type: text/html; charset=utf-8\n\n");
		echo html_html_open();
		echo html_head_open();
		echo html_title($this->GetTitle() . ' - ' . $config['site_name']);
		$this->DisplayHtmlHead();
		echo html_head_close();
		$this->DisplayBodyOpen();
		$this->DisplayMicroformat();
		$this->DisplayHtmlContent();
		echo html_body_close($this->display_disqus); // true to show Disqus comments
		echo html_html_close();	
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		echo '<h1>' . $this->GetTitle() . '</h1>';
	}
	
	//----------------------------------------------------------------------------------------------
	// Extra <HEAD> items
	function DisplayHtmlHead()
	{
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayBodyOpen()
	{
		echo html_body_open();
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayMicroformat()
	{
	}
	
	//----------------------------------------------------------------------------------------------
	/**
	 * @brief Default repsonse for a display format is to return a 404
	 *
	 */
	function DisplayNotFound()
	{
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;	
	}

	//----------------------------------------------------------------------------------------------
	function DisplayJson()
	{
		$this->DisplayNotFound();
	}

	
	//----------------------------------------------------------------------------------------------
	function DisplayRdf()
	{
		$this->DisplayNotFound();
	}
	
	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return 'Untitled';
	}		
	
	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		return $this->object;
	}
	
}



?>