<?php

namespace ManiaLivePlugins\MLEPP\AudioNotations\Gui\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Audio as SoundPlayer;

use ManiaLive\Gui\Controls\Frame;


class WindowAudio extends \ManiaLive\Gui\Window
{
	private $audio;
	private $frame;
	private $filename;
	
	function onConstruct()
	{
		parent::onConstruct();	
		$this->audio = new SoundPlayer(32,32);		
		$this->addComponent($this->audio);
	}

	function setAudio($filename)
	{
		$this->audio->setData("http://koti.mbnet.fi/reaby/manialive/audio/".$filename, true);	
	}

	function onDraw()
	{
		$this->audio->autoPlay();					
	}

}

?>