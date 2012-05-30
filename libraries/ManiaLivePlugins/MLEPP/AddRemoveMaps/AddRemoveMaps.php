<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Add/Remove Maps
 * @date 26-06-2011
 * @version 0.1.0
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
use ManiaLivePlugins\MLEPP\AddRemoveMaps\Gui\Windows\RemoveWindow;

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
        $this->setVersion('0.1.0');
        $this->setPublicMethod('getVersion');
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
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addLocalWin'), array("add", "track", "local"), true, false, false);
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'RemoveWindow'), array("remove", "track"), true, false, false);
            $this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'addmx'), array("add", "track", "mx"), true, false, false);
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
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'add', 'track', 'mx');   //remove full add mx command structure
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'add', 'track', 'local');   //remove full add local command structure
            $this->callPublicMethod('MLEPP\Admin', 'removeAdminCommand', 'remove', 'track'); // remove full remove command structure
            Console::println('[' . date('H:i:s') . '] [UNLOAD] [AddRemoveMaps] Removed all dependend add/rmove commands from admin.');
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
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
            return;
        }

        $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
        $login = $admin->login;

        if (!is_string($param1)) {
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$i/admin add local takes a filename as a parameter.', $admin);
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
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 added new local track $fff' . $param1);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Added new local track :' . $param1);
                $eventTargetFile = $targetFile;
                Dispatcher::dispatch(new onTrackAdded($login, $eventTargetFile, $isTmx));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$i' . $e->getMessage(), $fromLogin);
            }
        } else {
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iFile $fff' . $param1 . '.' . $mapExtensions[0] . ' $f00$i at location $fff' . $challengeDir . ' $f00$idoesn\'t exist.', $admin);
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
            $this->connection->chatSendServerMessageToLogin('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }

        if (!is_numeric($mxid)) {
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iYou have entered a non-numeric value for mx track. All mx tracks are numerical.', $login);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $loginObj->login . '] Use of non-numeric value for TMX track.');
            return;
        }

        $trackinfo = $this->getData('http://' . self::$mxLocation . '/api/tracks/get_track_info/id/' . $mxid . '?format=json');
        if (is_int($trackinfo)) {
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iAdding track from MX failed with http error $fff' . $trackinfo . '$f00$i.', $login);
            return;
        } else {
            $trackinfo = json_decode($trackinfo);
        }

        if (!is_null($trackinfo)) {
            $trackdata = $this->getDatas('http://' . self::$mxLocation . '/tracks/download/' . $mxid);

            $dataDir = $this->connection->gameDataDirectory();
            $dataDir = str_replace('\\', '/', $dataDir);
            $challengeDir = $dataDir . "Maps/Downloaded/MX/";
            if (!is_dir($challengeDir)) {
                mkdir($challengeDir, 0777, true);
            }

            if (strlen($trackdata) >= 1024 * 1024) {
                $size = round(strlen($trackdata) / 1024);
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$iThe track you\'re trying to download is too large (' . $size . 'Kb > 1024 Kb).', $loginObj);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackfile is too large (' . $size . 'Kb > 1024 Kb).');
                return;
            }

            $targetFile = $challengeDir . $this->filterName($trackinfo->Name) . '-' . $mxid . '.Map.Gbx';
            $eventTargetFile = "Maps/Downloaded/MX/" . $this->filterName($trackinfo->Name) . '-' . $mxid . '.Map.Gbx';

            if (file_put_contents($targetFile, $trackdata) === false) {
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$iCouldn\'t write trackdata. Check directory & file permissions at dedicated tracks folder!', $loginObj);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Trackdata couldn\'t been written. Check directory- and filepermissions!.');
                return;
            }

            $newChallenge = $this->connection->getMapInfo($targetFile);
            foreach ($this->storage->maps as $chal) {
                if ($chal->uId == $newChallenge->uId) {
					$this->connection->chatSendServerMessageToLogin('$fff» $f00$iThe track you tried to add is already in serverlist.', $loginObj);
                    Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Track already in the tracklist.');
                    return;
                }
            }
            try {
                $this->connection->insertMap($targetFile);
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $loginObj->nickName . '$z$s$ff0 added track $fff' . $trackinfo->Name . '$z$s$ff0 from $fffM$5DFX$0ae!');
                //Dispatcher::dispatch(new onTrackAdded($login,$eventTargetFile,$isTmx,$param2));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);

                Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Succesfully added track ' . $trackinfo->Name . '.');
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$i' . $e->getMessage(), $login);
            }
        } else {
            // track unknown
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iThe track you\'re trying to download doesn\'t exist.', $loginObj);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [ManiaExchange] [' . $login . '] Unknown track.');
        }
    }

	function getData($url) {
		$ch = curl_init($url);
		//curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_USERPWD, $this->config->credentials);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLOPT_HTTPAUTH);
		curl_setopt($ch, CURLOPT_USERAGENT, 'MLEPP ManiaExchange');

		$output = curl_exec($ch);
		$info = curl_getinfo($ch);

		curl_close($ch);

		return $output;
	}

    /**
     * removethis()
     * Function removes current track from tracklist.
     *
     * @param mixed $fromLogin
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $param3
     * @return void
     */
    function removethis($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'removeTrack')) {
            $this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $fromLogin);
            return;
        }
        $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
        $login = $admin->login;
        $challenge = $this->connection->getCurrentMapInfo();
        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);
        $file = $challenge->fileName;
        $challengeFile = $dataDir . "Maps/" . $file;

        $this->connection->removeMap($challengeFile);
		$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 removed this track from playlist.');
        Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Removed current track from the tracklist.');
        Dispatcher::dispatch(new onTrackRemoved($login, $challengeFile));
        $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
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
    function remove($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
        if (!$this->mlepp->AdminGroup->hasPermission($fromLogin, 'removeTrack')) {
            $this->mlepp->sendChat($this->mlepp->AdminGroups->noPermissionMsg, $fromLogin);
            return;
        }
        if ($param1 == 'this') {
            $this->removethis($fromLogin);
            return;
        }

        $admin = Storage::GetInstance()->getPlayerObject($fromLogin);
        $login = $admin->login;
        $data = false;

        $param1 = (int) $param1;
        if ($param1 == null || !\is_numeric($param1) || $param1 < 0) {

            $info = Info::Create($login);
            $info->setSize(100, 30);
            $info->setTitle('Wrong use of /admin remove #');
            $text = "You need to use a valid number";
            $info->setText($text);
            $info->centerOnScreen();
            WindowHandler::showDialog($info);
            return false;
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Wrong use of /admin remove (use valid number).');
        }

        if ($this->isPluginLoaded("MLEPP\Jukebox")) {
            $data = $this->callPublicMethod("MLEPP\Jukebox", "getJukeboxTrack", $login, $param1);
            if ($data != false) {
                $file = $data["challenge_file"];
                $name = $data["challenge_name"];
            }
        }

        if ($data == false) {
            $challenges = $this->connection->getMapList(-1, 0);
            $file = "";
            $name = "";
            foreach ($challenges as $key => $data) {
                if (($key + 1) == $param1) {
                    $file = $data->fileName;
                    $name = $data->name;
                    break;
                }
            }
        }

        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);
        $challengeFile = $dataDir . "Maps/" . $file;


        if (!is_file($challengeFile)) {
			$this->connection->chatSendServerMessageToLogin('$fff» $f00$iTarget trackfile not found in filesystem. Check, that you have entered correct track id!', $admin);
            Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Target trackfile not found in filesystem.');
            return;
        }
        $this->connection->removeMap($challengeFile);
		$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 removed track $fff' . $name . '$z$s$ff0 from playlist.');
        Dispatcher::dispatch(new onTrackRemoved($login, $challengeFile));
        $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $fromLogin, NULL, NULL, true);
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

    function RemoveWindow($login, $overrideDir = NULL) {

        if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessageToLogin('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }

        if ($overrideDir == 'this') {
            $this->removethis($login);
            return;
        }
        if (\is_numeric($overrideDir) && (int) $overrideDir > 0) {
            $this->remove($login, $overrideDir);
            return;
        }


        $window = RemoveWindow::Create($login);
        $window->setSize(200, 110);
        $window->clearAll();
        // prepare cols ...
        $window->addColumn('Filename', 0.6);
        $window->addColumn('Action', 0.2);

        // refresh records for this window ...
        $window->clearItems();


        $dataDir = $this->connection->gameDataDirectory();
        $dataDir = str_replace('\\', '/', $dataDir);
        $challengeDir = $dataDir . "Maps/";

        foreach ($this->connection->getMapList(-1, 0) as $challenge) {
            $entry = array
                (
                'Filename' => array($challenge->name, NULL, false),
                'Action' => array("Remove", array(($challengeDir . "/" . $challenge->fileName), $challenge->name), false)
            );
            $window->addAdminItem($entry, array($this, 'onClicks'));
        }

        // display or update window ...
        $window->centerOnScreen();
        $window->show();
    }

    function addLocalWin($login, $overrideDir = false) {
        if (!AdminGroup::contains($login)) {
            $this->connection->chatSendServerMessageToLogin('$fff» $f00$iYou don\'t have the permission to do that!', $login);
            return;
        }
        $window = AddLocalWindow::Create($login);
        $window->setTitle("Add local tracks");
        $window->setSize(200, 110);
        $window->clearAll();
        // prepare cols ...
        $window->addColumn('Filename', 0.6);
        $window->addColumn('Action', 0.2);

        // refresh records for this window ...
        $window->clearItems();


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
         $window->addAdminItem($entry, array($this, 'onClick'));
         }
         
         foreach ($arrayFiles as $entry) {
         $window->addAdminItem($entry, array($this, 'onClick'));
         }
         
        // display or update window ...
        $window->centerOnScreen();
        $window->show();
    }

    function onClicks($login, $action, $target) {
        if ($action == "Remove") {
            try {
                $this->connection->removeMap($target[0]);
                $file = str_replace(".Map.Gbx", "", $target[1]);
                $admin = $this->storage->getPlayerObject($login);
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 removed local track $fff' . $file);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] removed local track :' . $file);
                $eventTargetFile = $target[0];
                Dispatcher::dispatch(new onTrackRemoved($login, $eventTargetFile, false));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$i' . $e->getMessage(), $login);
            }
        }
        if (is_array($target) && $target[0] == "changeDir") {
            $this->RemoveWindow($login, $target[1]);
        }
    }

    function onClick($login, $action, $target) {
        if ($action == "Add") {
            try {
                $this->connection->insertMap($target[0]);
                $file = str_replace(".Map.Gbx", "", $target[1]);
                $admin = $this->storage->getPlayerObject($login);
				$this->connection->chatSendServerMessage('$fff»» $ff0Admin ' . $admin->nickName . '$z$s$ff0 added new local track $fff' . $file);
                Console::println('[' . date('H:i:s') . '] [MLEPP] [AddRemoveMaps] [' . $admin->login . '] Added new local track :' . $file);
                $eventTargetFile = $target[0];
                Dispatcher::dispatch(new onTrackAdded($login, $eventTargetFile, false));
                $this->callPublicMethod('MLEPP\Admin', 'saveMatchSettings', $login, NULL, NULL, true);
            } catch (\Exception $e) {
				$this->connection->chatSendServerMessageToLogin('$fff» $f00$i' . $e->getMessage(), $login);
            }
        }
        if (is_array($target) && $target[0] == "changeDir") {
            $this->addLocalWin($login, array($target[1]));
        }
    }

}

?>