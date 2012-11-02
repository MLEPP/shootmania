<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name IRC
 * @date 09-09-2012
 * @version 0.4.0
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
		$version = '0.4.0';
		$this->setVersion($version);
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('tellIRC');
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
	/*
	*
	*		Match reporting
	*
	*/
	function mode_onBeginMap($chal) {
		if(in_array('beginRace', $this->config->disable)) {return;}
			$map = $this->connection->getCurrentMapInfo();
			$this->say('4Begin of the map');
			$this->say('Map: '.Core::stripColors($map->name).' by '.Core::stripColors($map->author));
	}
	function mode_onPoleCapture($login) {
		if(in_array('eliteCap', $this->config->disable)) {return;}
	$map = $this->connection->getCurrentMapInfo();
	$this->say('15,14 PoleCapture by: '.$login.' on '.Core::stripColors($map->name).'');
	}
	function mode_onStartRoundElite($param2) {
		if(in_array('startEliteround', $this->config->disable)) {return;}
	$map = $this->connection->getCurrentMapInfo();
	$this->say('15,14 StartRound: No: '.$param2.' on '.Core::stripColors($map->name).'');
	}
	function mode_onEndRoundElite($param2) {
	if(in_array('endEliteround', $this->config->disable)) {return;}
	$map = $this->connection->getCurrentMapInfo();
	$EndRoundData = explode(';', $param2);
	$WinSide = str_replace('WinSide:', '', $EndRoundData[0]);
	$Side = str_replace('Side:', '', $EndRoundData[1]);
	$Wincondition = str_replace('WinCondition:', '', $EndRoundData[2]);
	if ($Wincondition == 1){
	$this->say('12,15 EndRound: '.$Side.' Win by timelimit on '.Core::stripColors($map->name).'');
	}
	if ($Wincondition == 2){
	$this->say('12,15 EndRound: '.$Side.' Win by reaching pole on '.Core::stripColors($map->name).'');
	}
	if ($Wincondition == 3){
	$this->say('12,15 EndRound: '.$Side.' Win by elimination of attack player on '.Core::stripColors($map->name).'');
	}
	if ($Wincondition == 4){
	$this->say('12,15 EndRound: '.$Side.' Win by elimination of all defense players on '.Core::stripColors($map->name).'');
	}
	}
	function mode_onHitElite($param){
	if(in_array('eliteHit', $this->config->disable)) {return;}
	$players = explode(';', $param);
	$shooter = str_replace('Shooter:', '', $players[0]);
	$victim = str_replace('Victim:', '', $players[2]);
	$weaponnum = str_replace('WeaponNum:', '', $players[1]);
	if($weaponnum == 1){
	sleep(1);
	$this->say('12,14 '.$victim.' was hit by a Railgun from '.$shooter.'');
	}
	if($weaponnum == 2){
	sleep(1);
	$this->say('12,14 '.$victim.' was hit by a Rocket from '.$shooter.'');
	}
	}
	function mode_onFragElite($param){
	if(in_array('eliteFrag', $this->config->disable)) {return;}
	$players = explode(';', $param);
	$shooter = str_replace('Shooter:', '', $players[0]);
	$victim = str_replace('Victim:', '', $players[2]);
	$weaponnum = str_replace('WeaponNum:', '', $players[1]);
	if($weaponnum == 1){
	sleep(1);
	$this->say('4,15 '.$victim.' was killed by a Railgun from '.$shooter.'');
	}
	if($weaponnum == 2){
	sleep(1);
	$this->say('4,15 '.$victim.' was killed by a Rocket from '.$shooter.'');
	}
	}
	/*
	*
	*		Message Parser
	*
	*/
	function onTick() {
		if(!isset($this->i)) {
			$this->i = 0;
		}

		if($this->joined === true) {
			stream_set_timeout($this->socket, 10);
			stream_set_blocking($this->socket, 0);
			$gonogo = "gogo";
			$info = stream_get_meta_data($this->socket);
               if (($info['timed_out'] || $info['eof'] || !$this->socket) && $gonogo != 'nogo' ) {
					Console::println(date('[m/d,H:i:s]').' time_out: '.$info['timed_out'].' -- eof: '.$info['eof']);
					$this->joined = FALSE;
					$gonogo = 'nogo';
					$this->reconnect();
				}
			while(!feof($this->socket)) {
				$data = fread($this->socket, 4096);
				if($data == "\n" || $data == "") {
					break;
				} else {
					$data = trim($data);

					$name_buffer = explode(' ', str_replace(':', '', $data));
					$ircuser = substr($name_buffer[0], 0, strpos($name_buffer[0], '!'));

					echo $data."\n\r";
					// On chat event
					if($name_buffer[1] == 'PRIVMSG') {
						if(!in_array('chatIRCtoTM', $this->config->disable)) {
							if(substr($name_buffer[2], 0, 1) == '#') {
								$message = $data;
								$message = str_replace($name_buffer[0].' ', '', $message);
								$message = str_replace($name_buffer[1].' ', '', $message);
								$message = str_replace($name_buffer[2].' ', '', $message);
								$message = substr($message, 2);
                                if (ISSET($name_buffer[5])){
								if ($name_buffer[5] == 'none'){$name_buffer[5] = '';}
								$d_message = $name_buffer[5];
								if (empty($d_message)) {
								$d_message = "";
								}
                                    break;
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
					// On chat general command event (ALL WORKS)
					if($name_buffer[1] == 'PRIVMSG') {
						// Console::println('IRC : GeneralChatCommand');
						// Console::println( print_r($name_buffer) );
						if(!in_array('IRCCommands', $this->config->disable)) {
							if(substr($name_buffer[2], 0, 1) == '#') {
								$message = $data;
								$message = str_replace($name_buffer[0].' ', '', $message);
								$message = str_replace($name_buffer[1].' ', '', $message);
								$message = str_replace($name_buffer[2].' ', '', $message);
								$message = substr($message, 2);
                                if (ISSET($name_buffer[3])){
								
								switch ($name_buffer[3])	{
									case "!version":
										$this->say('!version : Running MLEPP IRC Bot r'.$this->getVersion().'.');
										break;
									case "!players":
										$this->sendPlayerCount();
										break;
									case "!spectators":
										$this->sendSpecCount();
										break;
									case "!pcw":
										$filter = array('!pcw ', 'pcw');
										$message = str_replace($filter, '', $message);
										$this->connection->chatSendServerMessage('$f00[IRC - $fffChat$f00] [$fff'.$ircuser.'$f00] $fff'.$message);
										break;
									case "3on3":
										$this->connection->chatSendServerMessage('$f00[IRC - $fffChat$f00] [$fff'.$ircuser.'$f00] $fff'.$message);
										break;
								}
								}
							}
						}
					}
					/*
					*
					*		Take !admin commands from irc
					*
					*/
					if($name_buffer[1] == 'PRIVMSG' && $name_buffer[2] == $this->config->nickname && $name_buffer[3] == "!admin" && $name_buffer[4] == $this->config->adminpass) {
						if(!in_array('IRCAdminCommands', $this->config->disable)) {
								$message = $data;
								$message = str_replace($name_buffer[0].' ', '', $message);
								$message = str_replace($name_buffer[1].' ', '', $message);
								$message = str_replace($name_buffer[2].' ', '', $message);
								$message = str_replace($name_buffer[3].' ', '', $message);
								$message = str_replace($name_buffer[4].' ', '', $message);
								$message = str_replace($name_buffer[5].' ', '', $message);
								$message = substr($message, 2);
								// Console::println('MESSAGE :: '.$message);
								switch ($name_buffer[5])	{
									case "announce":
										$this->connection->chatSendServerMessage('$f00[ANNOUNCEMENT] [$fff'.$ircuser.'$f00] $fff'.$message);
										$text = Core::stripColors($message);
										$this->say('Announcing : '.$text, $ircuser);
										break;
									case "test":
										Console::println('IRC : test');
										break;
									case "pass":
										unset($text);$text = (string) "";
										$pass = $this->connection->getServerPassword();$pass = (string) $pass;
										$specpass = $this->connection->getServerPasswordForSpectator();$specpass = (string) $specpass;
										if($pass !== ""){ $text = 'Password:'.$pass.' '; }
										if($specpass !== ""){ $text .= 'SpecPassword:'.$specpass; }
										if($text == "") { $text = 'No passwords are set'; }
										$this->say(  $text, $ircuser);
										break;
										
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
					if ($name_buffer[0] == 'ERROR' && $name_buffer[1] == 'Closing' && $name_buffer[3] == 'Link'){
                fclose($this->socket);
                sleep(2);
				$this->starter();
					break;
					}
				}
			}
		}
	}
	function reconnect(){
    if ( $this->joined === FALSE){
        $now = date('His') + 0;
        Console::println(date('[m/d,H:i:s]').' now: '.$now);
        if ( ISSET($time_of_con) && $time_of_con < $now){ // retry directly after connected for 45 seconds
            $timer = $now;
           Console::println(date('[m/d,H:i:s]').' Disconnected from IRC, trying to reconnect...');
                   $this->starter();
        } else { // if connected less then 45 seconds, wait 30sec before retrying
            $timer = date('His', mktime(date('H'),date('i'),date('s')+30,0,0,0)) + 0;
            Console::println(date('[m/d,H:i:s]').' Disconnected from IRC, trying to reconnect in 30 seconds...');
                        $this->starter();
        }
    }
	}
	/*
	*
	*		Command Resolver Functions
	*
	*/
	function sendServerpass($param)	{
		if (empty($param)) {
			$param = "";
		}

		try {
			$this->connection->setServerPassword($param);
		$say = '!server password '.$param.' ';
		$this->say($say);
		} catch (\Exception $e) {
			$say = '!server password ' . $e->getMessage();
		}
	}
	function sendSpecpass($param) {
		if (empty($param)) {
			$param = "";
		}

		try {
			$this->connection->setServerPasswordForSpectator($param);
		$say = '!spec password '.$param.' ';
		$this->say($say);
		} catch (\Exception $e) {
			$say = '!spec password ' . $e->getMessage();
		}
	}
	function sendPlayerCount() {
		$maxplayers = $this->connection->getMaxPlayers();
		$say = '!players ('.$this->playercount().'/'.$maxplayers['CurrentValue'].'): ';
		if($this->playercount() == 0 || $this->playercount() == '0') {
			if($this->config->silentOnNone){return;}
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
			if($this->config->silentOnNone){return;}
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
	/*
	*
	*		SM Server Events
	*
	*/
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
	/*
	*
	*		Plugin UnLoad Functions
	*
	*/
	function onTerminate() {
		$this->write('QUIT');
		fclose($this->socket);
	}
	function onUnload() {
		$this->onTerminate();
		parent::onUnload();
	}
	/*
	*
	*		IRC Server interaction
	*
	*/
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
	function irc_prep2($type, $message, $nick) {
		for($i = 0; isset($this->config->channels[$i]); $i++) {
		$this->write('PRIVMSG '. $nick .', '. $this->config->channels[$i] .' :'.$type.''.$message);
		}
	}
	/**
	*	Public function to let other plugins announce stuff
	*	$this->callPublicMethod('MLEPP\IRC', 'tellIRC', $ARRAY);
	*	$ARRAY ( message , source , channel(optional) )
	*/
	function tellIRC($array) {
		$source = $array[1];
		$message = $array[0];
		if(!in_array('tellIRCmessage', $this->config->disable) && is_array($array) && !empty($array)) {
			if(isset($array[2])){$reciever=$array[2];}else{$reciever='a.channels';}
				if($reciever == 'a.channels') {
					for($i = 0; isset($this->config->channels[$i]); $i++) {
						$this->write('PRIVMSG '.$this->config->channels[$i].' :['.$source.']'.$message);
					}
				} else {
					// this only supports a single channel right now
					$this->write('PRIVMSG '.$reciever.' :['.$source.']'.$message);
				}
		} else {
				Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: IRC. '.$source.' tried to announce to IRC.');
				if(!is_array($array) | empty($array)) { Console::println('[' . date('H:i:s') . '] No array passed to tellIRC or it was empty.'); }
				if(in_array('tellIRCmessage', $this->config->disable)) { Console::println('[' . date('H:i:s') . ']Feature tellIRC disabled in pluginconfig.'); }
		}
	}
}
?>