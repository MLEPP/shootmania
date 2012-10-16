<?php

namespace ManiaLivePlugins\BAZINGA\ChangeMap\Gui\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLive\Utilities\Time;
use ManiaLive\Gui\Controls\Frame;
use ManiaLive\Gui\Controls\PageNavigator;

class playlistWindow extends \ManiaLive\Gui\ManagedWindow
{	
	private $tableau = array();
	private $navigator;
	private $textInfos;
	private $showInfos = false;
	
	private $page;
	private $nbpage;
	private $currentChallengeindex;

	private $challengesList = array();
	private $windowTitle;
	private $action;
	private $nbChallengesPlayed;
	
	function onConstruct()
	{
		parent::onConstruct();
		$this->setSize(90, 120);
		$this->centerOnScreen();
		$this->setPositionX(-160 + $this->sizeX/2);
		$this->tableau = new Frame();
		$this->tableau->setPosition(0, -10);
		$this->addComponent($this->tableau);
		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);
		$this->makeFirstLine(-15.5);
		$this->page = 1;
		$this->nbpage = 1;
	}
	
	function setInfos($Playlists = array(), $windowTitle = '')
	{
		$this->challengesList = $Playlists;
		$this->windowTitle = $windowTitle;
	}
	
	function makeFirstLine($posy = 0)
	{
		$texte = new Label();
		$texte->setSize(10, 4);
		$texte->setPosition(3, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oId");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(55, 4);
		$texte->setPosition(15, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oName");
		$this->addComponent($texte);				
	}
	
	function onDraw()
	{
		$this->tableau->clearComponents();
		$posy = 0;
		$num = 1;		
		$this->setTitle($this->windowTitle);

		if(count($this->challengesList) > 0)
		{
			$posy -= 10;
			for($i=($this->page-1)*10; $i<=($this->page)*10-1; ++$i)
			{
				if(!isset($this->challengesList[$i]))break;
				$this->setLineBgs($posy, $i, $this->challengesList[$i], $this->challengesList[$i]);
				$texte = new Label();
				$texte->setSize(6.5, 3);
				$texte->setPosition(6.5, $posy-0.5, 2);
				$texte->setTextColor("000");
				$texte->setTextSize(2);
				$texte->setHalign("right");
				$texte->setText(($i+1).".");
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(63, 3);
				$texte->setPosition(15.5, $posy-0.5, 3);
				$texte->setTextColor("000");
				$texte->setTextSize(2);
				$texte->setText('$fff$o'.$this->challengesList[$i]);
				$this->tableau->addComponent($texte);				
				$posy -= 6;
			}
		}
		
		$this->nbpage = intval((count($this->challengesList)-1)/10)+1;
		
		$this->navigator->setPositionX($this->getSizeX() / 2);
		$this->navigator->setPositionY(-($this->getSizeY() - 6));
		$this->navigator->setCurrentPage($this->page);
		$this->navigator->setPageNumber($this->nbpage);
		$this->navigator->showText(true);
		$this->navigator->showLast(true);

		if ($this->page < $this->nbpage)
		{
			$this->navigator->arrowNext->setAction($this->createAction(array($this,'showNextPage')));
			$this->navigator->arrowLast->setAction($this->createAction(array($this,'showLastPage')));
		}
		else
		{
			$this->navigator->arrowNext->setAction(null);
			$this->navigator->arrowLast->setAction(null);
		}

		if ($this->page > 1)
		{
			$this->navigator->arrowPrev->setAction($this->createAction(array($this,'showPrevPage')));
			$this->navigator->arrowFirst->setAction($this->createAction(array($this,'showFirstPage')));
		}
		else
		{
			$this->navigator->arrowPrev->setAction(null);
			$this->navigator->arrowFirst->setAction(null);
		}
	}
	
	function showPrevPage($login = null)
	{
		$this->page--;
		if ($login) $this->show();
	}

	function showNextPage($login = null)
	{
		$this->page++;
		if ($login) $this->show();
	}

	function showLastPage($login = null)
	{
		$this->page = $this->nbpage;
		if ($login) $this->show();
	}

	function showFirstPage($login = null)
	{
		$this->page = 1;
		if ($login) $this->show();
	}
	
	function showInfos($login = null, $showInfos)
	{
		$this->showInfos = $showInfos;
		if($login)$this->show();
	}
	
	function setAction($action = array())
	{
		$this->action = $action;
	}
	
	function setLineBgs($posy, $i, $challengeName, $fileName)
	{
			$bg = new BgsPlayerCard();
			$bg->setSubStyle(BgsPlayerCard::BgCardSystem);
			$bg->setAction($this->createAction(array($this, 'nominateChallenge'), $i, $challengeName, $fileName));
			$bg->setPosition(14.5, $posy+0.5, 2);
			$bg->setSize(64.5,5);
			$this->tableau->addComponent($bg);
	}
	
	function nominateChallenge($login, $i, $challengeName, $fileName)
	{
		call_user_func($this->action, $login, $i, $challengeName, $fileName);
		$this->hide();
	}
}

?>