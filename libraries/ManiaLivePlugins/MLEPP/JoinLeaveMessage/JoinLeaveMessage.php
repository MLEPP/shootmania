<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack for ShootMania
 *
 * -- MLEPP Plugin --
 * @name Join/Leave Message
 * @date 14-08-2012
 * @version 0.3.0
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
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

namespace ManiaLivePlugins\MLEPP\JoinLeaveMessage;

use ManiaLive\Utilities\Console;
use DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Config\Loader;

class JoinLeaveMessage extends \ManiaLive\PluginHandler\Plugin {

	/**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */

	function onInit() {
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
		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: JoinLeaveMessage v'.$this->getVersion() );
	}

	/**
	 * onPlayerConnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */

	function onPlayerConnect($login, $isSpectator) {
		$player = $this->storage->getPlayerObject($login);

		Console::println('['.date('H:i:s').'] [MLEPP] [JoinLeaveMessage] '.$login.' joins the server.');

		if($this->isPluginLoaded('MLEPP\Ranks')) {
			$message = '$39f%title% $fff%nickname%$z$s%spec% $39f[$fff%country%$39f] [Ladder: $fff%ladderrank%$39f] [Rank: $fff%rank%$39f] has joined the server.';
		} else {
			$message = '$39f%title% $fff%nickname%$z$s%spec% $39f[$fff%country%$39f] [Ladder: $fff%ladderrank%$39f] has joined the server.';
		}

		$this->connection->chatSendServerMessage('$fff» $fa0Welcome $fff'.$player->nickName.'$z$s$fa0, this server is running $fffMLEPP for ShootMania$fa0!', $login);
		$this->connection->chatSendServerMessage('$fff»» '.$this->controlMsg($message, $player));
	}

	/**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */

	function onPlayerDisconnect($login) {
		Console::println('['.date('H:i:s').'] [MLEPP] [JoinLeaveMessage] '.$login.' left the server.');

		$player = $this->storage->getPlayerObject($login);
		$message = '$39f%title% $fff%nickname%$z$s$39f has left the server.';

		$this->connection->chatSendServerMessage('$fff»» '.$this->controlMsg($message, $player));
	}

	/**
	 * controlMsg()
	 * Helper function, used for parsing the join/leave messages.
	 *
	 * @param mixed $msg
	 * @param mixed $player
	 * @return mixed
	 */

	function controlMsg($msg, $player) {
		if(isset($player->path) && is_string($player->path)) {
			$path = str_replace('World|', '', $player->path);
		} else {
			$path = "unknown";
		}

		if($player->isSpectator) {
			$spec = ' $39f($fffSpec$39f)';
		} else {
			$spec = '';
		}

		$zone = explode("|",$path);
		if (isset($zone[0])) {
			$country = $zone[0];
		} else {
			$country = $path;
		}

		if(isset($player->ladderStats['PlayerRankings'][0]['Ranking'])) {
			$ladderrank = $player->ladderStats['PlayerRankings'][0]['Ranking'];
			if(empty($ladderrank) || $ladderrank == -1 || $ladderrank == false) {
				$ladderrank = "n/a";
			}
		} else {
			$ladderrank = "n/a";
		}

		$message = $msg;

		if($this->isPluginLoaded('MLEPP\Ranks')) {
			$rankinfo = $this->callPublicMethod('MLEPP\Ranks', 'getRank', $player->login);
			$rank = $rankinfo['rank'].' ('.$rankinfo['score'].')';
			$message = str_replace('%rank%', $rank, $message);
		}

		if(AdminGroup::contains($player->login)) {
			$title = 'Admin';
		} else {
			$title = 'Player';
		}

		$message = str_replace('%nickname%', $player->nickName, $message);
		$message = str_replace('%title%', $title, $message);
		$message = str_replace('%spec%', $spec, $message);
		$message = str_replace('%country%', $country, $message);
		$message = str_replace('%ladderrank%', number_format((int)$ladderrank, 0, '', ' '), $message);

		return $message;
	}
}

?>