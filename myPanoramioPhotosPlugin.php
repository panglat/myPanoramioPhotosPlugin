<?php
/**
 * plugin blankContentPlugin
 * @version 1.2
 * @package blankContentPlugin
 * @copyright Copyright (c) Jahr Firmennamen URL
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

/**
 * Platz für Informationen
 * =======================
 *
 * Anwendung im Content:
 *   {MyPanoramioPhotosPlugin}
 *
 * Anwendung im Content mit Parameterübergabe:
 *   {MyPanoramioPhotosPlugin param1=Hello!|param2=it works fine|param3=Joomla! rocks ;-)}
 */


defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');


class plgContentMyPanoramioPhotosPlugin extends JPlugin {
    
    function plgContentMyPanoramioPhotosPlugin( &$subject ) {
        parent::__construct( $subject );
    }

	public function getParam($param, $text, $default=null) {
		if(preg_match_all("/".$param."='(.*?)'/", trim($text), $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				return $match[1];
			}
		}
		return $default;
	}

	public function getPhotoHTML($photoId) {
        $divName = "div_photo_ex_".$photoId;
		return 
            "<div id='".$divName."' style='margin: 10px 15px'></div>\n".
            "<script type='text/javascript'>\n".
            "var photo_ex_widget = new panoramio.PhotoWidget('".$divName."', {'ids': [{'userId': 1897251, 'photoId': ".$photoId."}]}, {'width': 480, 'height': 360});\n".
            "photo_ex_widget.setPosition(0);\n".
            "</script>\n";
	}

    public function getPhotoTableHTML($photoId, $description) {
        $output = "<table style='background-color: #333333; border-color: #ffffff; border-width: 0px;' align='center' border='0'>\n";
        $output .= "<tr><td>";
        $output .= $this->getPhotoHTML($photoId);
        $output .= "</td></tr>";
        if($description != null) {
            $output .= "<tr><td style='text-align: center;'><p><strong><span style='color: #ffffff;'>".$description."</span></strong></p></td></tr>";
        }
        $output .= "</table>\n";
        
        return $output;
	}

    
	public function getPhotosTableHTML($photoIds, $description) {
        $ids = explode(',',$photoIds);
        $output = "<table style='background-color: #333333; border-color: #ffffff; border-width: 0px;' align='center' border='0' cellpadding='10'>\n";
        $col = 0;
        
        foreach ($ids as &$id) {
            if($col == 0) {
                $output .= "<tr>\n";                
            }
            $output .= "<td>\n";
            $output .= $this->getPhotoHTML($id);
            $output .= "</td>\n";
            if($col == 0) $col = 1;
            else {
                $output .= "</tr>\n";                
                $col = 0;
            }
        }
        if($col == 1) {
            $output .= "<td>relleno</td></tr>\n";
        }
        if($description != null) {
            $output .= "<tr><td colspan='2' style='text-align: center;'><p><strong><span style='color: #ffffff;'>".$description."</span></strong></p></td></tr>";
        }
        $output .= "</table>\n";
        
        return $output;
	}


  /**
   * Contentstring Definition
   * String erkennen und mit neuem Inhalt füllen
   */
  public function onContentPrepare($context, &$article, &$params, $limitstart) {
      // simple performance check to determine whether bot should process further
	  if (strpos($article->text, 'MyPanoramioPhotosPlugin') === false) {
		  return true;
      }
      $document = JFactory::getDocument();
      $document->addScript('http://www.panoramio.com/wapi/wapi.js?v=1&amp;hl=es');
      
      $regex = '/{MyPanoramioPhotosPlugin\s*(.*?)}/i';
      $article->text = preg_replace_callback($regex,array($this,"form"), $article->text);
	  return true;
  }


  public function form($matches) {
      $output = "";
      $description = $this->getParam("description",$matches[1]);
      $photoId = $this->getParam("id",$matches[1]);
      if($photoId != null) {
          $output = $this->getPhotoTableHTML($photoId, $description);
      } else {
          $photoIds = $this->getParam("ids",$matches[1], null);
          if(photoIds != null) {
              $output = $this->getPhotosTableHTML($photoIds, $description);
          } else {
          }
      }
      return $output;
  }

}

?>