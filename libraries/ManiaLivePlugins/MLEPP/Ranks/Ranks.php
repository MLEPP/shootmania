<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack for ShootMania
 *
 * -- MLEPP Plugin --
 * @name Ranks
 * @date 27-05-2012
 * @version 0.1.0
 * @website mlepp.com
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

namespace ManiaLivePlugins\MLEPP\Ranks;

use ManiaLive\Utilities\Console;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Config\Loader;
use ManiaLive\Utilities\Logger;

class Ranks extends \ManiaLive\PluginHandler\Plugin {

	public $ranks = array(0 => 'Private',
						  2 => 'Testrank',
						  150 => 'Private First Class',
						  500 => 'Lance Corporal',
						  800 => 'Corporal',
						  2500 => 'Sergeant',
						  5000 => 'Staff Sergeant',
						  8000 => 'Gunnery Sergeant',
						  20000 => 'Master Sergeant',
						  30000 => 'First Sergeant',
						  40000 => 'Master Gunnery Sergeant',
						  50000 => 'Sergeant Major',
						  60000 => '2nd Lieutenant',
						  75000 => '1st Lieutenant',
						  90000 => 'Captain',
						  115000 => 'Major',
						  125000 => 'Lieutenant Colonel',
						  150000 => 'Colonel',
						  180000 => 'Brigadier General',
						  200000 => 'Major General',
						  220000 => 'Lieutenant General',
						  250000 => 'General');
	public $players = array();
	private $timeBeforeCalc = 20;
	private $times = 0;

	/**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */

	function onInit() {
		$this->setVersion('0.1.0');
		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('getRank');
	}

	/**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */

	function onLoad() {
		$this->enableDedicatedEvents();
		$this->enableTickerEvent();

		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Ranks v'.$this->getVersion() );

		$this->onTick();
	}
	
	function onRulesScriptCallback($param1, $param2){
	var_dump($param1);
	var_dump($param2);
	if ($param1 = "UnloadMap"){
	}
	if ($param1 = "OnHit"){
	$data = explode(";", $param2);
	$PlayerShooter = $data[0];
	$PlayerHits = $data[1];
	$PlayerVictim = $data[2];
	$log = Logger::getLog('OnHit');
	$log->write("Rankings for '{$this->storage->currentMap->name}' ({$this->storage->currentMap->uId}):");
	$log->write(" Shooter: '{$PlayerShooter}' Victim: '{$PlayerVictim}' Hits:'{$PlayerHits}' ");
	}
	}

	function onTick() {
		$this->timeBeforeCalc--;
		$this->times++;
		if($this->timeBeforeCalc === 0) {
			Console::println('['.date('H:i:s').'] Another 20 seconds, calculating ranks...');
			foreach($this->storage->players as $player) {
				$points = array_keys($this->ranks);
				//$rankinfo = $this->connection->getCurrentRankingForLogin($player->login);
				if($player->score == '') $player->score = 0;
				if(isset($this->players[$player->login])) {
					if($this->ranks[$this->closest($points, $player->score)] != $this->players[$player->login]['rank']) {
						$this->connection->chatSendServerMessage('$fff»» '.$player->nickName.'$z$s$39f promoted from $fff'.$this->players[$player->login]['rank'].'$39f to $fff'.$this->ranks[$this->closest($points, $player->score)].'$39f!');
					}
				}
				$this->players[$player->login] = array('score' => $player->score,
													   'rank' => $this->ranks[$this->closest($points, $player->score)]);
				//print_r($rankinfo);
			}
			//print_r($this->players);
			$this->timeBeforeCalc = 20;
		}
	}

	function getRank($login) {
		$players = array_keys($this->players);
		if(!in_array($login, $players)) {
			$player = $this->storage->getPlayerObject($login);
			$points = array_keys($this->ranks);
			if($player->score == '') $player->score = 0;
			$this->players[$player->login] = array('score' => $player->score,
												   'rank' => $this->ranks[$this->closest($points, $player->score)]);
		}
		return $this->players[$login];
	}

	function closest($array, $number) {
		sort($array);
		foreach($array as $a) {
			if($a <= $number) return $a;
		}
	}
}

?>