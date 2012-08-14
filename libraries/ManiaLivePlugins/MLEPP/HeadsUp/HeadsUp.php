<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name AutoModeChange
 * @date 14-08-2012
 * @version 0.3.0
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP Team
 * @copyright 2010 - 2012
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

namespace ManiaLivePlugins\MLEPP\HeadsUp;

use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Event\Dispatcher;
use ManiaLivePlugins\MLEPP\HeadsUp\Gui\Windows\SimpleWindow;
use ManiaLivePlugins\MLEPP\HeadsUp\Gui\Windows\HeadsUpWidget;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class HeadsUp extends \ManiaLive\PluginHandler\Plugin {

	private $text;
	private $url;
	private $width;
	private $pos;
	private $mlepp;

	 /**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		// this needs to be set in the init section
		$this->setVersion('0.3.0');
		$this->setPublicMethod('getVersion');
	}

	 /**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		$this->enableDedicatedEvents();
		$this->config = Config::getInstance();
		

		if ($this->isPluginLoaded('MLEPP\Admin', '0.3.0')) {
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'setText'), array("set", "headsup", "text"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'setUrl'), array("set", "headsup", "url"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'setWidth'), array("set", "headsup", "width"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'setPos'), array("set", "headsup", "pos"), true, false, false);
		}

		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: HeadsUp r' . $this->getVersion());
	}

	function onUnload() {
		parent::onUnload();
		HeadsUpWidget::EraseAll();
	}

	 /**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {
		$this->text = $this->config->text;
		if (empty($this->text))
			$this->text = "initial settings| set this widget | /admin set headsup";
		
		$this->url = $this->config->url;
		if (empty($this->url))
			$this->url = "";
		
		$this->width = $this->config->width;
		if (empty($this->width))
			$this->width = 50;
		
		$this->pos = $this->config->pos;
		if (empty($this->pos))
			$this->pos = "80,-85";
		
		foreach ($this->storage->players as $login => $player) {
			$this->showWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showWidget($login);
		}
	}

	function setText($login, $text) {
		$this->config->text = $text;
		$this->text = $text;
		//refreshes the widget
		$this->onReady();
	}

	function setWidth($login, $width) {
		$this->config->width = $width;
		$this->width = $width;
		//refreshes the widget
		$this->onReady();
	}

	function setPos($login, $pos) {
		$this->config->pos = $pos;
		$this->pos = $pos;
		//refreshes the widget
		$this->onReady();
	}

	function setUrl($login, $url) {
		$this->config->url = $url;
		$this->url = $url;
		//refresh widget
		$this->onReady();
	}

	 /**
	 * onBeginChallenge()
	 * Function called on begin of challenge.
	 *
	 * @param mixed $challenge
	 * @param mixed $warmUp
	 * @param mixed $matchContinuation
	 * @return void
	 */
	function onBeginMap($map, $warmUp, $matchContinuation) {

		foreach ($this->storage->players as $login => $player) {
			$this->showWidget($login);
		}
		foreach ($this->storage->spectators as $login => $player) {
			$this->showWidget($login);
		}
	}

	 /**
	 * showWidget()
	 * Helper function, shows the widget.
	 *
	 * @param mixed $login
	 * @param mixed $text
	 * @return void
	 */
	function showWidget($login) {
		
		$window = HeadsUpWidget::Create($login);
		$window->setSize($this->config->width, 50);
		$window->setText($this->config->text);
		$window->setUrl($this->config->url);
		$pos = explode(",", $this->config->pos);
		$window->setPosition($pos[0], $pos[1]);
		$window->show();
	}

	 /**
	 * onPlayerConnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */
	function onPlayerConnect($login, $isSpec) {
		$this->showWidget($login);
	}

	 /**
	 * onPlayerDisconnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */
	function onPlayerDisconnect($login) {
		HeadsUpWidget::Erase($login);
	}

}

?>