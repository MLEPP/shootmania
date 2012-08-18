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
 * This plugin is almost completely based on the plugin.mode.php
 * for FoxControl, writen by matrix142, libero, cyrilw and jensoo7
 * Copyright: FoxRace, http://www.fox-control.de
 */

namespace ManiaLivePlugins\MLEPP\AutoModeChange;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;

class AutoModeChange extends \ManiaLive\PluginHandler\Plugin {
	public $mapdirectory;
	public $currentGameMode;
	public $nextGameMode;
	public $changeGameMode = false;

	public function onInit() {
		require_once('libraries/ManiaLivePlugins/MLEPP/AutoModeChange/gbxdatafetcher.inc.php');

		$this->mapdirectory = $this->connection->getMapsDirectory();

		// getting current MapType
		$mapInfo = $this->connection->getCurrentMapInfo();
		$fileName = $mapInfo->fileName;

		$path = $this->mapdirectory.$fileName;

		$gbx = new GBXChallengeFetcher($path, true);

		$this->currentGameMode = $gbx->parsedxml['DESC']['MAPTYPE'];
	}

	function onLoad() {
		$this->setVersion('0.3.0');
		$this->enableDedicatedEvents();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: AutoModeChange v' . $this->getVersion());
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'AutoModeChange', $this);
	}

	public function mode_onEndMap($args) {
		$mapInfo = $this->connection->getNextMapInfo();
		$fileName = $mapInfo->fileName;

		$path = $this->mapdirectory.$fileName;

		$gbx = new GBXChallengeFetcher($path, true);

		$this->nextGameMode = $gbx->parsedxml['DESC']['MAPTYPE'];

		if($this->nextGameMode != $this->currentGameMode) {
			$this->changeGameMode = true;
		}
	}

	public function mode_onBeginMap($args) {
		// getting Current MapType
		$mapInfo = $this->connection->getCurrentMapInfo();
		$fileName = $mapInfo->fileName;

		$path = $this->map_directory.$fileName;

		$gbx = new GBXChallengeFetcher($path, true);

		$this->currentGameMode = $gbx->parsedxml['DESC']['MAPTYPE'];

		if($this->changeGameMode == true) {
			$this->changeGameMode = false;

			//Royal
			if($this->nextGameMode == 'Shootmania\RoyalArena') {
				$content = file_get_contents('Scripts/Royal.Script.txt');
				$this->connection->setRulesScriptText($content);

				$this->connection->chatSendServerMessage('$fff»» $f90[AutoMode] Changing GameMode to Royal!');
			}
			//Melee
			elseif($this->nextGameMode == 'ShootMania\MeleeArena') {
				$content = file_get_contents('Scripts/Melee.Script.txt');
				$this->connection->setRulesScriptText($content);

				$this->connection->chatSendServerMessage('$fff»» $z$s$f90[AutoMode] Changing GameMode to Melee!');
			}
			//Battle
			elseif($this->nextGameMode == 'ShootMania\BattleArena') {
				$content = file_get_contents('Scripts/Battle.Script.txt');
				$this->connection->setRulesScriptText($content);

				$this->connection->chatSendServerMessage('$fff»» $z$s$f90[AutoMode] Changing GameMode to Battle!');
			}
		}
	}
}
?>