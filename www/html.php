<?php

/**
 * @file html.php
 *
 * Wrap HTML output
 *
 */

require_once('../config.inc.php');
require_once('../user.php');

$starttime = '';

//--------------------------------------------------------------------------------------------------
function html_html_open()
{
	global $starttime;
	$starttime = microtime();
	
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
		. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_html_close()
{
	return '</html>';
}

//--------------------------------------------------------------------------------------------------
function html_head_open()
{
	global $config;
	
	$html = '<head>' . "\n"
		. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
				
	// Base URL for all links on page
	// This is very useful because we use Apache mod_rewrite extensively, and this ensures
	// image URLs can still be written as relative addresses
	$html .= '<base href="' . $config['web_root'] . '" />' . "\n";
	
	// Base URL as Javascript variable (Firefox needs this for some images to load correctly
	$html .= '<!--Webs site root as global Javascript variable so Firefox can find images. -->' . "\n";
	$html .= '<script type="text/javascript">' . "\n";
	$html .= 'var gWebRoot = \'' .  $config['web_root'] . '\';' . "\n";
	$html .= '</script>' . "\n";
		
	// Twitter analytics
	$html .= '<meta name="twitter:site" content="@biostor_org" />' . "\n";
	
	// Pintrest
	$html .= '<meta name="p:domain_verify" content="5f60c8da3099dba7fd452ca1b9668c0a"/>' . "\n";
		
	// Favicon
	$html .= '<link rel="shortcut icon" href="' . $config['web_root'] . '/images/biostor-shadow32x32.ico" />' . "\n";
		
	// CSS
	$html .= html_include_css ('/css/main.css');
	
	// RSS feed
	// Uncomment if want single global feed
	//$html .= html_include_link('application/atom+xml', 'ATOM', 'rss.php?format=atom', 'alternate');
		
	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_head_close()
{
	// Google Analytics (new style)
	/*
	echo "<script type=\"text/javascript\">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-12127487-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>\n";
*/

	return '</head>' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_body_open($params = '')
{
	global $config;
	
	$html = '';
	if ($params != '')
	{
		$html = '<body';
		foreach ($params as $k => $v)
		{
			$html .= ' '  . $k . '=' . '"' . $v . '"';
		}
		
		$html .= '>' . "\n";
	
	}
	else
	{
		$html = '<body>' . "\n";
	}
	
	
	
	return $html;
	
}

//--------------------------------------------------------------------------------------------------
function html_page_header($has_search = false, $query = '', $category = 'all')
{
	global $config;
	
	$html = '';
	$html .= '<div style="border-bottom:1px dotted rgb(128,128,128);padding-bottom:15px;">';
	$html .= '<a href="' . $config['web_root'] . '"><img src="' . $config['web_root'] . '/images/biostor-shadow32x32.png" border="0" align="top" />&nbsp;<span style="font-size:32px;">' . $config['site_name'] . '</span></a>';
	
	if ($has_search)
	{
		$html .='<div style="float:right;">' . "\n";	
		$html .= '<table>';
		$html .= '<tr>';
		
		if ($config['use_mendeley_oauth'])
		{
			$html .= '<td>';
			/* Disable sign-in for now
			if (user_is_logged_in())
			{
				$html .= '<a href="./clearsessions.php">Sign out</a>';
			}
			else						
			{
				$html .= '<a href="./redirect.php?url=' . urlencode($_SERVER["REQUEST_URI"]) . '
">Sign in using Mendeley</a>';
			}
			*/
			$html .= '</td>';
		}	
	
/*		// Login/out
		if (user_is_logged_in())
		{
			$user = user_with_openid($_COOKIE['openid']);
			if ($user != NULL)
			{
				$html .= '<td><img src="http://www.gravatar.com/avatar/' . md5($user->email) . '" width="32" /></td>';
			
				$html .= '<td>   '. $user->username . '   </td>';			
			}
			$html .= '<td>   <a href="logout.php">Logout</a>   </td>';
		}
		else
		{
			$html .=  '<td>   <a href="login.php">Login</a>   </td>';
		}	
*/		
		$html .= html_search_box($query, $category);

		$html .='</tr>' . "\n";
		$html .='</table>' . "\n";
		$html .='</div>' . "\n";
	}
	
	$html .='</div>' . "\n";
	

	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_body_close($disqus = false)
{
	global $starttime;
	global $config;
	
	$startarray = explode(" ", $starttime);
	$starttime = $startarray[1] + $startarray[0];
	$endtime = microtime();
	$endarray = explode(" ", $endtime);
	$endtime = $endarray[1] + $endarray[0];
	$totaltime = $endtime - $starttime; 
	$totaltime = round($totaltime,5);

	$html = '';

	if ($disqus && $config['use_disqus'])
	{
		//$html .= '<hr/>' . "\n";
	
		$html .= '<div id="disqus_thread"></div>
<script type="text/javascript" src="http://disqus.com/forums/biostor/embed.js"></script>
<noscript>
<a href="http://disqus.com/forums/biostor/?url=ref">View the discussion thread.</a>
</noscript>
<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>';
	}
	
	if ($config['use_uservoice'])
	{
		
		$html .= '<script type="text/javascript">
	var uservoiceOptions = {
	  /* required */
	  key: \'biostor\',
	  host: \'biostor.uservoice.com\', 
	  forum: \'36526\',
	  showTab: true,  
	  /* optional */
	  alignment: \'right\',
	  background_color:\'#f00\', 
	  text_color: \'white\',
	  hover_color: \'#06C\',
	  lang: \'en\'
	};
	
	function _loadUserVoice() {
	  var s = document.createElement(\'script\');
	  s.setAttribute(\'type\', \'text/javascript\');
	  s.setAttribute(\'src\', ("https:" == document.location.protocol ? "https://" : "http://") + "cdn.uservoice.com/javascripts/widgets/tab.js");
	  document.getElementsByTagName(\'head\')[0].appendChild(s);
	}
	_loadSuper = window.onload;
	window.onload = (typeof window.onload != \'function\') ? _loadUserVoice : function() { _loadSuper(); _loadUserVoice(); };
	</script>';
	}

	$html .= '<div style="border-top:1px dotted rgb(128,128,128);text-align:center;padding:4px;font-size:10px;">Page loaded in ' . $totaltime . ' seconds</div>';

	// Google analytics (traditional)
	/*
	$html .= '<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-12127487-1");
pageTracker._trackPageview();
} catch(err) {}</script>';
	*/
	
	// Pintrest
	$html .= '<script type="text/javascript" async src="//assets.pinterest.com/js/pinit.js"></script>';	
	
	$html .= '</body>' . "\n";
	return $html;
}


//--------------------------------------------------------------------------------------------------
function html_title($str)
{
	return '<title>' . $str . '</title>' . "\n";
}

//--------------------------------------------------------------------------------------------------
// Absolutely vital to write this in the form <script></script>, otherwise
// Firefox breaks badly
function html_include_script($script_path)
{
	global $config;
	
	if (preg_match('/^http:/', $script_path))
	{
		// Externally hosted
		return '<script type="text/javascript" src="' . $script_path . '"></script>' . "\n";
	}
	else
	{
		return '<script type="text/javascript" src="' . $config['web_root'] . $script_path . '"></script>' . "\n";
	}
}

//--------------------------------------------------------------------------------------------------
function html_include_css($css_path)
{
	global $config;
	return '<link type="text/css" href="' . $config['web_root'] . $css_path . '" rel="stylesheet" />' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_include_link($type, $title, $path, $rel)
{
	global $config;
	return '<link type="' . $type . '" title="' . $title . '" href="' . $config['web_root'] . $path . '" rel="' . $rel . '" />' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_meta($name, $content)
{
	return '<meta name="' . $name . '" content="' . $content . '" />' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_image($image_path, $class = '')
{
	global $config;
	$html = '<img ';
	if ($class != '')
	{
		$html .= ' class="' . $class . '"';
	}
	$html .=  'src="' . $image_path . '" alt="" />';
	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_search_box($query = '', $category = 'all')
{
	global $config;
	
	$html = '';
	
	if ($config['use_solr'])
	{
	
		// Note use of <div> around <input>, in XHTML we can't have a naked <input> element
		$html = '<td>';
		$html .= '<form  method="get" action="' . $config['web_root'] . 'search.php" onsubmit="return validateTextSearch(this);">
			<div >
			<input  id="search" name="q" type="text" size="20" value="' . $query . '"/>' . "\n";
	/*		
			<select  id="category" name="category">
				<!--<option value="all"';
				if ($category == 'all')
				{
					$html .= ' selected="selected"';
				}
				$html .= '>All</option>-->
				<option value="author"';
				if ($category == 'author')
				{
					$html .= ' selected="selected"';
				}
				$html .= '>Author</option>
				<!--<option value="citation"';
				if ($category == 'citation')
				{
					$html .= ' selected="selected"';
				}
				$html .= '>Citation</option>
				<option value="title"';
				if ($category == 'title')
				{
					$html .= ' selected="selected"';
				}
				$html .= '>Reference</option>-->
				<option value="name"';
				if ($category == 'name')
				{
					$html .= ' selected="selected"';
				}
				$html .= '>Taxon name</option>
			</select> */
		$html .= '<input  name="submit" type="submit" value="Search" />
			</div>
		</form>' . "\n";
	
		$html .= "</td>\n";
	}
	
	return $html;
}

/*
//--------------------------------------------------------------------------------------------------
function html_top($query = '')
{
	global $config;
	
	$html = '<div id="logo">';
//	$html .= '<div style="float:left;padding:0px;font-size:24px;"><a href="' . $config['web_root'] . '"><img src="images/challenge_banner_right.gif" height="63"/></a></div>';
	$html .= '<div style="float:right;padding:4px;margin-top:5px;">';
	$html .= html_search_box($query);
	$html .= '</div>';
	$html .= '</div>';
	return $html;
}*/

?>