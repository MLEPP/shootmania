<?php
/**
 * Change map plugin v.0.1.1 (01/08/12)
 * @author dfk7677
 * @copyright 2012
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
namespace ManiaLivePlugins\MLEPP\ChangeMap;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\PluginHandler\Dependency;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Event\Dispatcher;
use ManiaLive\DedicatedApi\Structures;
use ManiaLivePlugins\MLEPP\ChangeMap\Gui\Windows\MapsWindow;

class ChangeMap extends \ManiaLive\PluginHandler\Plugin {


	/**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */
    function onInit() {
        $this->setVersion('0.2.0');
        $this->setPublicMethod('getVersion');
		
    }

	/**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */
    function onLoad() {
        Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Change Map v' . $this->getVersion());
        MapsWindow::$adminPlugin = $this;

        if ($this->isPluginLoaded('MLEPP\Admin')) {
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'map'), array("map"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'maps'), array("maps"), true, false, false);
           
        } else {
            Console::println('[' . date('H:i:s') . '] [MLEPP] [ChangeMap] Disabled admin commands, Admin is not loaded, define admin plugin before this!');
        }
    }
	
	
	 /**
     * onUnload()
     * Function called on unloading of the plugin
     * 	
     */
	function onUnLoad() {
        Console::println('[' . date('H:i:s') . '] [UNLOAD] Change Map v' . $this->getVersion() . '');
        if ($this->isPluginLoaded('MLEPP\Admin')) {
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'map');   //remove command
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'maps');   //remove command
            Console::println('[' . date('H:i:s') . '] [UNLOAD] [ChangeMap] Removed all dependend map commands from admin.');
        }
        parent::onUnload();
    }
	
	
	/**
     * map()
     * Changes map.
     *
     * @param mixed $login
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return void
     */
    function map($login, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }
		
		if($param1 == NULL) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou must specify a map name!', $login);
			return;
		}
        $mapList = $this->connection->getMapList(128,0);
		$numMaps = count($mapList);
		$mapIndex = -1;
		for ($i =0 ;$i <$numMaps ; $i++) {
			$name = $this->filterName($mapList[$i]->name);
			if(stripos($name,$param1)!==false) {
				if ($mapIndex >=0) {
					
					$mapIndex = -2;
					break;
				}				
				$mapIndex = $i;
				$mapName = $name;
			}
		}
		
		
		$admin = $this->storage->getPlayerObject($login);
		
		if ($mapIndex>=0)
		{
		
			if($mapIndex != $this->connection->getCurrentMapIndex() ) {
		
				try {
		
					$this->connection->setNextMapIndex($mapIndex);
					$this->connection->nextMap();
			
					$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 changed map to '.$mapList[$mapIndex]->name);
				}
				catch (Exception $e) {
			
					$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $login);
				}
				Console::println('[' . date('H:i:s') . '] [MLEPP] [Change Map] [' . $admin->login . '] Changed map to ' . $mapName);
			}
			else
				$this->connection->chatSendServerMessage('$fff» $f00$iThis map is already being played!',$login);
				
		}
		else if ($mapIndex==-1)
		{
			
			$this->connection->chatSendServerMessage('$fff» $f00$iNo map found!',$login);
		}
		else {
			
			$this->connection->chatSendServerMessage('$fff» $f00$iMore than one maps found!',$login);
		}
		

	}
	
	
	function maps($login) {
		if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }
		
			$player = $this->storage->getPlayerObject($login);
			$window = MapsWindow::Create($player->login);
			$window->setSize(180, 120);
			$window->centerOnScreen();
			$window->show();
			return;
		
	}
	
	function onClick($login, $action, $target) {
		//$this->connection->chatSendServerMessage("$login --> $action --> $target", $this->storage->getPlayerObject($login));

		switch ($action) {
			case 'change':
				$this->map($login,$this->filterName($target));
				break;
			}
	}
	
	//Strips colors and text formatting from map name
	function filterName($text) {
        
        $output = "";
        for ($i = 0; $i < strlen($text); $i++) {
			$c = $text[$i];
			
			if ($c == '$') {
				if ($i+1<=strlen($text)) {
					$c2 = ord ($text[$i+1]);
					if($text[$i+1] == '$') {
						$output .= '$';
					}				
					else if ($c2 >= 48 && $c2 <= 57) {
						$i+=3;
					}
					else if ($c2 >= 97 && $c2 <= 102) {
						$i+=3;
					}
					else if ($c2 >= 65 && $c2 <= 70) {
						$i+=3;
					}
					else {
						$i++;
					}
				}
			}
			else {
				$output .= $c;
			}
			
        }
        return $output;
    }
	
}

?>