<?php
/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Core
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

namespace ManiaLivePlugins\MLEPP\Core;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;

use ManiaLivePlugins\MLEPP\Core\Gui\Windows\ListWindow;

class Core extends \ManiaLive\PluginHandler\Plugin {

	private $plugins = array();
	private $players = array();
	private $lastHit = array();

	function onInit() {
		$this->setVersion('0.3.0');
		$this->setPublicMethod('registerPlugin');
		$this->setPublicMethod('sendCallbacks');
		$this->setPublicMethod('getPlayerInfo');
	}

	function onLoad() {
		$this->enableDatabase();
		$this->enableDedicatedEvents();
		$cmd = $this->registerChatCommand("active", "activeCommand", 0, true);

		Console::println('[' . date('H:i:s') . '] [MLEPP] Core v' . $this->getVersion());
		$this->connection->chatSendServerMessage('$fff» $fa0Welcome, this server is running $fffMLEPP for ShootMania$fa0!');


		if(!$this->db->tableExists('players')) {
			$q = "CREATE TABLE IF NOT EXISTS `players` (
  					`player_id` mediumint(9) NOT NULL AUTO_INCREMENT,
  					`player_login` varchar(50) NOT NULL,
  					`player_nickname` varchar(100) DEFAULT NULL,
  					`player_nation` varchar(50) NOT NULL,
  					`player_updatedat` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  					`player_timeplayed` int(10) NOT NULL DEFAULT '0',
  					`player_points` mediumint(9) NOT NULL DEFAULT '0',
  					PRIMARY KEY (`player_id`),
  					UNIQUE KEY `player_login` (`player_login`)
				  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			$this->db->execute($q);
		}

		foreach($this->storage->players as $player) {
			$this->onPlayerConnect($player->login, false);
		}

		foreach($this->storage->spectators as $player) {
			$this->onPlayerConnect($player->login, false);
		}
	}

	function onUnload() {
		foreach($this->storage->players as $player) {
			$this->onPlayerDisconnect($player->login);
		}

		foreach($this->storage->spectators as $player) {
			$this->onPlayerDisconnect($player->login);
		}
	}

	function activeCommand($login, $param1 = null, $param2 = null, $param3 = null) {
		$window = ListWindow::Create($login);
		$execute = $this->db->execute("SELECT * FROM `players` ORDER BY `player_timeplayed` DESC LIMIT 0,100");
		$players = array();
		$i = 0;
		while($player = $execute->fetchObject()) {
			$players[$i] = $player;
			$i++;
		}
		$window->setInfos($players, $this->storage->server->name);
		$window->show();
	}

	function onPlayerConnect($login, $isSpectator) {
		$player = $this->storage->getPlayerObject($login);
		$this->insertPlayer($player);

		$this->players[$login] = time();
	}

	function onPlayerDisconnect($login) {
		try {
			$info = $this->db->execute("SELECT `player_timeplayed` FROM `players` WHERE `player_login` = '".$login."'")->fetchObject();
			$q = "UPDATE `players` SET `player_timeplayed` = '".($info->player_timeplayed + (time()-$this->players[$login]))."' WHERE `player_login` = '".$login."'";
			$this->db->execute($q);
		} catch(\Exception $e) {}
	}

	function onModeScriptCallback($param1, $param2) {
		Console::println('[' . date('H:i:s') . '] Script callback: '.$param1.', with parameter: '.$param2);
		switch($param1) {
			case 'beginMap':
				$this->sendCallbacks('mode_onBeginMap', $param2);
				return;
			case 'endMap':
				$scores = explode(';', $param2);
				$this->sendCallbacks('mode_onEndMap', $scores);
				return;
			case 'beginRound':
				$this->sendCallbacks('mode_onBeginRound', $param2);
				return;
			case 'endRound':
				$this->sendCallbacks('mode_onEndRound', $param2);
				$this->sendCallbacks('mode_onEndRoundElite', $param2);
				return;
			case 'poleCapture':
				$this->sendCallbacks('mode_onPoleCapture', $param2);
				return;
			case 'playerRespawn':
				$this->sendCallbacks('mode_onPlayerRespawn', $param2);
				return;
			case 'playerDeath':
				$this->mode_onPlayerDeath($param2);
				return;
			case 'playerHit':
				$this->mode_onPlayerHit($param2);
				return;
			case 'capture':
				$this->sendCallbacks('mode_onPoleCapture', $param2);
				return;
			case 'startRound':
				$this->sendCallbacks('mode_onStartRoundElite', $param2);
				return;
			case 'hit':
				$this->sendCallbacks('mode_onHitElite', $param2);
				return;
			case 'frag':
				$this->sendCallbacks('mode_onFragElite', $param2);
				return;
			
		}
	}

	// param = Victim:###;Shooter:###
	function mode_onPlayerHit($param) {
		$players = explode(';', $param);
		$victim = str_replace('Victim:', '', $players[0]);
		$shooter = str_replace('Shooter:', '', $players[1]);

		unset($this->lastHit[$victim]);
		$this->lastHit[$victim] = array('shooter' => $shooter,
									    'time' => time());

		$this->sendCallbacks('mode_onPlayerHit', $victim, $shooter);
	}

	function mode_onPlayerDeath($param) {
		$victim = $param;

		if(in_array($victim, array_keys($this->lastHit))) {
			$hit = $this->lastHit[$victim];
			if($hit['time'] == time() || $hit['time'] == (time()+1)) {
				$this->sendCallbacks('mode_onPlayerDeath', $param, $hit['shooter']);
			} else {
				// just died, probably by offzone
				$this->sendCallbacks('mode_onPlayerDeath', $param);
			}
		} else {
			// just died, probably by offzone
			$this->sendCallbacks('mode_onPlayerDeath', $param);
		}
	}

	function sendCallbacks($callback, $param = null, $param2 = null, $param3 = null) {
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $callback)) {
				if(is_null($param)) {
					$plugin->$callback();
				} else {
					if(is_null($param2)) {
						$plugin->$callback($param);
					} else {
						if(is_null($param3)) {
							$plugin->$callback($param, $param2);
						} else {
							$plugin->$callback($param, $param2, $param3);
						}
					}
				}
			}
		}
	}

	function registerPlugin($plugin, $class) {
		$this->plugins[$plugin] = $class;
	}

	//$this->callPublicMethod('MLEPP\Core', 'getPlayerInfo', $login);
	function getPlayerInfo($login) {
		$g =  "SELECT * FROM `players` WHERE `player_login` = ".$this->db->quote($login).";";
		$execute = $this->db->execute($g);

		if($execute->recordCount() == 1) {
			return $execute->fetchObject();
		} else {
			return false;
		}
	}

	function insertPlayer($player) {
		$g =  "SELECT * FROM `players` WHERE `player_login` = ".$this->db->quote($player->login).";";
		$execute = $this->db->execute($g);

		if($execute->recordCount() == 0) {
			$q = "INSERT INTO `players` (
					`player_login`,
					`player_nickname`,
					`player_nation`,
					`player_updatedat`
				  ) VALUES (
					'".$player->login."',
					".$this->db->quote($player->nickName).",
					".$this->db->quote(str_replace('World|', '', $player->path)).",
					'".date('Y-m-d H:i:s')."'
				  )";
		} else {
			$q = "UPDATE `players`
				  SET `player_nickname` = ".$this->db->quote($player->nickName).",
				      `player_nation` = ".$this->db->quote(str_replace('World|', '', $player->path)).",
				      `player_updatedat` = '".date('Y-m-d H:i:s')."'
				  WHERE `player_login` = '".$player->login."'";
		}

		$this->db->execute($q);
	}

	static function stripColors($input, $for_tm = true) {
		return
			//Replace all occurrences of a null character back with a pair of dollar
			//signs for displaying in TM, or a single dollar for log messages etc.
			str_replace("\0", ($for_tm ? '$$' : '$'),
				//Replace links (introduced in TMU)
				preg_replace(
					'/
				#Strip TMF H, L & P links by stripping everything between each square
				#bracket pair until another $H, $L or $P sequence (or EoS) is found;
				#this allows a $H to close a $L and vice versa, as does the game
				\\$[hlp](.*?)(?:\\[.*?\\](.*?))*(?:\\$[hlp]|$)
				/ixu',
					//Keep the first and third capturing groups if present
					'$1$2',
					//Replace various patterns beginning with an unescaped dollar
					preg_replace(
						'/
					#Match a single dollar sign and any of the following:
					\\$
					(?:
						#Strip color codes by matching any hexadecimal character and
						#any other two characters following it (except $)
						[0-9a-f][^$][^$]
						#Strip any incomplete color codes by matching any hexadecimal
						#character followed by another character (except $)
						|[0-9a-f][^$]
						#Strip any single style code (including an invisible UTF8 char)
						#that is not an H, L or P link or a bracket ($[ and $])
						|[^][hlp]
						#Strip the dollar sign if it is followed by [ or ], but do not
						#strip the brackets themselves
						|(?=[][])
						#Strip the dollar sign if it is at the end of the string
						|$
					)
					#Ignore alphabet case, ignore whitespace in pattern & use UTF-8 mode
					/ixu',
						//Replace any matches with nothing (i.e. strip matches)
						'',
						//Replace all occurrences of dollar sign pairs with a null character
						str_replace('$$', "\0", $input)
					)
				)
			)
			;
	}  // stripColors
}

?>