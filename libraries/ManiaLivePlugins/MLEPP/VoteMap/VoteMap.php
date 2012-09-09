<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Jukebox
 * @date 09-09-2012
 * @version 0.4.0
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

namespace ManiaLivePlugins\MLEPP\VoteMap;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\Time;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLib\Utils\Formatting as String;
use ManiaLive\DedicatedApi\Connection;
use ManiaLivePlugins\MLEPP\VoteMap\Gui\Windows\nominateWindow;
use ManiaLivePlugins\MLEPP\Core\Core;

class VoteMap extends \ManiaLive\PluginHandler\Plugin {

	protected $listNominatedChallenges = array();
	protected $listNextChallenges = array();
	protected $votes = array();
	private $mlepp;

	function onInit() {
		$this->setVersion('0.4.0');
	}

	function onLoad() {				
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: VoteMap v' . $this->getVersion());
		$this->enableDedicatedEvents();
		$this->registerChatCommand("nominate", "displayWindowNominate", 0, true);
		$this->registerChatCommand("nominate", "nominateMap", 1, true);
		$this->registerChatCommand("rtv", "displayWindowVote", 0, true);
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', ' VoteMap', $this);
	}

	function displayWindowNominate($login) {
		$window = nominateWindow::Create($login);
		$challenges = $this->connection->getMapList(-1, 0);
		$window->setInfos($challenges, 'Nominate a challenge');
		$window->setAction(array($this, 'storeNominateChallenges'));
		$window->show();
	}

	function displayWindowVote($login) {
		$challenges = $this->connection->getMapList(-1, 0);
		$challengeList = array();

		for ($i = 0; $i < 5; $i++) {
			if (array_key_exists($i, $this->listNominatedChallenges)) {
				foreach ($challenges as $map) {
					if ($map->fileName == $this->listNominatedChallenges[$i]['name']) {
						$challengeList[] = $map;
						break;
					}
				}
			} else {
				$challengeList[] = $challenges[rand(0, count($challenges) - 1)];
			}
		}

		foreach ($this->storage->players as $player) {
			$window = nominateWindow::Create($player->login);
			$window->setInfos($challengeList, 'Vote next map');
			$window->setAction(array($this, 'storeVotes'));
			$window->show();
		}
	}

	function nominateMap($login, $param1 = null) {
		if (!is_string($param1)) {
			$this->connection->chatSendServerMessage('$fff» $080Parameter for nominate command:  $fff"' . $param1 . '"$080 is not valid!', $login);
			return;
		}

		$challenges = $this->connection->getMapList(-1, 0);
		$i = 0;
		foreach ($challenges as $challenge) {
			if ($param1 == $challenge->name || $param1 == $challenge->fileName) {
				$this->listNominatedChallenges[] = array('id' => $i, 'name' => $challenge->name, 'login' => $login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename' => $challenge->fileName);
				$this->connection->chatSendServerMessage('$fff» $080Success! Map $fff"' . $challenge->name . '"$z$s$080 is now nominated to voting list!', $login);
				return;
			}
			$i++;
		}

		// failure
		$this->connection->chatSendServerMessage('$fff» $080Map "' . $param1 . '" is not present at server.', $login);
	}

	function storeNominateChallenges($login, $i, $challengeName, $fileName) {
		if (!AdminGroup::contains($login)) {
			foreach ($this->listNominatedChallenges as $c) {
				if ($c['login'] == $login) {
					$this->connection->chatSendServerMessage('$fff» $080You already have already nominated a challenge!', $login);
					return;
				}
			}
		}

		$this->listNominatedChallenges[] = array('id' => $i, 'name' => $challengeName, 'login' => $login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename' => $fileName);
		$this->connection->chatSendServerMessage('$fff»» $fff' . $this->connection->getPlayerInfo($login)->nickName . '$z$s$080 added $fff' . $challengeName . '$z$s$080 to nominate list!');
		Console::println('[' . date('H:i:s') . '] [MLEPP] [VoteMap] ' . $login . ' added ' . $challengeName);
	}

	function storeVotes($login, $i, $challengeName, $fileName) {



		foreach ($this->votes as $map) {
			foreach ($map as $c) {
				if ($c['login'] == $login) {
					$this->connection->chatSendServerMessage('$fff» $080You have already voted!', $login);
					return;
				}
			}
		}

		$this->votes[$fileName][] = array('login' => $login);
		$this->connection->chatSendServerMessage('$fff»» $080 Vote successfully casted for $fff' . $challengeName . '$z$s$080!', $login);
		Console::println('[' . date('H:i:s') . '] [MLEPP] [VoteMap] ' . $login . ' added ' . $challengeName);
	}

	function mode_onEndMap($mapname) {
	
			
		if (count($this->votes) > 0) {

			$countedVotes = array();

			foreach ($this->votes as $key => $vote) {
				$countedVotes[$key] = count($vote);
			}

			arsort($countedVotes);			

			$countedVotes = array_flip($countedVotes);
			$next = array_shift($countedVotes);


			try {
				$this->connection->chooseNextMap($next);
				$this->connection->chatSendServerMessage('$fff»» $080The next map will be $fff' . $this->storage->nextMap->name . '$z$s$080!');
				Console::println('[' . date('H:i:s') . '] [MLEPP] [VoteMap] Next map will be ' . Core::stripColors($this->storage->nextMap->name) . '');
			} catch (\Exception $e) {
				Console::println('[' . date('H:i:s') . '] [MLEPP] [VoteMap] failure');
			}
		}

		$this->votes = array();
		$this->listNextChallenges = array();
		$this->listNominatedChallenges = array();
	}

	function jukeboxCommand($login, $param1 = null, $param2 = null) {
		if ($param1 == 'list' || $param1 == 'l') {
			if (count($this->listNominatedChallenges) > 0) {
				$message = '$fff» $080Maps currently in the jukebox: ';
				$i = 1;
				foreach ($this->listNominatedChallenges as $c) {
					$message .= '$fff' . $i . '$080. [$fff' . Core::stripColors($c['name']) . '$z$s$080] ';
					$i++;
				}

				$this->connection->chatSendServerMessage($message, $login);
			} else {
				$this->connection->chatSendServerMessage('$fff» $f00$iThere are no maps in the jukebox!', $login);
			}
		} elseif ($param1 == 'drop' || $param1 == 'd') {
			$jukeboxreversed = array_reverse($this->listNominatedChallenges, true);
			$i = count($this->listNominatedChallenges) - 1;
			foreach ($jukeboxreversed as $c) {
				if ($c['login'] == $login) {
					$this->connection->chatSendServerMessage('$fff»» $fff' . $c['nickname'] . '$z$s$080 dropped his map $fff' . $c['name'] . '$z$s$080 from the jukebox!');
					Console::println('[' . date('H:i:s') . '] [MLEPP] [VoteMap] ' . $c['login'] . ' dropped his map ' . Core::stripColors($c['name']) . ' from the jukebox!');
					unset($this->listNominatedChallenges[$i]);
					return;
				}
				$i--;
			}
		}
	}

}

?>