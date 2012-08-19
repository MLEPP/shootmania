<?php

namespace ManiaLivePlugins\MLEPP\HeadsUp\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLive\Gui\Controls\Frame;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1;
//use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\HeadsUp\HeadsUp;

class HeadsUpWidget extends \ManiaLive\Gui\Window {

	protected $frame;
	protected $quad;
	private $label;
	public $callback;
	public $url;
	private $text;
	protected $background;

	function initializeComponents() {
			
	}

	function onLoad() {
		
	}

	function onDraw() {
		$this->quad = new Quad();
		$this->quad->setUrl($this->url);
		$this->addComponent($this->quad);
		
		$lines = explode("\n",$this->text);
		$textYsize = (count($lines)) * 4;

		for ($x = 0; $x < count($lines); $x++) {
			$label = new Label($this->sizeX,4);
			$label->setText(''.$lines[$x]);
			$label->setHalign("center");
			$label->setValign("top");
			$label->setPosition(0, (4*$x));
			$this->addComponent($label);
		}
		
		$this->quad->setSize($this->sizeX + 4, $textYsize);
		//print ($this->sizeX + 8).",".( $textYsize + 4);
		//$this->quad->setPosition(0, 0);
		$this->quad->setHalign("center");
		$this->quad->setValign("top");
		//$this->frame->setScale(0.7, 0.7);
	}

     function onResize($oldX, $oldY) {      
        
    }

	function setText($text) {
		$this->text = str_replace("|", "\n", $text);
	}

	function setUrl($url) {
		$this->url = $url;
	}

	function destroy() {
		unset($this->callback);
		parent::destroy();
	}

}

?>