<?php

require_once ('../config.inc.php');

$issn = '';
if (isset($_GET['issn']))
{
        $issn = $_GET['issn'];
}

//print_r($_GET);

$image_filename = $config['web_dir'] . '/images/issn/unknown.png';
$ext = 'png';

$image_filename;

if ($issn != '')
{
        // Do we have an image of the journal?
        $extensions = array('gif', 'jpg', 'png', 'jpeg');
        
        // Where we look for images
        $dir = $config['web_dir'] . '/images/issn';

        $base_name = str_replace('-', '', $issn);
        $found = false;
        
        foreach ($extensions as $extension)
        {
                $filename = $dir . '/' . $base_name . '.' . $extension;
                
                
                if (file_exists($filename))
                {
                        $found = true;
                        $ext = $extension;
                        $image_filename = $config['web_dir'] . '/images/issn/' . $base_name . '.' . $extension;
                        break;
                }
        }
        
        //echo $image_filename;

}

header("Content-Type: image/" . $ext);
@readfile($image_filename);

?>