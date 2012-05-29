<?php
namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Controls\Header;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Controls\Normal;
use ManiaLivePlugins\MLEPP\AddRemoveTracks\Gui\Controls\Action;

use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\PluginHandler\PluginHandler;

use ManiaLib\Gui\Elements\Bgs1InRace;
use ManiaLib\Gui\Tools;
use ManiaLib\Gui\Elements\BgsPlayerCard;
use ManiaLib\Gui\Elements\Label;
use ManiaLib\Gui\Elements\Entry;
use ManiaLib\Gui\Elements\Quad;
use ManiaLib\Gui\Elements\Button;
use ManiaLib\Gui\Layouts\Flow;
use ManiaLive\Data\Storage;
use ManiaLive\Gui\Windowing\Controls\ButtonResizeable;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLive\Gui\Windowing\Controls\PageNavigator;
use ManiaLive\Gui\Windowing\Controls\Panel;
use ManiaLive\Gui\Windowing\Controls\Frame;
use ManiaLive\Utilities\Time;
use ManiaLive\Utilities\Console;

class SelectTracklistWindow extends \ManiaLive\Gui\Windowing\ManagedWindow
{
	//components ...
	private $navigator;
	private $table;
	private $navigator_back;

	private $page;
	private $records;
	private $page_last;
	private $page_items;
	private $item_height;
	private $table_height;
	private $columns;
	//private $info;
	private $highlight;
	private $callback;

	function initializeComponents()
	{
		$this->page = 1;
		$this->page_last = 1;
		$this->item_height = 6;
		$this->table_height = 0;
		$this->records = array();
		$this->columns = array();
		$this->highlight = false;
		$this->panel = new Panel();
		$this->setTitle('Select new default tracklist file');
		$this->setMaximizable();

		// add background for navigation elements ...
		$this->navigator_back = new BgsPlayerCard();
		$this->navigator_back->setSubStyle(BgsPlayerCard::BgCardSystem);
		$this->addComponent($this->navigator_back);

		// create records-table ...
		$this->table = new Frame($this->getSizeX() - 4, $this->getSizeY()-16);
		$this->table->applyLayout(new Flow());
		$this->table->setPosition(2, 16);
		$this->addComponent($this->table);

		// create page navigator ...
		$this->navigator = new PageNavigator();
		$this->addComponent($this->navigator);
	}

	function onResize()
	{
		$this->table->setSize($this->getSizeX() - 4, $this->getSizeY() - 16);
		$this->calculatePages();
	}

	function onDraw()
	{
		// refresh table ...
		$this->table->clearComponents();

		// create table header ...
		foreach ($this->columns as $name => $percent)
		{
			$cell = new Header($percent * $this->table->getSizeX(), $this->item_height + 1);
			$cell->setText($name);

			$this->table->addComponent($cell);
		}

		// create table body ...
		$count = count($this->records);
		$max = $this->page_items * $this->page;
		for ($i = $this->page_items * ($this->page - 1); $i < $count && $i < $max; $i++)
		{
			$record = $this->records[$i];

			foreach ($this->columns as $name => $percent)
			{
				if ($name == "isSpec" ) {
					if ($record[$name][0] == "isSpec") $icon = "Spec";
					if ($record[$name][0] == "isRace") $icon = "Race";
					$cell = new SpecIcon($percent * $this->table->getSizeX(), $this->item_height, $icon);
				}
				else {
					if ($record[$name][2]) {
						$cell = new Action($percent * $this->table->getSizeX(), $this->item_height);
					}
					else {
						$cell = new Normal($percent * $this->table->getSizeX(), $this->item_height);
					}
				$cell->callback = $this->callback;
				$cell->action = $record[$name][0];
				$cell->target = $record[$name][1];

				if (isset($record[$name]))
					$cell->setText($record[$name][0]);
				else
					$cell->setText(' ');
				}

				$this->table->addComponent($cell);
			}
		}

		// add page navigator to the bottom ...
		$this->navigator->setPositionX($this->getSizeX() / 2);
		$this->navigator->setPositionY($this->getSizeY() - 4);

		// place navigation background ...
		$this->navigator_back->setValign('bottom');
		$this->navigator_back->setSize($this->getSizeX() - 0.6, 8);
		$this->navigator_back->setPosition(0.3, $this->getSizeY() - 0.3);

		// configure ...
		$this->navigator->setCurrentPage($this->page);
		$this->navigator->setPageNumber($this->page_last);
		$this->navigator->showText(true);
		$this->navigator->showLast(true);

		if ($this->page < $this->page_last)
		{
			$this->navigator->arrowNext->setAction($this->callback('showNextPage'));
			$this->navigator->arrowLast->setAction($this->callback('showLastPage'));
		}
		else
		{
			$this->navigator->arrowNext->setAction(null);
			$this->navigator->arrowLast->setAction(null);
		}

		if ($this->page > 1)
		{
			$this->navigator->arrowPrev->setAction($this->callback('showPrevPage'));
			$this->navigator->arrowFirst->setAction($this->callback('showFirstPage'));
		}
		else
		{
			$this->navigator->arrowPrev->setAction(null);
			$this->navigator->arrowFirst->setAction(null);
		}
	}

	function onHide()
	{
		$this->showFirstPage();
		$this->highlight = false;
	}

	function calculatePages()
	{
		$this->page_items = floor( ($this->table->getSizeY()-12) / $this->item_height);
		$this->page_last = ceil(count($this->records) * $this->item_height / max(1, $this->table->getSizeY()-12));
	}

	function addColumn($name, $percent)
	{
		$this->columns[$name] = $percent;
	}

	function clearAll()
	{
		$this->columns = array();
		$this->records = array();
	}

	function clearItems()
	{
		$this->records = array();
	}

	function addItem($record)
	{
		if (is_array($record))
		{
			$this->records[] = $record;
			$this->calculatePages();
		}
	}

	function addAdminItem($record,$callback)
	{
		$this->callback = $callback;
		if (is_array($record))
		{
			$this->records[] = $record;
			$this->calculatePages();
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
		$this->page = $this->page_last;
		if ($login) $this->show();
	}

	function showFirstPage($login = null)
	{
		$this->page = 1;
		if ($login) $this->show();
	}

	function destroy()
	{
		unset($this->callback);
		parent::destroy();
	}

	function showInfo($login,$title,$text) {

			$infowindow = Info::Create($login, false);
			$infowindow->setSize(120, 60);
			$infowindow->setTitle($title);
			$infowindow->setText($text);

			$infowindow->centerOn($this);
			WindowHandler::showDialog($infowindow);

	}
}