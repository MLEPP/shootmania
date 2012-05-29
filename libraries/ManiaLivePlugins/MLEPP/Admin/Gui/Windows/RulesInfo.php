<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLive\PluginHandler\PluginHandler;
use ManiaLib\Gui\Elements\Label;

class RulesInfo extends \ManiaLive\Gui\ManagedWindow {


    private $label;

    function onConstruct() {
        parent::onConstruct();     
        
        $this->Name = new Label();        
        $this->Name->enableAutonewline();
        $this->addComponent($this->Name);
		
		$this->MapType = new Label();        
        $this->MapType->enableAutonewline();
        $this->addComponent($this->MapType);
		
		$this->Description = new Label();        
        $this->Description->enableAutonewline();
        $this->addComponent($this->Description);
		
		$this->ParamName = new Label();        
        $this->ParamName->enableAutonewline();
        $this->addComponent($this->ParamName);
		
		$this->ParamDesc = new Label();        
        $this->ParamDesc->enableAutonewline();
        $this->addComponent($this->ParamDesc);
		
		$this->ParamType = new Label();        
        $this->ParamType->enableAutonewline();
        $this->addComponent($this->ParamType);
		
		$this->ParamLimit = new Label();        
        $this->ParamLimit->enableAutonewline();
        $this->addComponent($this->ParamLimit);
		
		$this->ParamName1 = new Label();        
        $this->ParamName1->enableAutonewline();
        $this->addComponent($this->ParamName1);
		
		$this->ParamDesc1 = new Label();        
        $this->ParamDesc1->enableAutonewline();
        $this->addComponent($this->ParamDesc1);
		
		$this->ParamType1 = new Label();        
        $this->ParamType1->enableAutonewline();
        $this->addComponent($this->ParamType1);
		
		$this->ParamLimit1 = new Label();        
        $this->ParamLimit1->enableAutonewline();
        $this->addComponent($this->ParamLimit1);
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->Name->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->Name->setPosition(($this->sizeX - 4) / 8,-16);
        $this->MapType->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->MapType->setPosition(($this->sizeX - 4) / 8,-20);
		$this->Description->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->Description->setPosition(($this->sizeX - 4) / 8,-24);
        $this->ParamName->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamName->setPosition(($this->sizeX - 4) / 8,-28);
		$this->ParamDesc->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamDesc->setPosition(($this->sizeX - 4) / 8,-32);
        $this->ParamType->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamType->setPosition(($this->sizeX - 4) / 8,-36);
        $this->ParamLimit->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit->setPosition(($this->sizeX - 4) / 8,-40);
        $this->ParamName1->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamName1->setPosition(($this->sizeX - 4) / 8,-44);
        $this->ParamDesc1->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamDesc1->setPosition(($this->sizeX - 4) / 8,-48);
        $this->ParamType1->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamType1->setPosition(($this->sizeX - 4) / 8,-52);
        $this->ParamLimit1->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit1->setPosition(($this->sizeX - 4) / 8,-56);			
        
    }

    function setText($text) {
        $this->Name->setText($text);
    }
	
	function setText1($text) {
        $this->MapType->setText($text);
    }
	
	function setText2($text) {
        $this->Description->setText($text);
    }
	
	function setText3($text) {
        $this->ParamName->setText($text);
    }
	
	function setText4($text) {
        $this->ParamDesc->setText($text);
    }
	
	function setText5($text) {
        $this->ParamType->setText($text);
    }
	
	function setText6($text) {
        $this->ParamLimit->setText($text);
    }
	
	function setText7($text) {
        $this->ParamName1->setText($text);
    }
	
	function setText8($text) {
        $this->ParamDesc1->setText($text);
    }
	
	function setText9($text) {
        $this->ParamType1->setText($text);
    }
	
	function setText10($text) {
        $this->ParamLimit1->setText($text);
    }
	
	
    
    function destroy() {
        parent::destroy();
    }

}

?>