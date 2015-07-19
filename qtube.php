<?php
/**
 * QTube Plugin
 *
 * @version 1.1.0
 * @package qtube
 * @author Massimo Giagnoni
 * @copyright Copyright (C) 2008 Massimo Giagnoni. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined( '_JEXEC' ) or die();

jimport( 'joomla.plugin.plugin' );


class plgContentQTube extends JPlugin {
	
	function plgContentQTube(&$subject, $params)	{
		parent::__construct($subject, $params);
	}

	function onPrepareContent(&$article, &$params, $limitstart) {
		global $mainframe;
		
		if(strpos($article->text, '{qtube') === false) {return;}
		$r = '#{qtube\s*(.*?)}#';
		$article->text = preg_replace_callback($r, array('plgContentQTube','callback'), $article->text);
	}
	
	function callback($matches) {
	
		$plugin = &JPluginHelper::getPlugin('content', 'qtube');
		$params = new JParameter($plugin->params);
		
		$r = '#(\w+)\s*:=\s*(?:(?:"([^"]*)")|([^\s]*))#';
		if($r = preg_match_all($r, $matches[1], $m)) {
			
			if(array_search('debug', $m[1]) !== false) {
				return '{qtube ' . str_replace(' debug:=1', '', $matches[1]) . '}';
			}
			$attrs = array('vid'=>'', 'w'=>'', 'h'=>'', 'c1'=>'', 'c2'=>'', 'b'=>'', 'ap'=>'', 'hd'=>'', 'dc'=>'', 'id'=>'');
			for($i=0; $i < $r; $i++) {
				$n= $m[1][$i];
				$v = $m[2][$i] ? $m[2][$i] : $m[3][$i];
				if (array_key_exists($n, $attrs)) {
					$attrs[$n] = htmlspecialchars($v);
				}
			}

			$attrs['url'] = 'http://www.youtube.com/embed/';

			foreach($attrs as $n=>$v) {
				if($v == '') {
					switch($n) {
						case 'id':
						$v = $params->get('id', '');	
						break;
						case 'w':
						$v = $params->get('width', '425');	
						break;
						case 'h':
						$v = $params->get('height', '355');
						break;
						case 'ap':
						$v = $params->get('autoplay', 0);
						break;
					}
					$attrs[$n] = $v;
				}
			}
			foreach($attrs as $n=>$v) {
				switch($n) {
					case 'id':
					if($v != '') { $attrs[$n]= ' id="' . $v . '"'; }
					break;
					case 'w':
					$attrs[$n]= ' width="' . $v . '"';;	
					break;
					case 'h':
					$attrs[$n] = ' height="' . $v . '"';
					break;
					case 'ap':
					if($v > 0) { 
						$attrs[$n] = "&amp;autoplay=$v"; 
					} else {
						$attrs[$n] = '';
					}
					break;
				}
			}
			
			if($attrs['vid'] == '') {
				$r = '{qtube error: video id missing!}';
			} else {
				$url = $attrs['url'].$attrs['vid'].'&amp;fs=1'.$attrs['ap'];
				$r = <<<EOD
<iframe{$attrs['id']} type="text/html"{$attrs['w']}{$attrs['h']} src="$url" frameborder="0">
EOD;
			}
			return $r;
			
		}
	}
}
?>