<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Add/Remove Maps
 * @date 22-12-2012
 * @version 0.5.0
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
 * This program is distributed in the hope that it will b e useful,
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

namespace ManiaLivePlugins\MLEPP\AddRemoveMaps;

use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\PluginHandler\Dependency;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Event\Dispatcher;
use ManiaLive\Gui\Windowing\WindowHandler;
use ManiaLive\Gui\Windowing\Windows\Info;
use ManiaLivePlugins\MLEPP\AddRemoveMaps\Events\onTrackAdded;
use ManiaLivePlugins\MLEPP\AddRemoveMaps\Events\onTrackRemoved;
use ManiaLivePlugins\MLEPP\AddRemoveMaps\Gui\Windows\AddLocalWindow;
//use ManiaLivePlugins\MLEPP\AddRemoveMaps\Gui\Windows\RemoveWindow;

class AddRemoveMaps extends \ManiaLive\PluginHandler\Plugin {

	private $config;
    public static $mxLocation = 'sm.mania-exchange.com';

    /**
     * onInit()
     * Function called on initialisation of ManiaLive.
     *
     * @return void
     */
    function onInit() {
        $this->setVersion('0.5.0');
        $this->setPublicMethod('getVersion');
		$this->setPublicMethod('addLocalWin');
    }

    /**
     * onLoad()
     * Function called on loading of ManiaLive.
     *
     * @return void
     */
    function onLoad() {
        Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Add/Remove Maps v' . $this->getVersion());
        $this->config = Config::getInstance();

        if ($this->isPluginLoaded('MLEPP\Admin')) {
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addlocal'), array("addlocal"), true, false, false);
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addmx'), array("add"), true, false, false);

			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'remove'), array("remove"), true, false, false);
			$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'removethis'), array("removethis"), true, false, false);
        } else {
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] Disabled admin commands, Admin is not loaded, define admin plugin before this!');
        }
    }

    /**
     * onUnload()
     * Function called on unloading of the plugin
     * 	
     */
    function onUnLoad() {
        Console::println('[' . date('H:i:s') . '] [UNLOAD] Add/Remove Maps v' . $this->getVersion() . '');
        if ($this->isPluginLoaded('MLEPP\Admin')) {
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'add');   //remove full add mx command structure
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'addlocal');   //remove full add local command structure
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'remove'); // remove full remove command structure
			$this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'removethis'); // remove full remove command structure
            Console::println('[' . date('H:i:s') . '] [UNLOAD] [AddRemoveMaps] Removed all dependend add/remove commands from admin.');
        }
        parent::onUnload();
    }

    /**
     * addlocal()
     * Function adding track in tracklist from local source.
     *
     * @param mixed $fromLogin
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return
     */
    function addlocal($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        if (!AdminGroup::contains($fromLogin)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
            return;
        }

        $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
        $login = $admin->login;

        if (!is_string($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$i/admin add local takes a filename as a parameter.', $admin);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Missing parameter . . .');
            return;
        }

        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);

        $challengeDir = $dataDir . "Maps/";
        $mapExtensions = array("map.gbx", "map.Gbx", "Map.gbx", "Map.Gbx");


        $cpt = 0;
        $targetFile = false;
        while ($cpt < sizeof($mapExtensions) && $targetFile == false) {
            //echo $challengeDir . $param1 . "." . $mapExtensions[$cpt] . "\n";
            if (is_file($challengeDir . $param1 . "." . $mapExtensions[$cpt])) {
                $targetFile = $challengeDir . $param1 . "." . $mapExtensions[$cpt];
            }else
                $cpt++;
        }

        $isTmx = false;
        if ($targetFile !== false) {
            try {
                $this->connection->insertMap($targetFile);
				$mapinfo = $this->connection->getMapInfo($targetFile);
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 added new local track $fff' . $mapinfo->name.'$z$s$ff0!');
                Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Added new local track :' . $param1);
                $eventTargetFile = $targetFile;
                Dispatcher::dispatch(new onTrackAdded($login, $eventTargetFile, $isTmx));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
            }
        } else {
			$this->connection->chatSendServerMessage('$fff» $f00$iFile $fff' . $param1 . '.' . $mapExtensions[0] . ' $f00$i at location $fff' . $challengeDir . ' $f00$idoesn\'t exist.', $admin);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Tried to add new local track :' . $param1 . ', but it doesn\'t exist.');
        }
    }

    /**
     * addmx()
     * Handles the /addmx command.
     *
     * @param mixed $login
     * @param string $mxid
     * @return void
     */
    function addmx($login, $mxid = '') {
        $loginObj = $this->storage->getPlayerObject($login);

        if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }

        if (!is_numeric($mxid)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou have entered a non-numeric value for mx track. All mx tracks are numerical.', $login);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $loginObj->login . '] Use of non-numeric value for TMX track.');
            return;
        }

        $trackinfo = $this->getData('http://' . self::$mxLocation . '/api/tracks/get_track_info/id/' . $mxid . '?format=xml');
        if (is_int($trackinfo)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iAdding track from MX failed with http error $fff' . $trackinfo . '$f00$i.', $login);
            return;
        } else {
            $trackinfo = $read = simplexml_load_string($trackinfo);
        }

        if (!is_null($trackinfo)) {
            $trackdata = $this->getData('http://' . self::$mxLocation . '/tracks/download/' . $mxid);

            $dataDir = $this->connection->gameDataDirectory();
            $dataDir = str_replace('\\', '/', $dataDir);
            $challengeDir = $dataDir . $this->config->mxDownloadDir;
            if (!is_dir($challengeDir)) {
                mkdir($challengeDir, 0777, true);
            }

            if (strlen($trackdata) >= 1024 * 1024) {
                $size = round(strlen($trackdata) / 1024);
				$this->connection->chatSendServerMessage('$fff» $f00$iThe track you\'re trying to download is too large (' . $size . 'Kb > 1024 Kb).', $loginObj);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackfile is too large (' . $size . 'Kb > 1024 Kb).');
                return;
            }

            $targetFile = $challengeDir . $trackinfo->Name . '-' . $mxid . '.Map.Gbx';
            $eventTargetFile = $this->config->mxDownloadDir . $trackinfo->Name . '-' . $mxid . '.Map.Gbx';

            if (file_put_contents($targetFile, $trackdata) === false) {
				$this->connection->chatSendServerMessage('$fff» $f00$iCouldn\'t write trackdata. Check directory & file permissions at dedicated tracks folder!', $loginObj);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackdata couldn\'t been written. Check directory- and filepermissions!.');
                return;
            }

            $newChallenge = $this->connection->getMapInfo($targetFile);
            foreach ($this->storage->maps as $chal) {
                if ($chal->uId == $newChallenge->uId) {
					$this->connection->chatSendServerMessage('$fff» $f00$iThe track you tried to add is already in serverlist.', $loginObj);
                    Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Track already in the tracklist.');
                    return;
                }
            }
            try {
                $this->connection->insertMap($targetFile);
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $loginObj->nickName . '$z$s$ff0 added track $fff' . $trackinfo->Name . '$z$s$ff0 from $fffM$5DFX$0ae!');
                Dispatcher::dispatch(new onTrackAdded($login,$eventTargetFile, true));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);

                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Succesfully added track ' . $trackinfo->Name . '.');
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $login);
            }
        } else {
            // track unknown
			$this->connection->chatSendServerMessage('$fff» $f00$iThe track you\'re trying to download doesn\'t exist.', $loginObj);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Unknown track.');
        }
    }

	function getData($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLOPT_HTTPAUTH);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MLEPP ManiaExchange');

		$output = curl_exec($ch);

		curl_close($ch);

		return $output;
	}

    /**
     * removethis()
     * Function removes current track from tracklist.
     *
     * @param mixed $login
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return void
     */
    function removethis($login, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }
        $admin = Storage::GetInstance()->getPlayerObject($login);
        $challenge = $this->connection->getCurrentMapInfo();
        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);
        $file = $challenge->fileName;
        $challengeFile = $dataDir . "Maps/" . $file;

        $this->connection->removeMap($challengeFile);
		$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 removed this track from playlist.');
        Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Removed current track from the tracklist.');
        Dispatcher::dispatch(new onTrackRemoved($login, $challengeFile));
        $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);
    }

    /**
     * remove()
     * Function removes track from the tracklist.
     *
     * @param mixed $fromLogin
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return
     */
    /**
         * remove()
         * Function removes track from the tracklist.
         *
         * @param mixed $fromLogin
         * @param mixed $param1
         * @param mixed $param2
         * @param mixed $param3
         * @return
         */
        function remove($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
                if (!AdminGroup::contains($fromLogin)) {
                        $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
                        return;
                }
                if ($param1 == 'this') {
                        $this->removethis($fromLogin);
                        return;
                }

                $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
                $login = $admin->login;
                $param1 = (int) $param1;
                if ($param1 == null || !\is_numeric($param1) || $param1 < 0) {
                        Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Wrong use of /admin remove (use valid number).');
                        $this->connection->chatSendServerMessage('$fff» $f00$iWrong use of /admin remove (use valid number).', $admin);
                }

                if ($param1 > count($this->storage->maps)) {
                        $this->connection->chatSendServerMessage('$fff» $f00$iError. Invalid map number!', $login);
						Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] Error. Invalid map number!.');
                        return;
                }
                try {
                        $fileName = $this->storage->maps[($param1 - 1)]->fileName;
                        $name = $this->storage->maps[($param1 - 1)]->name;
                        
                        $this->connection->removeMap($fileName);
                        $this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 removed track $fff' . $name . '$z$s$ff0 from playlist.');
						Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] Removed track '.$name.' from playlist.');
                        $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
                } catch (\Exception $e) {
                        $this->connection->chatSendServerMessage('$fff» $f00$i'.$e->getMessage(), $login);
                }
        }

    /**
     * filterName()
     * Function used to filter the tracks filename.
     *
     * @param mixed $text
     * @return string $output
     */
    function filterName($text) {
        $str = trim(utf8_decode($text));
        $output = "";
        for ($i = 0; $i < strlen($str); $i++) {
            $c = ord($str[$i]);
            if ($c == 32) {
                $output .= "_";
                continue;
            } // space
            if ($c >= 48 && $c <= 57) {
                $output .= chr($c);
                continue;
            }// 0-9
            if ($c >= 65 && $c <= 90) {
                $output .= chr($c);
                continue;
            }// A-Z
            if ($c >= 97 && $c <= 122) {
                $output .= chr($c);
                continue;
            }// a-z
            $output .= "_";
        }
        return utf8_encode($output);
    }
	
	function addLocalWin($login, $overrideDir = false) {
		if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }
       	$window = AddLocalWindow::Create($login);
		$window->setSize(220, 120);

        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);
        $challengeDir = $dataDir . "Maps/";

        if ($overrideDir == false || empty($overrideDir)) {
            $overrideDir = $challengeDir;
        } else {
            if (!is_array($overrideDir) && $overrideDir !== false) {
                $this->addlocal($login, $overrideDir);
            } else {
                $overrideDir = $overrideDir[0];
            }
        }


        $localFiles = scandir($overrideDir);
        $arrayDirs = array();
        $arrayFiles = array();
        
        foreach ($localFiles as $file) {
            if (is_dir($overrideDir . $file)) {
                if ($file == "." || $file == "MatchSettings")
                    continue;
                if ($file == "..") {
                    if (strcmp($overrideDir, $challengeDir) == 0)
                        continue;
                    $tempdir = explode('/', $overrideDir);
                    $newDir = "";
                    for ($x = 0; $x < count($tempdir) - 2; $x++) {
                        $newDir .= $tempdir[$x] . "/";
                    }
                    $file = "";
                    $label = "..";
                } else {
                    $file.="/";
                    $newDir = $overrideDir;
                    $label = $file;
                }
                $arrayDirs[] = array
                    (
                    'Filename' => array(utf8_encode($label), array("changeDir", $newDir . $file), true),
                    'Action' => array("", NULL, false)
                );
            } else {
                if (!stristr($file, ".map.gbx") && !stristr($file, ".challenge.gbx"))
                    continue;

                $newDir = $overrideDir;
                $arrayFiles[] = array
                    (
                    'Filename' => array(utf8_encode($file), NULL, false),
                    'Action' => array("Add", array(($newDir . "/" . $file), $file), false)
                );
            }
         }
         
         // add directories and files to window, directories first!
         foreach ($arrayDirs as $entry) {
		 $window->setInfos($entry);
         }
         
         foreach ($arrayFiles as $entry) {
         }
		$window->centerOnScreen();
		$window->show();
    }
}
?>