<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Jukebox
 * @date 25-10-2011
 * @version v0.1.0
 * @website mlepp.com
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

namespace ManiaLivePlugins\MLEPP\Jukebox;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\Time;
use ManiaLib\Utils\Formatting as String;
use ManiaLive\DedicatedApi\Connection;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;

class Jukebox extends \ManiaLive\PluginHandler\Plugin {

protected $listNextChallenges = array();

	function onInit() {
		$this->setVersion('0.1.0');
	}
	
	function onLoad() {
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Jukebox v' . $this->getVersion());
		$this->enableDedicatedEvents();
		$this->registerChatCommand("list", "displayWindowList", 0, true);
		$this->registerChatCommand("l", "displayWindowList", 0, true);
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'Jukebox', $this);
	}
	
	function displayWindowList($login, $param1 = null, $param2 = null) {
	    $window = trackList::Create($login);
		$challenges = $this->connection->getMapList(-1, 0);
		$window->setInfos($challenges, $this->storage->server->name);
		$window->setAction(array($this, 'storeNextChallenge'));
		$window->show();
	}
	
	function storeNextChallenge($login, $i, $challengeName, $fileName) {
		foreach($this->listNextChallenges as $c) {
			if($c['login'] == $login) {
				$this->connection->chatSendServerMessage('$fff» $080You already have a map in the jukebox!', $login);
				return;
			}
		}
		$this->listNextChallenges[] = array('id'=>$i, 'name'=>$challengeName, 'login'=>$login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename'=>$fileName);
		$this->connection->chatSendServerMessage('$fff»» $fff'.$this->connection->getPlayerInfo($login)->nickName.'$z$s$080 added $fff'.$challengeName.'$z$s$080 to the jukebox!');
	}
	
	function mode_onEndMap($mapname) {
		if(count($this->listNextChallenges) > 0) {
			$next = array_shift($this->listNextChallenges);
			$this->connection->ChooseNextMap($next['filename']);
			$this->connection->chatSendServerMessage('$fff»» $080The next map will be $fff'.$next['name'].'$z$s$080 as requested by $fff'.$next['nickname'].'$z$s$080!');
		}
	}

}
?>