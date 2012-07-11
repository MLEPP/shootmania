<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name AudioNotations
 * @date 11-07-2012
 * @version 0.1.0
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

namespace ManiaLivePlugins\MLEPP\AudioNotations;

use ManiaLive\Data\Storage;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Utilities\Console;
use ManiaLivePlugins\MLEPP\AudioNotations\Gui\Windows\WindowAudio;

class AudioNotations extends \ManiaLive\PluginHandler\Plugin {

	private $players = array();

	public function onInit() {
		$this->setVersion('0.1.0');
		
	}

	function onLoad() {
		$this->enableDedicatedEvents();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: AudioNotations v' . $this->getVersion());
		//$this->registerChatCommand("test", "sendTest", 0);
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'AudioNotations', $this);		
	}
	
	function onReady() {
		$this->mode_onBeginRound("");
	}
	
	public function mode_onBeginRound($mapName) {		
		// $this->debug("player data reset");
		foreach ($this->storage->players as $player) {
			$this->players[$player->login] = array("hits" => 0);
		}
		
	}
	public function onPlayerConnect($login, $isSpectator) {
		$this->preloadAudio($login);
		
	}
	public function mode_onPlayerHit($victim, $shooter) {
		$this->players[$shooter]['hits']++;
		//$this->debug($shooter . " hit count now: " . $this->players[$shooter]['hits']);

		switch ($this->players[$shooter]['hits']) {
			case 8:
			//	$this->debug('$o$fffaudio trigger: $0d0Impressive!');
				$this->sendAudioNotation("impressive.ogg");
				break;
			case 14:
			//	$this->debug('$o$fffaudio trigger: $d00GODLIKE!');
				$this->sendAudioNotation("godlike.ogg");
				break;
			default:
				break;
		}
		
	}
	
	private function debug($message) {
		$this->connection->chatSendServerMessage("debug:" . $message);
	}
	
	public function sendTest() {
		$this->debug("audio test send to all players!");
		$this->sendAudioNotation("godlike.ogg");		
	}
	
	function sendAudioNotation($filename) {
		$xml = '<manialinks><manialink id="16000"><audio pos="190 0 0" play="1" looping="0">http://koti.mbnet.fi/reaby/manialive/audio/'.$filename.'</audio></manialink></manialinks>'; 			
		$this->connection->sendDisplayManialinkPage($this->storage->players, $xml, 0, false, false);		
	}
	
	function preloadAudio($login) {
		$xml = '<manialinks><manialink id="16000"><audio pos="190 0 0" play="0" looping="0">http://koti.mbnet.fi/reaby/manialive/audio/godlike.ogg</audio><audio pos="190 0 0" play="0" looping="0">http://koti.mbnet.fi/reaby/manialive/audio/impressive.ogg</audio></manialink></manialinks>'; 			
		$player = $this->storage->getPlayerObject($login);
		$this->connection->sendDisplayManialinkPage($player, $xml, 0, false, true);
	}
	
	
}

?>