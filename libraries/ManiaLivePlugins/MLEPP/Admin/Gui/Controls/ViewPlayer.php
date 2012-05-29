<?php
namespace ManiaLivePlugins\MLEPP\Admin\Gui\Controls;


use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;


class ViewPlayer extends \ManiaLive\Gui\Control
{
	private $background;
        private $uIDbackground;
	private $loginName;
	private $nickName;
	private $uID;

	
	function __construct($player, $parentClass)
	{
		$this->sizeY = 6;
                $actionSpec = $this->createAction(array($parentClass, 'onClick'), "Spec", $player->login);
                
		$this->background = new \ManiaLib\Gui\Elements\Bgs1();
		$this->background->setSubStyle(\ManiaLib\Gui\Elements\Bgs1::BgCardList);                
		$this->addComponent($this->background);
                
                $this->uIDbackground = new \ManiaLib\Gui\Elements\Bgs1();
		$this->uIDbackground->setSubStyle(\ManiaLib\Gui\Elements\Bgs1::BgCardList);                
		$this->addComponent($this->uIDbackground);
                               
                               
		$this->uID = new Label();
                $this->uID->setText('$000'.$player->playerId.'$z');
                $this->addComponent($this->uID);
                
		$this->loginName = new Label();
		$this->loginName->setText('$000'.$player->login.'$z');
		$this->addComponent($this->loginName);
		
		$this->nickName = new Label();
                $this->nickName->setText('$fff   '.$player->nickName);
                $this->nickName->setStyle("TextValueMedium");
                $this->nickName->setTextSize(2);
                $this->nickName->setFocusAreaColor1('8888');
                $this->nickName->setFocusAreaColor2('fa0a');
                
                $this->nickName->setAction($actionSpec);
                
		$this->addComponent($this->nickName);
        }
        
	function onResize($oldX, $oldY)
	{	
		
		$this->uID->setSizeX(10);
		$this->uID->setPosition(1, -$this->sizeY / 2);
		$this->uID->setValign('center2');
                $this->uIDbackground->setSize(10, 6);
                $this->uIDbackground->setValign('center');
                $this->uIDbackground->setPosition(1, -$this->sizeY / 2);
                
		$this->loginName->setSizeX(40);
		$this->loginName->setPosition($this->uID->getBorderRight()+1, -$this->sizeY / 2);
		$this->loginName->setValign('center2');
		
		$this->nickName->setSizeX(60);
                $this->nickName->setSizeY(6);
		$this->nickName->setValign('center2');
		$this->nickName->setPosition($this->loginName->getBorderRight()+1, -$this->sizeY / 2);
                
		$this->background->setSize(42, 6);
                $this->background->setValign('center');
                $this->background->setPosition($this->uID->getBorderRight(), -$this->sizeY / 2);
		
               // $this->frame->setPosition($this->nickName->getBorderRight(), 0);
                //$this->frame->setValign('center2');
	}
}
?>