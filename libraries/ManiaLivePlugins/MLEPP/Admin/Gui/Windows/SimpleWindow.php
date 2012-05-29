<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\Label;

class SimpleWindow extends \ManiaLive\Gui\ManagedWindow {


    private $label;

    function onConstruct() {
        parent::onConstruct();     
        
        $this->label = new Label();        
        $this->label->enableAutonewline();
        $this->addComponent($this->label);

    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->label->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->label->setPosition(($this->sizeX - 4) / 8,-16);       
        
    }

    function setText($text) {
        $this->label->setText($text);
    }
    
    function destroy() {
        parent::destroy();
    }

}

?>