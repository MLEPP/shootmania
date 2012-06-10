<?php

namespace ManiaLivePlugins\MLEPP\Ranks\Gui\Windows;

use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Icons64x64_1;
use ManiaLive\Utilities\Time;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Gui\Controls\Frame;
use ManiaLive\Gui\Controls\PageNavigator;

class ListWindow extends \ManiaLive\Gui\ManagedWindow
{
	private $tableau = array();
	private $navigator;
	private $bgresume;
	private $textInfos;
	private $showInfos = false;

	private $page;
	private $nbpage;
	private $currentChallengeindex;

	private $serverName;
	private $players = array();
	private $action;
	private $nbChallengesPlayed;

	function onConstruct()
	{
		parent::onConstruct();
		$this->setSize(180, 120);
		$this->centerOnScreen();
		$this->tableau = new Frame();
		$this->tableau->setPosition(0, -10);
		$this->addComponent($this->tableau);

		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);

		$this->makeFirstLine(-15.5);
		$this->page = 1;
		$this->nbpage = 1;
	}

	function setInfos($players = array(), $serverName)
	{
		$this->players = $players;
		$this->serverName = $serverName;
		$this->connection =  Connection::getInstance();
	}

	function makeFirstLine($posy = 0)
	{
		$texte = new Label();
		$texte->setSize(10, 4);
		$texte->setPosition(4, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oId");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(55, 4);
		$texte->setPosition(15, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oNickName");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(37.5, 4);
		$texte->setPosition(80, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oRank");
		$this->addComponent($texte);
		$texte = new Label();
		$texte->setSize(30, 4);
		$texte->setPosition(113, $posy, 2);
		$texte->setTextColor("000");
		$texte->setTextSize(2);
		$texte->setText("\$oPoints");
		$this->addComponent($texte);
	}

	function onDraw()
	{
		$this->tableau->clearComponents();
		$posy = 0;
		$num = 1;
		$this->setTitle('TOP 100 best players on $fff'.$this->serverName);

		if(count($this->players) > 0)
		{
			$posy -= 10;
			for($i=($this->page-1)*15; $i<=($this->page)*15-1; ++$i)
			{
				if(!isset($this->players[$i]))break;
				//$this->setLineBgs($posy, $i, $karmaInfo[$i]->name, $this->challengesList[$i]->fileName);
				$texte = new Label();
				$texte->setSize(6.5, 3);
				$texte->setPosition(7.5, $posy-0.5, 2);
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
				$texte->setText($this->players[$i]['nickname']);
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(30, 3);
				$texte->setPosition(80.5, $posy-0.5, 3);
				$texte->setTextColor("000");
				$texte->setTextSize(2);
				$texte->setText($this->players[$i]['rank']);
				$this->tableau->addComponent($texte);
				$texte = new Label();
				$texte->setSize(15.5, 3);
				$texte->setPosition(114, $posy-0.5, 3);
				$texte->setTextColor("000");
				$texte->setTextSize(2);
				$texte->setText($this->players[$i]['points']);
				$texte->setHalign("left");
				$this->tableau->addComponent($texte);
				$posy -= 6;
			}
		}

		$this->nbpage = intval((count($this->players)-1)/15)+1;

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
}

?>