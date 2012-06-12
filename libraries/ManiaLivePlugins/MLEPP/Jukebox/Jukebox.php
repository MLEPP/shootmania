<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Jukebox
 * @date 10-06-2012
 * @version v0.2.0
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
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLib\Utils\Formatting as String;
use ManiaLive\DedicatedApi\Connection;
use ManiaLivePlugins\MLEPP\Jukebox\Gui\Windows\trackList;
use ManiaLivePlugins\MLEPP\Core\Core;

class Jukebox extends \ManiaLive\PluginHandler\Plugin {

	protected $listNextChallenges = array();
	private $mlepp;

	function onInit() {
		$this->setVersion('0.2.0');
	}
	
	function onLoad() {
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Jukebox v' . $this->getVersion());
		$this->enableDedicatedEvents();
		$this->registerChatCommand("list", "displayWindowList", 0, true);
		$this->registerChatCommand("l", "displayWindowList", 0, true);
		$this->registerChatCommand("jukebox", "jukeboxCommand", 1, true);
		$this->registerChatCommand("jb", "jukeboxCommand", 1, true);
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
		if(!AdminGroup::contains($login)) {
			foreach($this->listNextChallenges as $c) {
				if($c['login'] == $login) {
					$this->connection->chatSendServerMessage('$fff» $080You already have a map in the jukebox!', $login);
					return;
				}
			}
		}

		$this->listNextChallenges[] = array('id'=>$i, 'name'=>$challengeName, 'login'=>$login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename'=>$fileName);
		$this->connection->chatSendServerMessage('$fff»» $fff'.$this->connection->getPlayerInfo($login)->nickName.'$z$s$080 added $fff'.$challengeName.'$z$s$080 to the jukebox!');
		Console::println('[' . date('H:i:s') . '] [MLEPP] [Jukebox] '.$login.' added '.$challengeName);
	}
	
	function mode_onEndMap($mapname) {
		if(count($this->listNextChallenges) > 0) {
			$next = array_shift($this->listNextChallenges);
			$this->connection->ChooseNextMap($next['filename']);
			$this->connection->chatSendServerMessage('$fff»» $080The next map will be $fff'.$next['name'].'$z$s$080 as requested by $fff'.$next['nickname'].'$z$s$080!');
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Jukebox] Next map will be '.Core::stripColors($next['name']).' as requested by '.$next['login']);
		}
	}

	function jukeboxCommand($login, $param1 = null, $param2 = null) {
		if($param1 == 'list' || $param1 == 'l') {
			if(count($this->listNextChallenges) > 0) {
				$message = '$fff» $080Maps currently in the jukebox: ';
				$i = 1;
				foreach($this->listNextChallenges as $c) {
					$message .= '$fff'.$i.'$080. [$fff'.Core::stripColors($c['name']).'$z$s$080] ';
					$i++;
				}

				$this->connection->chatSendServerMessage($message, $login);
			} else {
				$this->connection->chatSendServerMessage('$fff» $f00$iThere are no maps in the jukebox!', $login);
			}
		} elseif($param1 == 'drop' || $param1 == 'd') {
			$jukeboxreversed = array_reverse($this->listNextChallenges, true);
			$i = count($this->listNextChallenges)-1;
			foreach($jukeboxreversed as $c) {
				if($c['login'] == $login) {
					$this->connection->chatSendServerMessage('$fff»» $fff'.$c['nickname'].'$z$s$080 dropped his map $fff'.$c['name'].'$z$s$080 from the jukebox!');
					Console::println('[' . date('H:i:s') . '] [MLEPP] [Jukebox] '.$c['login'].' dropped his map '.Core::stripColors($c['name']).' from the jukebox!');
					unset($this->listNextChallenges[$i]);
					return;
				}
				$i--;
			}
		}
	}
}
?>