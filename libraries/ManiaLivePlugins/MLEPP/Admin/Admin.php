<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack for ShootMania
 *
 * -- MLEPP Plugin --
 * @name Admin
 * @date 09-09-2012
 * @version 0.4.0
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

namespace ManiaLivePlugins\MLEPP\Admin;

use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\PlayersWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\SelectTracklistWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\AdminWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\RulesInfo;
use ManiaLivePlugins\MLEPP\Admin\Gui\Windows\SimpleWindow;
use ManiaLivePlugins\MLEPP\Admin\Gui\Controls\Button;
use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Features\ChatCommand\Interpreter as ChatCommand;
use ManiaLive\DedicatedApi\Xmlrpc\Exception;
use ManiaLive\Utilities\Console;
use ManiaLive\Gui\Handler;
use ManiaLive\Data\Storage;
use ManiaLive\Gui\Windowing\Window;
use ManiaLive\Event\Dispatcher;
use SimpleXMLElement;

class Plugin extends \ManiaLive\PluginHandler\Plugin {

	private $AdminCommand = array();
	private $descAdmin = "Provides admin commands. For more help see /admin";
	private $descPlayers = "Shows all players on server with given id numbers for usage with other plugins.";
	private $matchsettings;

	/**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
		$this->setVersion('0.4.0');

		$this->setPublicMethod('getVersion');
		$this->setPublicMethod('addAdminCommand');
		$this->setPublicMethod('removeAdminCommand');
		$this->setPublicMethod('saveMatchSettings');
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

		AdminWindow::$adminPlugin = $this;
		PlayersWindow::$adminPlugin = $this;
		$this->config = Config::getInstance();

		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Admin v' . $this->getVersion());
	}

	function onUnLoad() {
		Console::println('[' . date('H:i:s') . '] [UNLOAD] Admin r' . $this->getVersion() . '');
		parent::onUnload();
	}

	/**
	 * onPlayerConnect()
	 * Function called when a player connects.
	 *
	 * @param mixed $login
	 * @param mixed $isSpectator
	 * @return void
	 */
	function onPlayerConnect($login, $isSpec) {
		$player = $this->storage->getPlayerObject($login);
		if (AdminGroup::contains($player->login)) {
			$this->showAdminPanel($player->login);
			return;
		}

		foreach (AdminWindow::GetAll() as $window) {
			$window::Buzz();
			$window->updateData();
		}
	}

	/**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function onPlayerDisconnect($login) {
		
	}

	function onPlayerChat($playerUid, $login, $text, $isRegistredCmd) {
		if ($playerUid == 0)
			return;
		if (in_array($login, AdminGroup::get()) && $text == "/help")
			$this->showHelp($login);
	}

	/**
	 * onReady()
	 * Function called when ManiaLive is ready loading.
	 *
	 * @return void
	 */
	function onReady() {

		$cmd = $this->registerChatCommand("players", "players", 0, true);
		$cmd = $this->registerChatCommand("unmute", "unmute", 0, true);
		$cmd->help = $this->descPlayers;

		$this->addAdminCommand(array($this, 'GetRulesScriptInfo'), array('get', 'rules', 'info'), false, false, false);
		$this->addAdminCommand(array($this, 'setModeSettings'), array('set', 'script', 'settings'), true, true, false);
		$this->addAdminCommand(array($this, 'skip'), array('skip'), false, false, false);
		$this->addAdminCommand(array($this, 'kick'), array('kick'), false, false, false);
		$this->addAdminCommand(array($this, 'restart'), array('restart'), false, false, false);
		$this->addAdminCommand(array($this, 'setServerPassword'), array('set', 'server', 'pass'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerName'), array('set', 'server', 'name'), true, false, false);
		$this->addAdminCommand(array($this, 'setServerComment'), array('set', 'server', 'comment'), true, false, false);
		$this->addAdminCommand(array($this, 'setSpecPassword'), array('set', 'server', 'specpass'), true, false, false);

		$this->addAdminCommand(array($this, 'cancel'), array('cancel'), false, false, false);
		$this->addAdminCommand(array($this, 'ban'), array('ban'), true, false, false);
		$this->addAdminCommand(array($this, 'unban'), array('unban'), true, false, false);
		$this->addAdminCommand(array($this, 'enableCallvotes'), array('enable', 'votes'), false, false, false);
		$this->addAdminCommand(array($this, 'disableCallvotes'), array('disable', 'votes'), false, false, false);
		$this->addAdminCommand(array($this, 'saveMatchSettings'), array('save'), false, false, false);
		$this->addAdminCommand(array($this, 'loadMatchSettings'), array('load'), false, false, false);

		// show adminpanel at manialive restart
		foreach ($this->storage->players as $login => $player) {
			$this->onPlayerConnect($login, true);
		}
		// show adminpanel also to admins who spectate
		foreach ($this->storage->spectators as $login => $player) {
			$this->onPlayerConnect($login, false);
		}
	}

	function showHelp($login) {
		$plugins = "";
		foreach (array_keys($this->AdminCommand) as $command) {
			$plugins .= $command . ", ";
		}
		$plugins = substr($plugins, 0, -2);
		$this->connection->chatSendServerMessage('$fffAdmin commands available: $o$FC4' . $plugins);
	}

	/**
	 * showAdminPanel()
	 * Function shows the admin panel.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function showAdminPanel($login) {

		$panel = \ManiaLivePlugins\MLEPP\Admin\Gui\Windows\AdminPanel::Create($login);


		$panel->clearItems();
		//end round
		$item = new Button("Icons64x64_1", "QuitRace", "endRound");
		$item->addCall(array($this, 'panelCommand'));
		$panel->addItem($item);
		//restart
		$item = new Button("Icons64x64_1", "Refresh", "restart");
		$item->addCall(array($this, 'panelCommand'));
		$panel->addItem($item);
		//skip
		$item = new Button("Icons64x64_1", "ClipPlay", "skip");
		$item->addCall(array($this, 'panelCommand'));
		$panel->addItem($item);
		//empty

		$item = new Button("empty", "empty", "empty");
		$item->addCall(array($this, 'panelCommand'));
		$panel->addItem($item);
		//addtrack
		/*
		  $item = new Button("Icons64x64_1", "Add", "addtrack");
		  $item->addCall(array($this, 'panelCommand'));
		  $panel->addItem($item);
		  //removetrack
		  $item = new Button("Icons64x64_1", "MediaAudioDownloading", "removetrack");
		  $item->addCall(array($this, 'panelCommand'));
		  $panel->addItem($item);
		  //empty
		  $item = new Button("empty", "empty", "empty");
		  $item->addCall(array($this, 'panelCommand'));
		  $panel->addItem($item);
		  //pluginmanager
		  /*$item = new Button("Icons64x64_1", "Browser", "pluginmanager");
		  $item->addCall(array($this, 'panelCommand'));
		  $panel->addItem($item); */
		//list maps
		/* $item = new Button("Icons64x64_1", "ToolRoot", "list");
		  $item->addCall(array($this, 'panelCommand'));
		  $panel->addItem($item); */

		//players
		$item = new Button("Icons64x64_1", "Buddy", "players");
		$item->addCall(array($this, 'panelCommand'));
		$panel->addItem($item);

		$panel->setScale(0.9);
		$panel->show();
	}

	/**
	 * hideAdminPanel()
	 * Function shows the admin panel.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function hideAdminPanel($login) {
		$panel = AdminPanelWindow::Erase($login);
	}

	/**
	 * addAdminCommand()
	 * Helper function, adds admin command.
	 *
	 * @param mixed $callback
	 * @param mixed $commandname
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $help
	 * @param mixed $plugin
	 * @return void
	 */
	function addAdminCommand($callback, $commandname, $param1 = null, $param2 = null, $param3 = null, $help = null, $plugin = null) {
		if (!is_array($commandname))
			$commandname = array($commandname);
		$aCommand = array();
		$aCommand['params'][] = $param1;
		$aCommand['params'][] = $param2;
		$aCommand['params'][] = $param3;
		$aCommand['callback'] = $callback;
		$aCommand['commandNb'] = count($commandname);
		$this->createArrayEntry($this->AdminCommand, $commandname, $aCommand);
		$chatCommand = ChatCommand::getInstance();
		//		if chatcommand is registered already, don't create new.
		if ($chatCommand->isRegistered($commandname[0]))
			return;

		// otherwice register new chatcommand.
		$command = new Command($commandname[0], -1);
		$command->addLoginAsFirstParameter = true;
		$command->log = false;
		// $command->isPublic = true;  // disabled since we got own admin commands help
		$command->help = 'Admin command';

		switch ($commandname[0]) {
			case "set":
				$command->callback = array($this, "set");
				break;
			case "get":
				$command->callback = array($this, "get");
				break;
			case "enable":
				$command->callback = array($this, "enable");
				break;
			case "disable":
				$command->callback = array($this, "disable");
				break;

			default:
				$command->callback = $this->AdminCommand[$commandname[0]]['callback'];
				break;
		}

		$command->authorizedLogin = AdminGroup::get();
		ChatCommand::getInstance()->register($command);
	}

	/**
	 * createArrayEntry()
	 * Helper function, creates array entry.
	 *
	 * @param string $command
	 * @param mixed $e
	 * @param mixed $val
	 * @return void
	 */
	function removeAdminCommand($e, $plugin = NULL) {
		if (!is_array($e)) {
			unset($this->AdminCommand[$e]);
			return;
		}
		$count = count($e);
		switch ($count) {
			case 1:
				unset($this->AdminCommand[$e[0]]);
				break;
			case 2:
				unset($this->AdminCommand[$e[0]][$e[1]]);
				break;
			case 3:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]]);
				break;
			case 4:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]]);
				break;
			case 5:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]]);
				break;
			case 6:
				unset($this->AdminCommand[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]]);
				break;
		}
	}

	/**
	 * createArrayEntry()
	 * Helper function, creates array entry.
	 *
	 * @param mixed $arr
	 * @param mixed $e
	 * @param mixed $val
	 * @return void
	 */
	function createArrayEntry(&$arr, $e, &$val) {
		$count = count($e);

		switch ($count) {
			case 1:
				$arr[$e[0]] = $val;
				break;
			case 2:
				$arr[$e[0]][$e[1]] = $val;
				break;
			case 3:
				$arr[$e[0]][$e[1]][$e[2]] = $val;
				break;
			case 4:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]] = $val;
				break;
			case 5:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]] = $val;
				break;
			case 6:
				$arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]] = $val;
				break;
		}
	}

	/**
	 * checkcArrayKeys()
	 * Helper function, checks array keys.
	 *
	 * @param mixed $arr
	 * @param mixed $e
	 * @return
	 */
	function checkcArrayKeys($arr, $e) {
		switch (count($e) - 1) {
			case 6:
				if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]][$e[5]]))
					return 6;
			case 5:
				if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]]))
					return 5;
			case 4:
				if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]][$e[4]]))
					return 4;
			case 3:
				if (isset($arr[$e[0]][$e[1]][$e[2]][$e[3]]))
					return 3;
			case 2:
				if (isset($arr[$e[0]][$e[1]][$e[2]]))
					return 2;
			case 1:
				if (isset($arr[$e[0]][$e[1]]))
					return 1;
				break;
		}
	}

	/**
	 * array_searchMultiOnKeys()
	 * Helper function, search on multiple keys.
	 *
	 * @param mixed $multiArray
	 * @param mixed $searchKeysArray
	 * @param mixed $innerarray
	 * @return
	 */
	function array_searchMultiOnKeys($multiArray, $searchKeysArray, $innerarray = array()) {


		if (in_array($searchKeysArray[0], array_keys($multiArray))) {
			$result = $multiArray[$searchKeysArray[0]]; // Iterate through searchKeys, making $multiArray smaller and smaller.

			if (is_array($result)) { // if result is an array, continue
				array_shift($searchKeysArray);  //shift the search arraykeys by one

				if (is_array($searchKeysArray)) {   // if there is arraykeys left iterate
					$innerarray = $this->array_searchMultiOnKeys($result, $searchKeysArray, $result);
				} else {  //else return resultset.
					$innerarray = $result;
				}
			}
		}
		return $innerarray;  // return final result.
	}

	function set() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->admin($login, "set", $args);
	}

	function get() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->admin($login, "get", $args);
	}

	function enable() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->admin($login, "enable", $args);
	}

	function disable() {
		$args = func_get_args();
		$login = array_shift($args);
		$this->admin($login, "disable", $args);
	}

	/**
	 * admin()
	 * Provides the /admin commands.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $param4
	 * @param mixed $param5
	 * @return
	 */
	function admin($login, $param = NULL, $param1 = NULL) {

		$adminparams[] = $param;
		if (is_array($param1) && count($param1) > 0) {
			preg_match_all('/(?!\\\\)"((?:\\\\"|[^"])+)"?|([^\s]+)/', $param1[0], $matches);
			$parameters = array_map(
					function($str, $word) {
						return str_replace('\\"', '"', $str != '' ? $str : $word);
					}, $matches[1], $matches[2]);
			$adminparams = array_merge($adminparams, $parameters);
		}
		$adminparams[] = null;

		$tree = $this->array_searchMultiOnKeys($this->AdminCommand, $adminparams);

		if (isset($tree['params'])) {
			$paramscount = 0;
			foreach ($tree['params'] as $para) {
				if ($para === true)
					$paramscount++;
			}

			$validCmdNumber = $this->checkcArrayKeys($this->AdminCommand, $adminparams) + 1;

			switch ($paramscount) {
				case 0:
					call_user_func_array($tree['callback'], array($login));
					break;
				case 1:
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber]));
					break;
				case 2:
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber], $adminparams[$validCmdNumber + 1]));
					break;
				case 3:
					call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber], $adminparams[$validCmdNumber + 1], $adminparams[$validCmdNumber + 2]));
					break;
				case 4:
               call_user_func_array($tree['callback'], array($login, $adminparams[$validCmdNumber], $adminparams[$validCmdNumber + 1], $adminparams[$validCmdNumber + 2], $adminparams[$validCmdNumber + 3]));
			   	break;
			}
		} else {

			if (count($tree) != 0) {
				$adminCommandCount = count($tree);

				$validCmdNumber = $this->checkcArrayKeys($this->AdminCommand, $adminparams);
				print_r($validCmdNumber);


				$x = 0;
				$scope = "";
				$invalid = "";
				foreach ($adminparams as $data) {
					if ($data != null) {
						if ($x <= $validCmdNumber) {
							$scope .= $data . " ";
							$x++;
						} else {
							$invalid .= $data . " ";
						}
					}
				}
				$scope = substr($scope, 0, -1);
				$invalid = substr($invalid, 0, -1);
				$help = '$fffInvalid admin command:$0f0$o/' . $scope . '$f00 ' . $invalid . '$z$s$fff' . "\n";
				$help .= '$fffAvailable next commands in $fc4$o/' . $scope . '$z$s$fff are:' . "\n" . ' $fc4$o';
				foreach (array_keys($tree) as $param) {
					$help .= '$fc4' . $param . '$fff, ';
				}
			} else {
				$help = '$fffPossible next admin commands are: $fc4$o';
				foreach (array_keys($this->AdminCommand) as $param) {
					$help .= '$fc4' . $param . '$fff, ';
				}
			}
			$this->connection->chatSendServerMessage(substr($help, 0, -2), $login);
		}
	}

	/**
	 * adminParameterError()
	 * Function sends out a parameter error.
	 *
	 * @param mixed $login
	 * @param mixed $number
	 * @return void
	 */
	function adminParameterError($login, $number) {
		$this->connection->chatSendServerMessage('$f00$iWrong number of parameters given. The admin command you entered takes $fff' . $number . ' $f00of parameters!');
		Console::println('[' . date('H:i:s') . '] [MLEPP] [AdminPanel] [' . $login . '] Wrong number of parameters given.');
	}

	/**
	 *
	 */
	function setModeSettings($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}
		$RSI = $this->connection->getModeScriptInfo();
		if ($RSI->name == 'Royal.Script.txt') {
			$gamemode = NULL;

			if (strtolower($param1) == "pointlimit")
				$modesettings = 'S_MapPointsLimit';
			if (strtolower($param1) == "offzone activation")
				$modesettings = 'S_OffZoneActivationTime';
			if (strtolower($param1) == "offzone timelimit")
				$modesettings = 'S_OffZoneTimeLimit	';
			if (strtolower($param1) == "spawn")
				$modesettings = 'S_SpawnInterval';
			if ($param1 == "") {
				$this->connection->chatSendServerMessage('Usage: /set script settings pointlimit X or offzone activation X or offzone timelimit X or spawn X ', $fromLogin);
				return;
			}
			if($param2 = trim(strtolower($param2))){
				if ($param2 == 'int') $param2 = (int)$param2;
                if ($param2 == 'float') $param2 = (float)$param2;
                if ($param2 == 'string') $param2 = (string)$param2;
                if ($param2 == 'double') $param2 = (double)$param2;
                if ($param2 == 'boolean') $param2 = (bool)$param2;

                if (strtolower($param2) == 'true') $param2 = true;
                elseif (strtolower($param2) == 'false') $param2 = false;
			} else {
				$this->connection->chatSendServerMessage('Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
				return;
			}
			$setmodesetting = array($modesettings => $param2);
		}

		$RSI = $this->connection->getModeScriptInfo();
		if ($RSI->name == 'Melee.Script.txt') {
			$gamemode = NULL;

			if (strtolower($param1) == "pointlimit")
				$modesettings = 'S_PointLimit';
			if (strtolower($param1) == "timelimit")
				$modesettings = 'S_TimeLimit';
			if ($param1 == "") {
				$this->connection->chatSendServerMessage('Usage: /set script settings pointlimit X or timelimit X  ', $fromLogin);
				return;
			}
			if($param2 = trim(strtolower($param2))){
				if ($param2 == 'int') $param2 = (int)$param2;
                if ($param2 == 'float') $param2 = (float)$param2;
                if ($param2 == 'string') $param2 = (string)$param2;
                if ($param2 == 'double') $param2 = (double)$param2;
                if ($param2 == 'boolean') $param2 = (bool)$param2;

                if (strtolower($param2) == 'true') $param2 = true;
                elseif (strtolower($param2) == 'false') $param2 = false;
			} else {
				$this->connection->chatSendServerMessage('Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
				return;
			}
			$setmodesetting = array($modesettings => $param2);
		}

		$RSI = $this->connection->getModeScriptInfo();
		if ($RSI->name == 'Battle.Script.txt') {
			$gamemode = NULL;

			if (strtolower($param1) == "respawn")
				$modesettings = 'S_RespawnTime';
			if (strtolower($param1) == "rtw")
				$modesettings = 'S_RoundsToWin';
			if (strtolower($param1) == "rgtw")
				$modesettings = 'S_RoundGapToWin';
			if (strtolower($param1) == "roundlimit")
				$modesettings = 'S_RoundsLimit';
			if (strtolower($param1) == "timelimit")
				$modesettings = 'S_TimeLimit';
			if (strtolower($param1) == "capturemv")
				$modesettings = 'S_CaptureMaxValue';
			if ($param1 == "") {
				$this->connection->chatSendServerMessage('Usage: /set script settings respawn X or rtw X or rgtw X or roundlimit X or timelimit X or capturemv X  ', $fromLogin);
				return;
			}
			if($param2 = trim(strtolower($param2))){
				if ($param2 == 'int') $param2 = (int)$param2;
                if ($param2 == 'float') $param2 = (float)$param2;
                if ($param2 == 'string') $param2 = (string)$param2;
                if ($param2 == 'double') $param2 = (double)$param2;
                if ($param2 == 'boolean') $param2 = (bool)$param2;

                if (strtolower($param2) == 'true') $param2 = true;
                elseif (strtolower($param2) == 'false') $param2 = false;
			} else {
				$this->connection->chatSendServerMessage('Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
				return;
			}
			$setmodesetting = array($modesettings => $param2);
		}

		$RSI = $this->connection->getModeScriptInfo();
		if ($RSI->name == 'BattleWaves.Script.txt') {
			$gamemode = NULL;

			if (strtolower($param1) == "respawn")
				$modesettings = 'S_RespawnTime';
			if (strtolower($param1) == "rtw")
				$modesettings = 'S_RoundsToWin';
			if (strtolower($param1) == "rgtw")
				$modesettings = 'S_RoundGapToWin';
			if (strtolower($param1) == "roundlimit")
				$modesettings = 'S_RoundsLimit';
			if (strtolower($param1) == "timelimit")
				$modesettings = 'S_TimeLimit';
			if (strtolower($param1) == "capturemv")
				$modesettings = 'S_CaptureMaxValue';
			if (strtolower($param1) == "TFFC")
				$modesettings = 'S_TimeLimitForFirstCapture';
			if (strtolower($param1) == "TAFC")
				$modesettings = 'S_TimeLimitAfterFirstCapture';
			if (strtolower($param1) == "waveduration")
				$modesettings = 'S_WaveDuration';
			if ($param1 == "") {
				$this->connection->chatSendServerMessage('Usage: /set script settings respawn X or rtw X or rgtw X or roundlimit X or timelimit X or capturemv X or waveduration X or TAFC X or TFFC X  ', $fromLogin);
				return;
			}
			if($param2 = trim(strtolower($param2))){
				if ($param2 == 'int') $param2 = (int)$param2;
                if ($param2 == 'float') $param2 = (float)$param2;
                if ($param2 == 'string') $param2 = (string)$param2;
                if ($param2 == 'double') $param2 = (double)$param2;
                if ($param2 == 'boolean') $param2 = (bool)$param2;

                if (strtolower($param2) == 'true') $param2 = true;
                elseif (strtolower($param2) == 'false') $param2 = false;
			} else {
				$this->connection->chatSendServerMessage('Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
				return;
			}
			$setmodesetting = array($modesettings => $param2);
		}

		$RSI = $this->connection->getModeScriptInfo();
		if ($RSI->name == 'ShootMania\Elite') {
			$gamemode = NULL;
			if (strtolower($param1) == "mode")
				$modesettings = 'S_Mode';
			if (strtolower($param1) == "timelimit")
				$modesettings = 'S_TimeLimit';
			if (strtolower($param1) == "timegoal")
				$modesettings = 'S_TimePole';
			if (strtolower($param1) == "timecapture")  // Don't work
				$modesettings = 'S_TimeCapture';
			if (strtolower($param1) == "warmup")
				$modesettings = 'S_WarmUpDuration';	
			if (strtolower($param1) == "winmap")
				$modesettings = 'S_MapWin';	
			if (strtolower($param1) == "submatchwin")
				$modesettings = 'S_SubmatchWin';
			if (strtolower($param1) == "winturnlimit")
				$modesettings = 'S_TurnLimit';
			if (strtolower($param1) == "deciderturnlimit")
				$modesettings = 'S_DeciderTurnLimit';
			if (strtolower($param1) == "turnwin")
				$modesettings = 'S_TurnWin';
			// S_LaserVsRocket
			if (strtolower($param1) == "draft")		// Don't work
				$modesettings = 'S_UseDraft';
			if (strtolower($param1) == "totalmaps")
				$modesettings = 'S_MapTotal';
			if ($param1 == NULL) {
				$this->connection->chatSendServerMessage('Usage: /set script settings timelimit X or or mode X or winmatch X or warmup 0 or timecapture  X or timecapture X or winround X or winroundgap X or winroundlimit X or winmap X or WarmUp X  ', $fromLogin);
				return;
			}
			if($param2 = trim(strtolower($param2))){
				if ($param2 == 'int') $param2 = (int)$param2;
                if ($param2 == 'float') $param2 = (float)$param2;
                if ($param2 == 'string') $param2 = (string)$param2;
                if ($param2 == 'double') $param2 = (double)$param2;
                if ($param2 == 'boolean') $param2 = (bool)$param2;

                if (strtolower($param2) == 'true') $param2 = true;
                elseif (strtolower($param2) == 'false') $param2 = false;
			} else {
				$this->connection->chatSendServerMessage('Invalid parameter. Correct parameter for the command is a numeric value.', $fromLogin);
				return;
			}
			$setmodesetting = array($modesettings => $param2);
		}

		try {
			$this->connection->setModeScriptSettings($setmodesetting);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . ' $z$s$ff0 sets script settings to $fff ' . ucfirst($param1) . '$z$s$ff0 and $fff' . ucfirst($param2));
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * GetModeScriptInfo()
	 */
	function GetRulesScriptInfo($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}
		try {
			$RSI = $this->connection->getModeScriptInfo();
			if ($RSI->name == 'ShootMania\Elite') {
				$RinfoWindow = RulesInfo::Create($fromLogin);
				$RinfoWindow->setTitle("Mode Script Info: Elite");
				$RinfoWindow->setText('Scriptname: ' . $RSI->name);
				$RinfoWindow->setText1('MapTypes: ' . $RSI->compatibleMapTypes);
				$RinfoWindow->setText2('Version: ' . $RSI->version);
				$RinfoWindow->setText3('Settings: ' . $RSI->paramDescs[0]->name);
				$RinfoWindow->setText4('Default: ' . $RSI->paramDescs[0]->default);
				$RinfoWindow->setText5('Settings: ' . $RSI->paramDescs[1]->name);
				$RinfoWindow->setText6('Default: ' . $RSI->paramDescs[1]->default);
				$RinfoWindow->setText7('Settings: ' . $RSI->paramDescs[2]->name);
				$RinfoWindow->setText8('Default: ' . $RSI->paramDescs[2]->default);
				$RinfoWindow->setText9('Settings: ' . $RSI->paramDescs[3]->name);
				$RinfoWindow->setText10('Default: ' . $RSI->paramDescs[3]->default);
				$RinfoWindow->setText11('Settings: ' . $RSI->paramDescs[4]->name);
				$RinfoWindow->setText12('Default: ' . $RSI->paramDescs[4]->default);
				$RinfoWindow->setText13('Settings: ' . $RSI->paramDescs[5]->name);
				$RinfoWindow->setText14('Default: ' . $RSI->paramDescs[5]->default);
				$RinfoWindow->setText15('Settings: ' . $RSI->paramDescs[6]->name);
				$RinfoWindow->setText16('Default: ' . $RSI->paramDescs[6]->default);
				$RinfoWindow->setText17('Settings: ' . $RSI->paramDescs[7]->name);
				$RinfoWindow->setText18('Default: ' . $RSI->paramDescs[7]->default);
				$RinfoWindow->setText19('Settings: ' . $RSI->paramDescs[8]->name);
				$RinfoWindow->setText20('Default: ' . $RSI->paramDescs[8]->default);
				$RinfoWindow->setText21('Settings: ' . $RSI->paramDescs[9]->name);
				$RinfoWindow->setText22('Default: ' . $RSI->paramDescs[9]->default);

				$RinfoWindow->setSize(165, 165);
				$RinfoWindow->centerOnScreen();
				$RinfoWindow->show();
			}
			if ($RSI->name == 'Royal.Script.txt') {
				$RinfoWindow = RulesInfo::Create($fromLogin);
				$RinfoWindow->setTitle("Mode Script Info: Royal");
				$RinfoWindow->setText('Scriptname: ' . $RSI->name);
				$RinfoWindow->setText1('MapTypes: ' . $RSI->compatibleMapTypes);
				$RinfoWindow->setText2('Version: ' . $RSI->version);
				$RinfoWindow->setText3('Settings: ' . $RSI->paramDescs[0]->name);
				$RinfoWindow->setText4('Default: ' . $RSI->paramDescs[0]->default);
				$RinfoWindow->setText5('Settings: ' . $RSI->paramDescs[1]->name);
				$RinfoWindow->setText6('Default: ' . $RSI->paramDescs[1]->default);
				$RinfoWindow->setText7('Settings: ' . $RSI->paramDescs[2]->name);
				$RinfoWindow->setText8('Default: ' . $RSI->paramDescs[2]->default);
				$RinfoWindow->setText9('Settings: ' . $RSI->paramDescs[3]->name);
				$RinfoWindow->setText10('Default: ' . $RSI->paramDescs[3]->default);

				$RinfoWindow->setSize(100, 100);
				$RinfoWindow->centerOnScreen();
				$RinfoWindow->show();
			}
			if ($RSI->name == 'Battle.Script.txt') {
				$RinfoWindow = RulesInfo::Create($fromLogin);
				$RinfoWindow->setTitle("Mode Script Info: Battle");
				$RinfoWindow->setText('Scriptname: ' . $RSI->name);
				$RinfoWindow->setText1('MapTypes: ' . $RSI->compatibleMapTypes);
				$RinfoWindow->setText2('Version: ' . $RSI->version);
				$RinfoWindow->setText3('Settings: ' . $RSI->paramDescs[0]->name);
				$RinfoWindow->setText4('Default: ' . $RSI->paramDescs[0]->default);
				$RinfoWindow->setText5('Settings: ' . $RSI->paramDescs[1]->name);
				$RinfoWindow->setText6('Default: ' . $RSI->paramDescs[1]->default);
				$RinfoWindow->setText7('Settings: ' . $RSI->paramDescs[2]->name);
				$RinfoWindow->setText8('Default: ' . $RSI->paramDescs[2]->default);
				$RinfoWindow->setText9('Settings: ' . $RSI->paramDescs[3]->name);
				$RinfoWindow->setText10('Default: ' . $RSI->paramDescs[3]->default);
				$RinfoWindow->setText11('Settings: ' . $RSI->paramDescs[4]->name);
				$RinfoWindow->setText12('Default: ' . $RSI->paramDescs[4]->default);
				$RinfoWindow->setText13('Settings: ' . $RSI->paramDescs[5]->name);
				$RinfoWindow->setText14('Default: ' . $RSI->paramDescs[5]->default);
				$RinfoWindow->setText15('Settings: ' . $RSI->paramDescs[6]->name);
				$RinfoWindow->setText16('Default: ' . $RSI->paramDescs[6]->default);

				$RinfoWindow->setSize(100, 100);
				$RinfoWindow->centerOnScreen();
				$RinfoWindow->show();
			}

			if ($RSI->name == 'BattleWaves.Script.txt') {
				$RinfoWindow = RulesInfo::Create($fromLogin);
				$RinfoWindow->setTitle("Mode Script Info: BattleWaves");
				$RinfoWindow->setText('Scriptname: ' . $RSI->name);
				$RinfoWindow->setText1('MapTypes: ' . $RSI->compatibleMapTypes);
				$RinfoWindow->setText2('Version: ' . $RSI->version);
				$RinfoWindow->setText3('Settings: ' . $RSI->paramDescs[0]->name);
				$RinfoWindow->setText4('Default: ' . $RSI->paramDescs[0]->default);
				$RinfoWindow->setText5('Settings: ' . $RSI->paramDescs[1]->name);
				$RinfoWindow->setText6('Default: ' . $RSI->paramDescs[1]->default);
				$RinfoWindow->setText7('Settings: ' . $RSI->paramDescs[2]->name);
				$RinfoWindow->setText8('Default: ' . $RSI->paramDescs[2]->default);
				$RinfoWindow->setText9('Settings: ' . $RSI->paramDescs[3]->name);
				$RinfoWindow->setText10('Default: ' . $RSI->paramDescs[3]->default);
				$RinfoWindow->setText11('Settings: ' . $RSI->paramDescs[4]->name);
				$RinfoWindow->setText12('Default: ' . $RSI->paramDescs[4]->default);
				$RinfoWindow->setText13('Settings: ' . $RSI->paramDescs[5]->name);
				$RinfoWindow->setText14('Default: ' . $RSI->paramDescs[5]->default);
				$RinfoWindow->setText15('Settings: ' . $RSI->paramDescs[6]->name);
				$RinfoWindow->setText16('Default: ' . $RSI->paramDescs[6]->default);
				$RinfoWindow->setText17('Settings: ' . $RSI->paramDescs[7]->name);
				$RinfoWindow->setText18('Default: ' . $RSI->paramDescs[7]->default);
				$RinfoWindow->setText19('Settings: ' . $RSI->paramDescs[8]->name);
				$RinfoWindow->setText20('Default: ' . $RSI->paramDescs[8]->default);
				$RinfoWindow->setText21('Settings: ' . $RSI->paramDescs[9]->name);
				$RinfoWindow->setText22('Default: ' . $RSI->paramDescs[9]->default);

				$RinfoWindow->setSize(165, 165);
				$RinfoWindow->centerOnScreen();
				$RinfoWindow->show();
			}

			if ($RSI->name == 'Melee.Script.txt') {
				$RinfoWindow = RulesInfo::Create($fromLogin);
				$RinfoWindow->setTitle("Mode Script Info: Melee");
				$RinfoWindow->setText('Scriptname: ' . $RSI->name);
				$RinfoWindow->setText1('MapTypes: ' . $RSI->compatibleMapTypes);
				$RinfoWindow->setText2('Version: ' . $RSI->version);
				$RinfoWindow->setText3('Settings: ' . $RSI->paramDescs[0]->name);
				$RinfoWindow->setText4('Default: ' . $RSI->paramDescs[0]->default);
				$RinfoWindow->setText5('Settings: ' . $RSI->paramDescs[1]->name);
				$RinfoWindow->setText6('Default: ' . $RSI->paramDescs[1]->default);
				;

				$RinfoWindow->setSize(105, 105);
				$RinfoWindow->centerOnScreen();
				$RinfoWindow->show();
			}
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * loadMatchSettings()
	 * Admin function, loads MatchSettings.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function loadMatchSettings($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$matchsettings = $dataDir . "Maps/MatchSettings/";

		if ($param1 != NULL)
			$tracklist = $param1;
		try {
			//if ($this->checkMatchSettingsFile($tracklist)) {
			$this->connection->loadMatchSettings($matchsettings . $tracklist);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $player->nickName . '$z$s$ff0 loaded maplist: $fff' . $tracklist . '$ff0!');
			$this->matchsettings = $tracklist;
			/* } else {
			  $this->mlepp->sendChat('%adminerror%Tracklist named %variable%' . $tracklist . '%adminerror% does not exist!', $fromLogin);
			  } */
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * setServerName()
	 * Admin function, sets servername.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function setServerName($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}
		if (empty($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$i /set server name takes a servername as a parameter, none entered.', $fromLogin);
			return;
		}

		try {
			$this->connection->setServerName($param1);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 sets new server name: $fff' . $param1);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * setSpecPassword()
	 * Admin function, sets spectator password.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setSpecPassword($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerPasswordForSpectator($param1);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 sets/unsets new spec password to $fff' . $param1, $fromLogin);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * setServerComment()
	 * Admin function, sets server comment.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setServerComment($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerComment($param1);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 sets new server comment: $fff' . $param1);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * setServerPassword()
	 * Admin function, sets server password.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function setServerPassword($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}

		if (empty($param1)) {
			$param1 = "";
		}

		try {
			$this->connection->setServerPassword($param1);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 sets/unsets new server password to $fff' . $param1, $fromLogin);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * saveMatchSettings()
	 * Admin function, saves MatchSettings.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @param mixed $fromPlugin
	 * @return void
	 */
	function saveMatchSettings($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL, $fromPlugin = false) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $player);
			return;
		}

		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);

		$matchsettings = $dataDir . "Maps/MatchSettings/";

		$tracklist = $this->config->matchsettings;
		/* if (empty($tracklist)) {
		  $this->selectTracklist($fromLogin);
		  return;
		  } */
		if ($param1 != NULL && $fromPlugin === false)
			$tracklist = $param1;
		try {
			$this->connection->saveMatchSettings($matchsettings . $tracklist);
			$this->connection->chatSendServerMessage('$fff» $ff0Maplist $fff' . $tracklist . '$ff0 saved successfully!', $fromLogin);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	function selectTracklist($login) {
		$window = SelectTracklistWindow::Create($login);
		$window->setSize(200, 110);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Filename', 0.6);
		$window->addColumn('Action', 0.2);

		// refresh records for this window ...
		$window->clearItems();


		$dataDir = $this->connection->gameDataDirectory();
		$dataDir = str_replace('\\', '/', $dataDir);
		$challengeDir = $dataDir . "Maps/MatchSettings";


		$localFiles = scandir($challengeDir);

		foreach ($localFiles as $file) {
			if ($file == ".")
				continue;
			if ($file == "..")
				continue;

			//if (!stristr($file, ".txt"))
			//		continue;

			$entry = array
				(
				'Filename' => array(utf8_encode($file), NULL, false),
				'Action' => array("Select", array(($challengeDir . "/" . $file), $file), false)
			);

			$window->addAdminItem($entry, array($this, 'onFileClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	/**
	 * forceEndRound()
	 * Admin function, forces end of the round.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function forceEndRound($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		try {
			$this->connection->forceEndRound();
			$admin = $this->storage->getPlayerObject($fromLogin);					
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 forces the end of this round.');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * kick()
	 * Admin function, kicks player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function kick($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		
		try {
			$this->connection->kick($param1);
			$player = $this->storage->getPlayerObject($param1);
			$admin = $this->storage->getPlayerObject($fromLogin);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 kicks the player $fff' . $player->nickName);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * cancel()
	 * Admin function, cancels vote.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function cancel($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->cancelVote();
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 canceled the current CallVote!');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	function disableCallvotes($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->setCallVoteTimeOut(0);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 disabled CallVotes!');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	function enableCallvotes($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->setCallVoteTimeOut(60);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 enabled CallVotes!');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * blacklist()
	 * Admin function, blacklists player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function blacklist($fromLogin, $param1 = NULL, $param2 = "", $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iPlayer $fff' . $param1 . '$f00 doesn\' exist.', $fromLogin);
			return;
		}

		$player = $this->storage->getPlayerObject($param1);
		if (is_object($player)) {
			$nickname = $player->nickName;
		} else {
			$nickname = $param1;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			if ($this->playerExists($param1)) {
				$this->connection->banAndBlackList($player, $param2, true);
			} else {
				$this->manualAddBlacklist($param1);
			}
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 blacklists the player $fff' . $nickname);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * ban()
	 * Admin function, bans player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function ban($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iPlayer $fff' . $param1 . '$f00 doesn\' exist.', $fromLogin);
			return;
		}

		$player = $this->storage->getPlayerObject($param1);
		if (is_object($player)) {
			$nickname = $player->nickName;
		} else {
			$nickname = $param1;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->ban($player);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 bans the player $fff' . $nickname);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * unban()
	 * Admin function, unbans player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unban($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$i/unban takes a login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = $this->storage->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unBan($player);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 unbans the player ' . $player->login);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * unBlacklist()
	 * Admin function, unblacklists player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unBlacklist($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage('$fff» $f00$iYou don\'t have the permission to do that!', $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->connection->chatSendServerMessage('$fff» $f00$i/unblack takes a s login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = $this->storage->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unBlackList($player);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 unblacklists the player ' . $player->login);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * ignore()
	 * Admin function, ignores (mute) player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function ignore($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->connection->chatSendServerMessage('Player $fff' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = $this->storage->getPlayerObject($param1);
		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->ignore($player);
			$plNick = $player->nickName;
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 Ignores the player $fff' . $player->nickName);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * toggleMute()
	 * Admin function, toggles mute.
	 *
	 * @param mixed $login
	 * @param mixed $target
	 * @return
	 */
	function toggleMute($login, $target) {

		$ignorelist = $this->connection->getIgnoreList(-1, 0);
		try {
			foreach ($ignorelist as $player) {
				if ($player->login == $target) {
					$this->unignore($login, $target);
					return;
				}
			}
			// else ignore him.
			$this->ignore($login, $target);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $login);
		}
	}

	function isLoginMuted($login) {

		$ignorelist = $this->connection->getIgnoreList(-1, 0);
		//if ignorelist is empty, then automaticly ignore the player.

		if (count($ignorelist) > 1) {
			return false;
		}

		// if player found at ignorelist, unignore
		foreach ($ignorelist as $player) {
			if ($player->login == $login) {
				return true;
			}
		}
		return false;
	}

	/**
	 * unignore()
	 * Admin function, unignores (unmute) player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function unignore($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		if (!AdminGroup::contains($fromLogin)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		if (empty($param1)) {
			$this->connection->chatSendServerMessage('/unignore takes a login as a parameter, none entered.', $fromLogin);
			return;
		}
		$admin = $this->storage->getPlayerObject($fromLogin);
		$player = new \ManiaLive\DedicatedApi\Structures\Player();
		$player->login = $param1;
		try {
			$this->connection->unIgnore($player);
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 unIgnores the player ' . $player->login);
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * ignorelist()
	 * Admin function, shows ignorelist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showIgnorelist($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		$ignorelist = $this->connection->getIgnoreList(1000, 0);

		if (count($ignorelist) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The ignorelist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$id = 1;
		$window = AdminWindow::Create($fromLogin);
		$window->setSize(124, 61);
//        $window->clearAll();
//        // prepare cols ...
//        $window->addColumn('Id', 0.1);
//        $window->addColumn('Login', 0.8);
//        $window->addColumn('unIgnore', 0.1);
//
//
//        // refresh records for this window ...
//        $window->clearItems();
//        $id = 1;
//        foreach ($ignorelist as $player) {
//            $entry = array
//                (
//                'Id' => array($id, NULL, false),
//                'Login' => array($player->login, NULL, false),
//                'unIgnore' => array("unIgnore", $player->login, true),
//            );
//            $id++;
//            $window->addAdminItem($entry, array($this, 'onClick'));
//        }
//
//        // display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	/**
	 * banlist()
	 * Admin function, shows banlist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showBanlist($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		$banList = $this->connection->getBanList(1000, 0);

		if (count($banList) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The banlist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$id = 1;
		$window = AdminWindow::Create($fromLogin);
		$window->setSize(180, 100);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Login', 0.8);
		$window->addColumn('unBan', 0.1);


		// refresh records for this window ...
		$window->clearItems();
		$id = 1;
		foreach ($banList as $player) {
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'Login' => array($player->login, NULL, false),
				'unBan' => array("unBan", $player->login, true),
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	/**
	 * forceSpec()
	 * Admin function, forces player into spectator mode.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function forceSpec($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->connection->chatSendServerMessage('Player $fff' . $param1 . '$0ae doesn\' exist.', $fromLogin);
			return;
		}

		$player = $this->storage->getPlayerObject($param1);
		$admin = $this->storage->getPlayerObject($fromLogin);
		$this->connection->forceSpectator($player, 1);
		$this->connection->forceSpectator($player, 0);
		$plNick = $player->nickName;
		$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 Forces the player $fff' . $player->nickName . '$z$s$ff0 to Spectator.');
	}

	/**
	 * warnPlayer()
	 * Admin function, warns player.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function warnPlayer($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		if (!$this->playerExists($param1)) {
			$this->connection->chatSendServerMessage('Player $fff' . $param1 . '$0ae doesn\'t exist.', $fromLogin);  //fix for notepad++ '
			return;
		}

		$player = $this->storage->getPlayerObject($param1);
		$admin = $this->storage->getPlayerObject($fromLogin);
		$plNick = $player->nickName;
		$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 Warned the player $fff' . $player->nickName);
		$window = SimpleWindow::Create($param1);
		$window->setTitle("Warning!");
		$window->setText("\n\nAn admin has \$f00warned you for your \$o\$soffensive behaviour.\$z \n\n Continuing this behaviour will get you either: \n * Removed from the chat \n * Kicked from the server \n * You might even get banned \n\nPlease take this in consideration while playing here.");
		$window->setSize(160, 90);
		$window->centerOnScreen();
		$window->setBackgroundStyle("BgCard2");
		$window->show();
	}

	/**
	 * skipTrack()
	 * Admin function, skips the current track.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function skip($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->nextMap();
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 skipped the map');
		} catch (Exception $e) {
			//Console::println("Error:\n".$e->getMessage());
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
			//$this->connection->chatSendServerMessage('$fff» $f00$iChange in progress. Please be patient.');
		}
	}

	/**
	 * restartTrack()
	 * Admin function, restarts the current track.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return void
	 */
	function restart($fromLogin, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		$admin = $this->storage->getPlayerObject($fromLogin);
		try {
			$this->connection->restartMap();
			$this->connection->chatSendServerMessage('$fff»» $ff0Admin $fff' . $admin->nickName . '$z$s$ff0 restarted the map');
		} catch (Exception $e) {
			$this->connection->chatSendServerMessage('$fff» $f00$i' . $e->getMessage(), $fromLogin);
		}
	}

	/**
	 * getNick()
	 * Helper function, gets nickname of playerlogin.
	 *
	 * @param mixed $login
	 * @return
	 */
	function getNick($login) {
		return $this->storage->getPlayerObject($login)->nickName;
	}

	/**
	 * playerExists()
	 * Function used for checking if the player exists.
	 *
	 * @param mixed $login
	 * @return
	 */
	function playerExists($login) {
		if (array_key_exists($login, $this->storage->players)) {
			return true;
		} else {
			if (array_key_exists($login, $this->storage->spectators)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * showBlacklist()
	 * Admin function, shows blacklist.
	 *
	 * @param mixed $fromLogin
	 * @param mixed $param1
	 * @param mixed $param2
	 * @param mixed $param3
	 * @return
	 */
	function showBlacklist($fromLogin = NULL, $param1 = NULL, $param2 = NULL, $param3 = NULL) {
		$player = $this->storage->getPlayerObject($fromLogin);
		$message = 'No Admin permissions';
		if (!AdminGroup::contains($player->login)) {
			$this->connection->chatSendServerMessage($message, $fromLogin);
			return;
		}

		$blacklist = $this->connection->getBlackList(-1, 0);
		if (count($blacklist) == 0) {
			$infoWindow = SimpleWindow::Create($fromLogin);
			$infoWindow->setTitle("Notice");
			$infoWindow->setText("The blacklist of the server is empty.");
			$infoWindow->setSize(100, 100);
			$infoWindow->centerOnScreen();
			$infoWindow->show();
			return;
		}

		$window = AdminWindow::Create($fromLogin);
		$window->setSize(125, 61);
		$window->clearAll();
		// prepare cols ...
		$window->addColumn('Id', 0.1);
		$window->addColumn('Login', 0.8);
		$window->addColumn('unBlack', 0.1);


		// refresh records for this window ...
		$window->clearItems();
		$id = 1;

		foreach ($blacklist as $player) {
			$entry = array
				(
				'Id' => array($id, NULL, false),
				'Login' => array($player->login, NULL, false),
				'unBlack' => array("unBlack", $player->login, true),
			);
			$id++;
			$window->addAdminItem($entry, array($this, 'onClick'));
		}

		// display or update window ...
		$window->centerOnScreen();
		$window->show();
	}

	/**
	 * players()
	 * Public function, shows playerlist.
	 *
	 * @param mixed $login
	 * @return
	 */
	function players($login) {
		$player = $this->storage->getPlayerObject($login);
		if (AdminGroup::contains($player->login)) {
			$this->adminPlayers($player->login);
			return;
		} else {
			$player = $this->storage->getPlayerObject($login);
			$window = PlayersWindow::Create($player->login);
			$window->setSize(180, 120);
			$window->centerOnScreen();
			$window->show();
			return;
		}
	}

	/**
	 * adminPlayers()
	 * Admin function, shows playerlist.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function adminPlayers($login) {

		$window = AdminWindow::Create($login);
		$window->setSize(220, 120);
		$window->centerOnScreen();
		$window->show();
	}

	/**
	 * onClick()
	 * Helper function, called on clicking.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @param mixed $target
	 * @return void
	 */
	function onClick($login, $action, $target) {
		//$this->connection->chatSendServerMessage("$login --> $action --> $target", $this->storage->getPlayerObject($login));

		switch ($action) {
			case 'Force':
				$this->forceSpec($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Spec':
				$this->connection->forceSpectator($login, 1);
				$this->connection->forceSpectator($login, 0);
				$this->connection->forceSpectatorTarget($login, $target, 1);
				break;
			case 'Warn':
				$this->warnPlayer($login, $target);
				break;
			case 'Mute':
				$this->toggleMute($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unMute':
				$this->toggleMute($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Kick':
				$this->kick($login, $target);
				$this->adminPlayers($login);
				break;
			case 'Ban':
				$this->ban($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unBan':
				$this->unban($login, $target);
				$this->showBanlist($login);
				break;
			case 'Black':
				$this->blacklist($login, $target);
				$this->adminPlayers($login);
				break;
			case 'unBlack':
				$this->unBlacklist($login, $target);
				$this->showBlacklist($login);
				break;
			case 'unIgnore':
				$this->unignore($login, $target);
				$this->showIgnorelist($login);
				break;
		}

		// $this->players($login);
	}

	/**
	 * panelCommand()
	 * Helper function, gets commands from panel.
	 *
	 * @param mixed $login
	 * @param mixed $action
	 * @return void
	 */
	function panelCommand($login, $action) {
		// $this->connection->chatSendServerMessage('$fff'.$action, $login);
		switch ($action) {
			case 'skip':
				$this->skip($login);
				break;
			case 'restart':
				$this->restart($login);
				break;
			case 'queueRestart':
				$this->callPublicMethod('MLEPP\Jukebox', 'adminQueueRestart', $login);
				break;
			case 'players':
				$this->players($login);
				break;
				/* case 'voteDeny':
				  $this->cancelVote($login);
				 */ break;
				/* case 'votePass':
				  $this->passVote($login);
				 */ break;
			case 'endRound':
				$this->forceEndRound($login);
				break;
			case 'list':
				$this->callPublicMethod('MLEPP\Jukebox', 'trackList', $login, null, null);
				break;
			case 'pluginmanager':
				$this->callPublicMethod('Standard\PluginManager', 'openWindow', $login);
				break;
			case 'addtrack':
				$this->callPublicMethod('MLEPP\AddRemoveMaps', 'addLocalWin', $login, false);
				break;
			case 'removetrack':
				$this->callPublicMethod('MLEPP\AddRemoveMaps', 'RemoveWindow', $login, false);
				break;
		}
	}

}

?>