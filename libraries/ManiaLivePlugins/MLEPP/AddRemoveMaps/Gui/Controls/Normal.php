<?php

namespace ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Controls;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\BgsPlayerCard;
class Normal extends \ManiaLive\Gui\Windowing\Control
{
	protected $background;
	protected $label;
	protected $highlight;
	public $callback;
	public $action;
	public $target;

	function initializeComponents()
	{
		$this->sizeX = $this->getParam(0);
		$this->sizeY = $this->getParam(1);

		// insert background ...
		$this->background = new BgsPlayerCard($this->getSizeX(), $this->getSizeY());
		$this->addComponent($this->background);

		// insert label ...
		$this->label = new Label($this->getSizeX() - 2, $this->getSizeY());
		$this->label->setPosition(1, 1);
		$this->addComponent($this->label);
	}

	function onResize()
	{
		$this->background->setSize($this->getSizeX(), $this->getSizeY());
		$this->label->setSize($this->getSizeX() - 2, $this->getSizeY());
	}

	function beforeDraw()
	{
		if ($this->target != NULL) {
			$style = "BgPlayerCardBig";
			$this->background->setSubStyle($style);
			$this->background->setAction($this->callback('onClicked'));
			$this->background->setVisibility(true);
		}
		else {
			$style = "BgPlayerName";
			$this->background->setSubStyle($style);

		}
	}

	function setHighlight($highlight)
	{
		$this->highlight = $highlight;
	}

	function setText($text)
	{
		$this->label->setText('$eee'.$text);
	}

	function onClicked($login)
	{
		call_user_func($this->callback, $login, $this->action,$this->target);
		$this->redraw();
	}
}