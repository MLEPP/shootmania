<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\Label;

class RulesParam extends \ManiaLive\Gui\ManagedWindow {


    private $label;

    function onConstruct() {
        parent::onConstruct();     
        
        $this->Param = new Label();        
        $this->Param->enableAutonewline();
        $this->addComponent($this->Param);
		
		$this->Param1 = new Label();        
        $this->Param1->enableAutonewline();
        $this->addComponent($this->Param1);
		
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->Param->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->Param->setPosition(($this->sizeX - 4) / 8,-16);
        $this->Param1->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->Param1->setPosition(($this->sizeX - 4) / 8,-20);
    }

    function setText($text) {
        $this->Param->setText($text);
    }
	
	function setText1($text) {
        $this->Param1->setText($text);
    }
	
    
    function destroy() {
        parent::destroy();
    }

}

?>