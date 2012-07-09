<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name IRC
 * @date 06-09-2011
 * @version v0.2.3
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The MLEPP team
 * @copyright 2010 - 2011
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

namespace ManiaLivePlugins\MLEPP\IRC;

use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\Time;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;

use ManiaLivePlugins\MLEPP\Core\Core;

class IRC extends \ManiaLive\PluginHandler\Plugin {

	private $socket;
	private $joined = false;
	private $i;

	private $mlepp;
	private $config;

	/**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */

	function onInit() {
		$version = '0.2.3';
		$this->setVersion($version);
		$this->setPublicMethod('getVersion');

		$this->config = Config::getInstance();
	}

	/**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */

	function onLoad() {
		$this->enableDedicatedEvents();
		$this->enableApplicationEvents();
		$this->enableTickerEvent();

		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: IRC Bot v'.$this->getVersion());
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'IRC', $this);

		$this->socket = fsockopen($this->config->server, $this->config->port);

		$this->write('USER '.$this->config->ident.' '.$this->config->hostname.' '.$this->config->server.' :'.$this->config->realname);
		$this->write('NICK '.$this->config->nickname);

		$this->starter();
	}

	function starter() {
		$this->joined = false;
		if($this->joined === false) {
			while($data = fgets($this->socket, 4096)) {
				if($this->joined !== false) break;
				if($data != "\n") {
					$eData = explode(" ",$data);
					for($i = 0; isset($eData[$i]); $i++) {
						$eData[$i] = trim($eData[$i]);
					}

					echo $data;

					$name_buffer = explode(' ', str_replace(':', '', trim($data)));

					if($name_buffer[0] == 'PING') {
						$this->write('PONG '.$name_buffer[1]);
					}

					if($this->joined == false && (strstr($data,'MOTD') || strstr($data,'message of the day'))) {
						if($this->config->authmethod == 'nickserv') {
							$this->write('NICKSERV IDENTIFY '.$this->config->authpass);
							sleep(1);
						} elseif($this->config->authmethod == 'qauth') {
							$this->write('AUTH '.$this->config->nickname.' '.$this->config->authpass);
							sleep(1);
						}

						for($i = 0; isset($this->config->channels[$i]); $i++) {
							$this->write('JOIN '.$this->config->channels[$i]);
						}
						sleep(1);
						$this->say('Running MLEPP IRC Bot v'.$this->getVersion().'.');
						$this->joined = true;
						break;
					}
				}
			}
		}
	}

	function mode_onBeginMap($chal) {
		if(!in_array('beginRace', $this->config->disable)) {
			$map = $this->connection->getCurrentMapInfo();
			$this->say('4Begin of the map');
			$this->say('Map: '.Core::stripColors($map->name).' by '.Core::stripColors($map->author));
		}
	}

	function onTick() {
		if(!isset($this->i)) {
			$this->i = 0;
		}

		if($this->joined === true) {
			stream_set_timeout($this->socket, 10);
			stream_set_blocking($this->socket, 0);
			while(!feof($this->socket)) {
				$data = fread($this->socket, 4096);
				if($data == "\n" || $data == "") {
					break;
				} else {
					$data = trim($data);

					$name_buffer = explode(' ', str_replace(':', '', $data));
					$ircuser = substr($name_buffer[0], 0, strpos($name_buffer[0], '!'));

					echo $data."\n\r";

					if($name_buffer[1] == 'PRIVMSG') {
						if(!in_array('chatIRCtoTM', $this->config->disable)) {
							if(substr($name_buffer[2], 0, 1) == '#') {
								$message = $data;
								$message = str_replace($name_buffer[0].' ', '', $message);
								$message = str_replace($name_buffer[1].' ', '', $message);
								$message = str_replace($name_buffer[2].' ', '', $message);
								$message = substr($message, 2);
								if($message == '!version') {
									$this->say('!version : Running MLEPP IRC Bot r'.$this->getVersion().'.');
								} elseif($message == '!players') {
									$this->sendPlayerCount();
								} elseif($message == '!spectators') {
									$this->sendSpecCount();
								} else {
									if(strstr($message, 'ACTION ')) {
										$message = str_replace('ACTION ', '', $message);
										$this->connection->chatSendServerMessage('$f00[IRC - $fffChat$f00] $fff'.$ircuser.' '.$message);
									} else {
										$this->connection->chatSendServerMessage('$f00[IRC - $fffChat$f00] [$fff'.$ircuser.'$f00] $fff'.$message);
									}
								}
							}
						}
					}

					if($name_buffer[1] == 'JOIN') {
						if(!in_array('joinIRCMessage', $this->config->disable)) {
							$this->connection->chatSendServerMessage('$f00[IRC - $fffJoin$f00] $fff'.$ircuser);
						}
					}

					if($name_buffer[1] == 'PART') {
						if(!in_array('leaveIRCMessage', $this->config->disable)) {
							$this->connection->chatSendServerMessage('$f00[IRC - $fffLeave$f00] $fff'.$ircuser);
						}
					}

					if($name_buffer[0] == 'PING') {
						$this->write('PONG '.$name_buffer[1]);
					}
					break;
				}
			}
		}
	}

	function sendPlayerCount() {
		$maxplayers = $this->connection->getMaxPlayers();
		$say = '!players ('.$this->playercount().'/'.$maxplayers['CurrentValue'].'): ';
		if($this->playercount() == 0 || $this->playercount() == '0') {
			$say .= 'none';
		} else {
			$i = 0;
			foreach($this->storage->players as $player) {
				$i++;
				if($i != 1 && $i != '1') {
					$say .= ', ';
				}
				$say .= Core::stripColors($player->nickName);
			}
		}
		$this->say($say);
	}

	function sendSpecCount() {
		$maxspectators = $this->connection->getMaxSpectators();
		$say = '!spectators ('.$this->speccount().'/'.$maxspectators['CurrentValue'].'): ';
		if($this->speccount() == 0 || $this->speccount() == '0') {
			$say .= 'none';
		} else {
			foreach($this->storage->spectators as $spectator) {
				//$playerObject = $this->storage->getPlayerObject($login);
				$say .= Core::stripColors($spectator->nickName).' ';
			}
		}
		$this->say($say);
	}

	function playercount() {
		$players = 0;
		foreach($this->storage->players as $login => $player){
			$players++;
		}
		return $players;
	}

	function speccount() {
		$players = 0;
		foreach($this->storage->spectators as $login){
			$players++;
		}
		return $players;
	}

	function onPlayerChat($PlayerUid, $Login, $Text, $IsRegistredCmd) {
		if(!in_array('chatTMtoIRC', $this->config->disable)) {
			if($IsRegistredCmd === false && $Login != $this->storage->serverLogin) {
				if(strpos($Text, '/admin') === false) {
					$playerObject = $this->storage->getPlayerObject($Login);
					$this->say('4[Chat - 1'.Core::stripColors($playerObject->nickName).'4]1 '.Core::stripColors($Text));
				}
			} else {
				if(strstr($Text, '$f00[Site - $fffChat$f00] [$fff')) {
					//$this->say('4[Chat - 1'.stripColors($playerObject->nickname).'4]1 '.stripColors($Text));
					$tex = Core::stripColors($Text);
					$tex = str_replace('[Site - Chat]', '', $tex);
					$user = strstr($tex, ']', true);
					$tex = str_replace($user.'] ', '', $tex);
					$send = '4[Site -1'.str_replace('[', '', $user).'4]1 '.$tex;
					$this->say($send);
				}
			}
		}
	}

	function onTerminate() {
		$this->write('QUIT');
		fclose($this->socket);
	}

	function onPlayerConnect($login, $isSpectator) {
		if(!in_array('joinTMMessage', $this->config->disable)) {
			$playerObject = $this->storage->getPlayerObject($login);
			$this->say('4[Join]1 '.Core::stripColors($playerObject->nickName).' joined the game.');
		}
	}

	function onPlayerDisconnect($login) {
		if(!in_array('leaveTMMessage', $this->config->disable)) {
			$playerObject = $this->storage->getPlayerObject($login);
			$this->say('4[Leave]1 '.Core::stripColors($playerObject->nickName).' left the game.');
		}
	}

	function onUnload() {
		$this->onTerminate();
		parent::onUnload();
	}

	function write($data) {
		fwrite($this->socket, $data."\r\n");
		echo 'Write: '.$data."\r\n";
	}

	function say($message, $reciever = 'a.channels') {
		if($reciever == 'a.channels') {
			for($i = 0; isset($this->config->channels[$i]); $i++) {
				$this->write('PRIVMSG '.$this->config->channels[$i].' :'.$message);
			}
		} else {
			$this->write('PRIVMSG '.$reciever.' :'.$message);
		}
	}
}
?>