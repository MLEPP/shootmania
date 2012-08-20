<?php
namespace ManiaLivePlugins\MLEPP\ChangeMap\Gui\Controls;


use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLib\Gui\Elements\Label;


class ViewMap extends \ManiaLive\Gui\Control
{
	private $background;
        private $uIDbackground;
	private $name;
	private $author;
	private $uID;

	
	function __construct($map, $parentClass)
	{
		$this->sizeY = 6;
         //       $actionSpec = $this->createAction(array($parentClass, 'onClick'), "change", $map->uId);
                $actionChange = $this->createAction(array($parentClass, 'onClick'), "change", $map->name);
                
		$this->background = new \ManiaLib\Gui\Elements\Bgs1();
		$this->background->setSubStyle(\ManiaLib\Gui\Elements\Bgs1::BgCardList);                
		$this->addComponent($this->background);
                
         //       $this->uIDbackground = new \ManiaLib\Gui\Elements\Bgs1();
		//$this->uIDbackground->setSubStyle(\ManiaLib\Gui\Elements\Bgs1::BgCardList);                
		//$this->addComponent($this->uIDbackground);
                               
                               
		
                
		$this->name = new Label();
		$this->name->setText('$000'.$map->name.'$z');
		$this->addComponent($this->name);
		
		
		$this->uID = new Label();
                $this->uID->setText('$000'.$map->uId.'$z');
                $this->addComponent($this->uID);
		
		$this->author = new Label();
                $this->author->setText('$000   '.$map->author);
                /*$this->nickName->setStyle("TextValueMedium");
                $this->nickName->setTextSize(2);
                $this->nickName->setFocusAreaColor1('8888');
                $this->nickName->setFocusAreaColor2('fa0a');*/
                
                //$this->nickName->setAction($actionSpec);
        
		$this->addComponent($this->author);
		
		$this->frame = new \ManiaLive\Gui\Controls\Frame($this->author->getBorderRight()+1,0);        
		$this->frame->setLayout(new \ManiaLib\Gui\Layouts\Flow(30,6));
		
		$button = new \ManiaLive\Gui\Controls\ButtonResizable(25, 6);
		$button->setAction($actionChange);
		 $button->setText("Change Map");
         $this->frame->addComponent($button);
                
        $this->addComponent($this->frame);        
               
    }
        
	function onResize($oldX, $oldY)
	{	
		
		$this->uID->setSizeX(60);
		$this->uID->setPosition(1, -$this->sizeY / 2);
		$this->uID->setValign('center2');
                
                
		$this->name->setSizeX(40);
		$this->name->setPosition($this->uID->getBorderRight()+5, -$this->sizeY / 2);
		$this->name->setValign('center2');
		
		$this->author->setSizeX(30);
                $this->author->setSizeY(6);
		$this->author->setValign('center2');
		$this->author->setPosition($this->name->getBorderRight()+1, -$this->sizeY / 2);
                
		$this->background->setSize(42, 6);
                $this->background->setValign('center');
                $this->background->setPosition($this->uID->getBorderRight(), -$this->sizeY / 2);
		
                $this->frame->setPosition($this->author->getBorderRight()+1, 0);
                $this->frame->setValign('center2');
	}
}
?>