<?php
namespace ManiaLivePlugins\MLEPP\Admin\Gui\Controls;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLib\Gui\Elements\BgRaceScore2;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Label;

class Button extends \ManiaLive\Gui\Control
{
	public $callBack;
        
	protected $param;
	protected $icon;
	protected $background;

	function __construct($style,$substyle,$callback)
	{
		$this->setSize(6,6);
                $this->callBack = $callback;
                $this->icon = new Quad(6,6);
		$this->icon->setStyle($style);
		$this->icon->setSubStyle($substyle);
		$this->addComponent($this->icon);
	}
        public function addCall($call) {
                $action = $this->createAction($call, $this->callBack);
                $this->icon->setAction($action);
        
        }
        protected function onResize($oldX, $oldY) {
            parent::onResize($oldX, $oldY);
        }
        
	function destroy()
	{                  
		$this->callBack = null;
		parent::destroy();
	}
}
?>