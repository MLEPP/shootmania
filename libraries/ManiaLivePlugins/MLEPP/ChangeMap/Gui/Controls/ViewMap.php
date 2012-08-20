<?php
/**
 * Change map plugin for MLEPP v.0.2.1 (20/08/12)
 * @author dfk7677
 * @copyright 2012
 *
 * ---------------------------------------------------------------------
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 * You are allowed to change things or use this in other projects, as
 * long as you leave the information at the top (name, date, version,
 * website, package, author, copyright) and publish the code under
 * the GNU General Public License version 3.
 * ---------------------------------------------------------------------
 */
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
		
		
		/*$this->uID = new Label();
                $this->uID->setText('$000'.$map->uId.'$z');
                $this->addComponent($this->uID);*/
		
		$this->author = new Label();
                $this->author->setText('by '.$map->author);
                /*$this->nickName->setStyle("TextValueMedium");
                $this->nickName->setTextSize(2);
                $this->nickName->setFocusAreaColor1('8888');
                $this->nickName->setFocusAreaColor2('fa0a');*/
                
                //$this->nickName->setAction($actionSpec);
        
		$this->addComponent($this->author);
		
		$this->frame = new \ManiaLive\Gui\Controls\Frame($this->author->getBorderRight()+1,0);        
		$this->frame->setLayout(new \ManiaLib\Gui\Layouts\Flow(35,6));
		
		$button = new \ManiaLive\Gui\Controls\ButtonResizable(30, 6);
		$button->setAction($actionChange);
		$button->setText("Change Map");
        $this->frame->addComponent($button);
                
        $this->addComponent($this->frame);        
               
    }
        
	function onResize($oldX, $oldY)
	{	
		
		/*$this->uID->setSizeX(70);
		$this->uID->setPosition(1, -$this->sizeY / 2);
		$this->uID->setValign('center2');*/
                
                
		$this->name->setSizeX(45);
		$this->name->setPosition(5, -$this->sizeY / 2);
		$this->name->setValign('center2');
		
		$this->author->setSizeX(30);
        $this->author->setSizeY(6);
		$this->author->setValign('center2');
		$this->author->setPosition($this->name->getBorderRight()+5, -$this->sizeY / 2);
                
		$this->background->setSize(45, 6);
        $this->background->setValign('center');
        $this->background->setPosition($this->name->getBorderLeft()-1, -$this->sizeY / 2);
		
        $this->frame->setPosition($this->author->getBorderRight()+1, 0);
        $this->frame->setValign('center2');
	}
}
?>