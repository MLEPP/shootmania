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
		
		$this->ParamName2 = new Label();        
        $this->ParamName2->enableAutonewline();
        $this->addComponent($this->ParamName2);
		
		$this->ParamDesc2 = new Label();        
        $this->ParamDesc2->enableAutonewline();
        $this->addComponent($this->ParamDesc2);
		
		$this->ParamType2 = new Label();        
        $this->ParamType2->enableAutonewline();
        $this->addComponent($this->ParamType2);
		
		$this->ParamLimit2 = new Label();        
        $this->ParamLimit2->enableAutonewline();
        $this->addComponent($this->ParamLimit2);
		
		$this->ParamName3 = new Label();        
        $this->ParamName3->enableAutonewline();
        $this->addComponent($this->ParamName3);
		
		$this->ParamDesc3 = new Label();        
        $this->ParamDesc3->enableAutonewline();
        $this->addComponent($this->ParamDesc3);
		
		$this->ParamType3 = new Label();        
        $this->ParamType3->enableAutonewline();
        $this->addComponent($this->ParamType3);
		
		$this->ParamLimit3 = new Label();        
        $this->ParamLimit3->enableAutonewline();
        $this->addComponent($this->ParamLimit3);
		
		$this->ParamLimit4 = new Label();        
		$this->ParamLimit4->enableAutonewline();
		$this->addComponent($this->ParamLimit4);

		$this->ParamLimit5 = new Label();        
		$this->ParamLimit5->enableAutonewline();
		$this->addComponent($this->ParamLimit5);


		$this->ParamLimit6 = new Label();        
		$this->ParamLimit6->enableAutonewline();
		$this->addComponent($this->ParamLimit6);

		$this->ParamLimit7 = new Label();        
		$this->ParamLimit7->enableAutonewline();
		$this->addComponent($this->ParamLimit7);

		$this->ParamLimit8 = new Label();        
		$this->ParamLimit8->enableAutonewline();
		$this->addComponent($this->ParamLimit8);

		$this->ParamLimit9 = new Label();        
		$this->ParamLimit9->enableAutonewline();
		$this->addComponent($this->ParamLimit9);

		$this->ParamLimit10 = new Label();        
		$this->ParamLimit10->enableAutonewline();
		$this->addComponent($this->ParamLimit10);

		$this->ParamLimit11 = new Label();        
		$this->ParamLimit11->enableAutonewline();
		$this->addComponent($this->ParamLimit11);

		$this->ParamLimit12 = new Label();        
		$this->ParamLimit12->enableAutonewline();
		$this->addComponent($this->ParamLimit12);
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
        $this->ParamName2->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamName2->setPosition(($this->sizeX - 4) / 8,-60);
        $this->ParamDesc2->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamDesc2->setPosition(($this->sizeX - 4) / 8,-64);
        $this->ParamType2->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamType2->setPosition(($this->sizeX - 4) / 8,-68);
        $this->ParamLimit2->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit2->setPosition(($this->sizeX - 4) / 8,-72);
		$this->ParamName3->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamName3->setPosition(($this->sizeX - 4) / 8,-76);
        $this->ParamDesc3->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamDesc3->setPosition(($this->sizeX - 4) / 8,-80);
        $this->ParamType3->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamType3->setPosition(($this->sizeX - 4) / 8,-84);
        $this->ParamLimit3->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit3->setPosition(($this->sizeX - 4) / 8,-88);
		$this->ParamLimit4->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit4->setPosition(($this->sizeX - 4) / 8,-92);
		$this->ParamLimit5->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit5->setPosition(($this->sizeX - 4) / 8,-96);
		
		$this->ParamLimit6->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit6->setPosition(($this->sizeX - 4) / 8,-100);
		
		$this->ParamLimit7->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit7->setPosition(($this->sizeX - 4) / 8,-104);
		
		$this->ParamLimit8->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit8->setPosition(($this->sizeX - 4) / 8,-108);
		
		$this->ParamLimit9->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit9->setPosition(($this->sizeX - 4) / 8,-112);
		
		$this->ParamLimit10->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit10->setPosition(($this->sizeX - 4) / 8,-116);
		
		$this->ParamLimit11->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit11->setPosition(($this->sizeX - 4) / 8,-120);
		
		$this->ParamLimit12->setSize($this->sizeX - 4, $this->sizeY - 8);
        $this->ParamLimit12->setPosition(($this->sizeX - 4) / 8,-124);
        
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
	
	function setText11($text) {
        $this->ParamLimit2->setText($text);
    }
	
	function setText12($text) {
        $this->ParamName3->setText($text);
    }
	
	function setText13($text) {
        $this->ParamDesc3->setText($text);
    }
	
	function setText14($text) {
        $this->ParamType3->setText($text);
    }
	
	function setText15($text) {
        $this->ParamLimit4->setText($text);
    }
	
	function setText16($text) {
        $this->ParamLimit5->setText($text);
    }
	
	function setText17($text) {
        $this->ParamLimit6->setText($text);
    }
	
	function setText18($text) {
        $this->ParamLimit7->setText($text);
    }
	
	function setText19($text) {
        $this->ParamLimit8->setText($text);
    }
	
	function setText20($text) {
        $this->ParamLimit9->setText($text);
    }
	
	function setText21($text) {
        $this->ParamLimit10->setText($text);
    }
	
	function setText22($text) {
        $this->ParamLimit11->setText($text);
    }
	
	function setText23($text) {
        $this->ParamLimit12->setText($text);
    }
	
	
    
    function destroy() {
        parent::destroy();
    }

}

?>