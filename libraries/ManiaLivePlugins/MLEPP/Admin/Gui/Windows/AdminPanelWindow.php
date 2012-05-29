<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLib\Gui\Elements\Quad;
use ManiaLive\Gui\Controls\Frame;

class AdminPanelWindow extends \ManiaLive\Gui\Window {

    private $frame;
    private $background;
    public static $size;
    public static $position;
    
    function onConstruct() {
        $pos = explode(",",self::$position);
        $this->setPosition(158, 60);
        $this->setSize(100, 8);
        $this->setHalign("right");
        $this->background = new Quad();
        $this->background->setSubStyle(Bgs1InRace::BgHealthBar);
        $this->background->setSize($this->sizeX, $this->sizeY);
               
        $this->addComponent($this->background);        
        $this->frame = new Frame(2,-1);
        $this->frame->setLayout(new Flow($this->sizeX,$this->sizeY));       
        $this->addComponent($this->frame);
    }

    function addItem($item) {
        $this->frame->addComponent($item);
    }

    function clearItems() {
        $this->frame->clearComponents();
    }

    function destroy() {
        parent::destroy();
    }
     function onResize($oldX, $oldY) {      
        
    }

}

?>