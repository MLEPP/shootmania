<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack for ShootMania
 *
 * -- MLEPP Plugin --
 * @name Karma
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

namespace ManiaLivePlugins\MLEPP\Karma;

use ManiaLive\Utilities\Console;
use DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Config\Loader;

use ManiaLivePlugins\MLEPP\Karma\Gui\Windows\ListWindow;

use ManiaLivePlugins\MLEPP\Core\Core;

class Karma extends \ManiaLive\PluginHandler\Plugin {

	private $playerKarmas = array();
	private $totalKarma;
	private $karmaVoters;
	private $karmaVotesPosNeg;

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
		$this->enableDatabase();
		$this->enableDedicatedEvents();
		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Karma v'.$this->getVersion() );
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'Karma', $this);
	}

	/**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */

	function onReady() {
		$cmd = $this->registerChatCommand("karma", "karma", 0, true);
		$cmd = $this->registerChatCommand("whokarma", "whoKarma", 0, true);
		$cmd = $this->registerChatCommand("++", "votePlus", 0, true);
		$cmd = $this->registerChatCommand("--", "voteMin", 0, true);

		if(!$this->db->tableExists('karma')) {
			$q = "CREATE TABLE `karma` (
                                    `karma_id` MEDIUMINT( 5 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                                    `karma_playerlogin` VARCHAR( 50 ) NOT NULL ,
                                    `karma_trackuid` VARCHAR( 50 ) NOT NULL ,
                                    `karma_value` MEDIUMINT ( 5 ) NOT NULL
                                    ) CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = MYISAM;";
			$this->db->query($q);
		}
		
		$this->karma();
	}

	/**
	 * onBeginMap()
	 * Function called on begin of the map.
	 *
	 * @param $map
	 * @return void
	 */

	function mode_onBeginMap($map) {
		$challenge = $this->connection->getCurrentMapInfo();
		$this->connection->chatSendServerMessage('$fff»» $06fNew map: $fff'.Core::stripColors($challenge->name).'$06f by $fff'.$challenge->author.'$06f.');
		$this->karma();
	}

	/**
	 * onPlayerChat()
	 * Function called when someone is chatting.
	 *
	 * @param mixed $playerUid
	 * @param mixed $login
	 * @param $text
	 * @param mixed $isRegistredCmd
	 * @internal param mixed $chat
	 * @return void
	 */

	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {
		if($playerUid != 0) {
			if(substr($text, 0, 1) != "/") {
				if(trim($text) == "++")  $this->votePlus($login);
				if(trim($text) == "--")  $this->voteMin($login);
			}
		}
	}

	/**
	 * karma()
	 * Providing the /karma command.
	 *
	 * @param $login
	 * @return void
	 */

	function karma($login = null) {
		$player = $this->storage->getPlayerObject($login);
		$this->getMapKarma();

		$positive = $this->karmaVotesPosNeg['positive'];
		$negative = $this->karmaVotesPosNeg['negative'];
		$message = '$f90Current mapkarma: $fff'.$this->totalKarma.'$f90 ($fff'.$this->karmaVoters.'$f90 voters, $fff'.$positive.'$f90 ++ and $fff'.$negative.'$f90 --) !';

		if($login != null) {
			// message to login
			$message = '$fff» '.$message;
			$player = $this->storage->getPlayerObject($login);
			$this->connection->chatSendServerMessage($message, $player);
		} else {
			$message = '$fff»» '.$message;
			$this->connection->chatSendServerMessage($message);
		}
	}

	function whoKarma($login) {
		if(!empty($this->playerKarmas)) {
			$window = ListWindow::Create($login);
			$map = $this->connection->getCurrentMapInfo();
			$karmaLogins = array_keys($this->playerKarmas);
			$karmaInfo = array();

			foreach($karmaLogins as $login) {
				$player = $this->callPublicMethod('MLEPP\Core', 'getPlayerInfo', $login);
				if($player == false) {
					$player = new \stdClass();
					$player->player_nickname = 'None';
				}
				$karmaInfo[] = array('login' => $login, 'player' => $player, 'vote' => $this->playerKarmas[$login]);
			}

			$window->setInfos($karmaInfo, $map->name);
			$window->show();
		} else {
			$player = $this->storage->getPlayerObject($login);
			$this->connection->chatSendServerMessage('$fff» $f00$iNo karma votes on this map!', $player);
		}
	}

	/**
	 * getMapKarma()
	 * Function used for getting the karma of the challenge.
	 *
	 * @param null $uid
	 * @return void
	 */

	function getMapKarma($uid = null) {
		if(is_null($uid)) {
			$challenge = $this->connection->getCurrentMapInfo();
			$uid = $challenge->uId;
		}

		$q = "SELECT * FROM `karma` WHERE `karma_trackuid` = ".$this->db->quote($uid).";";
		$query = $this->db->query($q);

		$counter = 0;
		$totalkarma = 0;

		$positive = 0;
		$negative = 0;

		$this->playerKarmas = array();
		while($data = $query->fetchObject()) {
			if($data->karma_value == '-1') {
				$karma_value = '--';
				$totalkarma = ($totalkarma-1);
				$negative++;
			} elseif($data->karma_value == '1') {
				$karma_value = '++';
				$totalkarma = ($totalkarma+1);
				$positive++;
			}
			$this->playerKarmas[$data->karma_playerlogin] = $karma_value;
			$counter++;
		}

		$this->totalKarma = $totalkarma;
		$this->karmaVoters = $counter;
		$this->karmaVotesPosNeg = array('positive' => $positive, 'negative' => $negative);
	}

	function votePlus($login) {
		$this->applyVote($login, '1');
	}

	function voteMin($login) {
		$this->applyVote($login, '-1');
	}

	/**
	 * applyVote()
	 * Function applies the vote.
	 *
	 * @param mixed $login
	 * @param mixed $value
	 * @return void
	 */

	function applyVote($login, $value) {
		$player = $this->storage->getPlayerObject($login);
		$challengeobject = $this->connection->getCurrentMapInfo();
		if($value == '1') {
			$valueName = '++';
		} else {
			$valueName = '--';
		}
		$this->connection->chatSendServerMessage('$fff»» $f90You voted $fff'.$valueName.'$f90 on map $fff'.$challengeobject->name.'$z$s$f90!', $player);

		Console::println('['.date('H:i:s').'] [MLEPP] [Karma] '.$login.' voted '.$valueName.'.');

		// To do the karma for database.
		$this->updateKarmaToDatabase($login, $value);

		//redraw window
		$this->getMapKarma();
		$this->karma($login);
	}

	/**
	 * updateKarmaToDatabase()
	 * Function used for updating the karma in the database.
	 *
	 * @param mixed $login
	 * @param mixed $value
	 * @return void
	 */

	function updateKarmaToDatabase($login, $value) {
		$challenge = $this->connection->getCurrentMapInfo();
		$uid = $challenge->uId;
		//check if player is at database
		$g =  "SELECT * FROM `karma` WHERE `karma_playerlogin` = ".$this->db->quote($login)."
		 AND `karma_trackuid` = ".$this->db->quote($uid).";";

		$query = $this->db->query($g);
		// get player data
		$player = $this->storage->getPlayerObject($login);

		if($query->recordCount() == 0) {
			// 	--> add new player entry
			$q = "INSERT INTO `karma` (`karma_playerlogin`,
                                                    `karma_trackuid`,
                                                    `karma_value`
                                                    )
		                                VALUES (".$this->db->quote($login).",
                                                ".$this->db->quote($uid).",
                                                ".$this->db->quote($value)."
                                                )";
			$this->db->query($q);
		}
		else {
			//	--> update existing player entry
			$q =
				"UPDATE
			`karma`
			 SET
			 `karma_value` = ".$this->db->quote($value)."
			 WHERE
			 `karma_playerlogin` = ".$this->db->quote($login)."
			 AND
			 `karma_trackuid` = ".$this->db->quote($uid).";";

			$this->db->query($q);
		}
	}
}
?>