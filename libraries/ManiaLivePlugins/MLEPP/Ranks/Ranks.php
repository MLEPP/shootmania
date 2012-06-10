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

	public $ranks = array('0' => 'Private',
						  '150' => 'Private First Class',
						  '500' => 'Lance Corporal',
						  '800' => 'Corporal',
						  '2500' => 'Sergeant',
						  '5000' => 'Staff Sergeant',
						  '8000' => 'Gunnery Sergeant',
						  '20000' => 'Master Sergeant',
						  '30000' => 'First Sergeant',
						  '40000' => 'Master Gunnery Sergeant',
						  '50000' => 'Sergeant Major',
						  '60000' => '2nd Lieutenant',
						  '75000' => '1st Lieutenant',
						  '90000' => 'Captain',
						  '115000' => 'Major',
						  '125000' => 'Lieutenant Colonel',
						  '150000' => 'Colonel',
						  '180000' => 'Brigadier General',
						  '200000' => 'Major General',
						  '220000' => 'Lieutenant General',
						  '250000' => 'General');
	public $players = array();

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
		$this->enableDatabase();
		$this->enableDedicatedEvents();
		$this->enableTickerEvent();

		Console::println('['.date('H:i:s').'] [MLEPP] Plugin: Ranks v'.$this->getVersion() );
		$this->callPublicMethod('MLEPP\Core', 'registerPlugin', 'Ranks', $this);

		$this->onTick();

		$points = array_keys($this->ranks);
		foreach($this->storage->players as $player) {
			$this->players[$player->login] = array('score' => 0,
												   'rank' => $this->ranks[$this->closest($points, 0)]);
		}
	}

	function onPlayerConnect($login, $isSpectator) {
		$player = $this->storage->getPlayerObject($login);
		$points = array_keys($this->ranks);

		$this->players[$player->login] = array('score' => 0,
										       'rank' => $this->ranks[$this->closest($points, 0)]);
	}

	function mode_onEndMap($scores) {
		$players = explode(';', $scores);
		$points = array_keys($this->ranks);

		foreach($players as $player) {
			if (strpos($player, ':') !== false) {
				$arrayplayer = explode(':', $player);
				$playerinfo = $this->storage->getPlayerObject($arrayplayer[0]);
				if($arrayplayer[1] == '') $arrayplayer[1] = 0;
				if(isset($this->players[$arrayplayer[0]])) {
					if($this->ranks[$this->closest($points, ($this->players[$arrayplayer[0]]['score'] + $arrayplayer[1]))] != $this->players[$arrayplayer[0]]['rank']) {
						$this->connection->chatSendServerMessage('$fff»» '.$playerinfo->nickName.'$z$s$39f promoted from $fff'.$this->players[$arrayplayer[0]]['rank'].'$39f to $fff'.$this->ranks[$this->closest($points, ($this->players[$arrayplayer[0]]['score'] + $arrayplayer[1]))].'$39f!');
						Console::println('['.date('H:i:s').'] [MLEPP] [Ranks] '.$playerinfo->login.' promoted from '.$this->players[$arrayplayer[0]]['rank'].' to '.$this->ranks[$this->closest($points, ($this->players[$arrayplayer[0]]['score'] + $arrayplayer[1]))].'!');
					}
				}
				$this->players[$arrayplayer[0]] = array('score' => $this->players[$arrayplayer[0]]['score'] + $arrayplayer[1],
													    'rank' => $this->ranks[$this->closest($points, $arrayplayer[1])]);
				$q = "UPDATE `players` SET `player_points` = '".($this->players[$arrayplayer[0]]['score'] + $arrayplayer[1])."' WHERE `player_login` = '".$arrayplayer[0]."'";
				$this->db->query($q);
			}
		}
	}

	function getRank($login) {
		$players = array_keys($this->players);
		$points = array_keys($this->ranks);

		if(in_array($login, $players)) {
			return $this->players[$login];
		} else {
			$q = "SELECT `player_points` FROM `players` WHERE `player_login` = '".$login."'";
			$query = $this->db->query($q);
			$info = $query->fetchAll();

			return array('score' => $info->player_points,
						 'rank' => $this->ranks[$this->closest($points, $info->player_points)]);
		}
	}

	function closest($array, $number) {
		sort($array);
		return max(array_intersect($array, range(0,$number)));
	}
}

?>