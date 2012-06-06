<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLive\Utilities\Time;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Gui\Controls\Frame;
use ManiaLive\Gui\Controls\PageNavigator;

class trackList extends \ManiaLive\Gui\ManagedWindow
{	
	private $tableau = array();
	private $navigator;
	private $bgresume;
	private $textInfos;
	private $showInfos = false;
	
	private $page;
	private $nbpage;
	private $currentChallengeindex;

	private $challengesList = array();
	private $serverName;
	private $action;
	private $nbChallengesPlayed;
	
	function onConstruct()
	{
		parent::onConstruct();
		$this->setSize(212, 126);
		$this->centerOnScreen();
		$this->tableau = new Frame();
		$this->tableau->setPosition(0, -10);
		$this->addComponent($this->tableau);
		
		$this->navigator = new PageNavigator();
		//$this->navigator->setIconSize(6);
		$this->addComponent($this->navigator);
		
		/*$this->bgresume = new Bgs1InRace();
		$this->bgresume->setSubStyle(Bgs1InRace::BgList);
		$this->bgresume->setSize(81, 4);
		$this->bgresume->setPosition(2, 5.5, 2);
		$this->addComponent($this->bgresume);*/
		
		$this->makeFirstLine(-15.5);
		$this->page = 1;
		$this->nbpage = 1;
	}
	
	function setInfos($challengesList = array(), $serverName)
	{
		$this->challengesList = $challengesList;
		$this->serverName = $serverName;
		$this->connection =  Connection::getInstance();
	}
	
	function makeFirstLine($posy = 0)
	{
		$texte = new Label();
		$texte->setSize(10, 4);
		$texte->setPosition(3, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("Id");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(55, 4);
		$texte->setPosition(15, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("Name");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(37.5, 4);
		$texte->setPosition(80, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("Author");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(10, 4);
		$texte->setPosition(112, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("Mood");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(15, 4);
		$texte->setPosition(131, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("Coppers");
		$this->addComponent($texte);
	}
	
	function onDraw()
	{
		$this->tableau->clearComponents();
		$posy = 0;
		$num = 1;
		//print_r($this->challengesList);
		$this->setTitle(count($this->challengesList).' tracks on $z'.$this->serverName);
		$this->currentChallengeindex = $this->connection->getCurrentMapIndex();

		if(count($this->challengesList) > 0)
		{
			$posy -= 10;
			for($i=($this->page-1)*15; $i<=($this->page)*15-1; ++$i)
			{
				if(!isset($this->challengesList[$i]))break;
				$this->setLineBgs($posy, $i, $this->challengesList[$i]->name, $this->challengesList[$i]->fileName);
				$texte = new Label();
				$texte->setSize(6.5, 3);
				$texte->setPosition(6.5, $posy-0.5, 2);
				$texte->setTextColor("FFF");
				$texte->setTextSize(2);
				$texte->setHalign("right");
				$texte->setText(($i+1).".");
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(63, 3);
				$texte->setPosition(15.5, $posy-0.5, 3);
				$texte->setTextColor("FFF");
				$texte->setTextSize(2);
				$texte->setText($this->challengesList[$i]->name);
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(30, 3);
				$texte->setPosition(80.5, $posy-0.5, 3);
				$texte->setTextColor("FFF");
				$texte->setTextSize(2);
				$texte->setText($this->challengesList[$i]->author);
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(15.5, 3);
				$texte->setPosition(114, $posy-0.5, 3);
				$texte->setTextColor("FFF");
				$texte->setTextSize(2);
				$texte->setText($this->challengesList[$i]->environnement);
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(12, 3);
				$texte->setPosition(137, $posy-1, 3);
				$texte->setTextSize(1.75);
				$texte->setHalign('center');
				$texte->setText($this->challengesList[$i]->copperPrice);
				$this->tableau->addComponent($texte);
				$posy -= 6;
			}
		}
		
		$this->nbpage = intval((count($this->challengesList)-1)/15)+1;
		
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
		if($i == $this->currentChallengeindex)
		{
			$bg = new Bgs1InRace();
			$bg->setSubStyle(Bgs1InRace::NavButtonBlink);
			$bg->setPosition(14.5, $posy+1, 2);
			$bg->setSize(64.5,6);
			$this->tableau->addComponent($bg);
		}
		else if($this->currentChallengeindex - $i > $this->nbChallengesPlayed || $this->currentChallengeindex - $i < 0)
		{
			$bg = new BgsPlayerCard();
			$bg->setSubStyle(BgsPlayerCard::BgCardSystem);
			$bg->setAction($this->createAction(array($this, 'imposeChallenge'), $i, $challengeName, $fileName));
			$bg->setPosition(14.5, $posy+0.5, 2);
			$bg->setSize(64.5,5);
			$this->tableau->addComponent($bg);
		}
		$bg = new BgsPlayerCard();
		$bg->setSubStyle(BgsPlayerCard::BgCardSystem);
		$bg->setPosition(79.5, $posy+0.5, 2);
		$bg->setSize(32,5);
		$this->tableau->addComponent($bg);
		$bg = new BgsPlayerCard();
		$bg->setSubStyle(BgsPlayerCard::BgCardSystem);
		$bg->setPosition(112, $posy+0.5, 3);
		$bg->setSize(17.5, 5);
		$this->tableau->addComponent($bg);
		$bg = new BgsPlayerCard();
		$bg->setSubStyle(BgsPlayerCard::BgCardSystem);
		$bg->setPosition(130, $posy+0.5, 3);
		$bg->setSize(14.5, 5);
		$this->tableau->addComponent($bg);
	}
	
	function imposeChallenge($login, $i, $challengeName, $fileName)
	{
		call_user_func($this->action, $login, $i, $challengeName, $fileName);
		$this->hide();
	}
}

?>