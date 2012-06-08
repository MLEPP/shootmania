<?php
/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Core --
 * @name Core
 * @date 07-06-2012
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

namespace ManiaLivePlugins\MLEPP\Core;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;

class Core extends \ManiaLive\PluginHandler\Plugin {

	private $plugins = array();

	function onInit() {
		$this->setVersion('0.1.0');
		$this->setPublicMethod('registerPlugin');
	}

	function onLoad() {
		$this->enableDedicatedEvents();
		Console::println('[' . date('H:i:s') . '] [MLEPP] Core v' . $this->getVersion());
		$this->connection->chatSendServerMessage('$fff» $fa0Welcome, this server is running $fffMLEPP for ShootMania$fa0!');
	}

	function registerPlugin($plugin, $class) {
		$this->plugins[$plugin] = $class;
	}

	function onRulesScriptCallback($param1, $param2) {
		switch($param1) {
			case 'beginMap':
				$this->callMethods('mode_onBeginMap', $param2);
				return;
			case 'endMap':
				$this->callMethods('mode_onEndMap', $param2);
				return;
			case 'beginRound':
				$this->callMethods('mode_onBeginRound', $param2);
				return;
			case 'endRound':
				$this->callMethods('mode_onEndRound', $param2);
				return;
		}
	}

	function callMethods($callback, $param = null) {
		foreach($this->plugins as $plugin) {
			if(method_exists($plugin, $callback)) {
				if(is_null($param)) {
					$plugin->$callback();
				} else {
					$plugin->$callback($param);
				}
			}
		}
	}
}

?>