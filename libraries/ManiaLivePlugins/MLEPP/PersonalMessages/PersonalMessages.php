<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name Personal Messages
 * @date 09-09-2012
 * @version 0.4.0
 * @website mlepp.trackmania.nl
 * @package MLEPP
 *
 * @author The Mlepp Team
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

namespace ManiaLivePlugins\MLEPP\PersonalMessages;

use ManiaLive\DedicatedApi\Connection;
use ManiaLive\Features\ChatCommand\Command;
use ManiaLive\Utilities\Console;
use ManiaLive\Data\Storage;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLivePlugins\MLEPP\Core\Core;
use ManiaLivePlugins\MLEPP\Core\Mlepp;

class PersonalMessages extends \ManiaLive\PluginHandler\Plugin {

	private $pm;
	private $descAdmin = "Usage: /a your message goes here";
	private $descReply = "Usage: /r your message goes here";
	private $descPm = "Usage: /pm login your message goes here";
	private $mlepp = null;

	/**
	 * onInit()
	 * Function called on initialisation of ManiaLive.
	 *
	 * @return void
	 */
	function onInit() {
// this needs to be set in the init section
		$this->setVersion('0.4.0');
		$this->setPublicMethod('getVersion');
	}

	/**
	 * onLoad()
	 * Function called on loading of ManiaLive.
	 *
	 * @return void
	 */
	function onLoad() {
		Console::println('[' . date('H:i:s') . '] [MLEPP] Plugin: Communication r' . $this->getVersion());
		$this->enableDedicatedEvents();

		$command = $this->registerChatCommand('pm', 'sendPersonalMessage', -1, true);
		$command->help = $this->descPm;

		$command = $this->registerChatCommand('r', 'sendReply', -1, true);
		$command->help = $this->descReply;

		$command = $this->registerChatCommand('a', 'adminChat', -1, true, AdminGroup::get());
		$command->help = $this->descAdmin;
	}

	/**
	 * onUnload()
	 * Function called on unloading the plugin.
	 * 
	 * @return void
	 */
	function onUnload() {
		parent::onUnload();
	}

	/**
	 * onPlayerDisconnect()
	 * Function called when a player disconnects.
	 *
	 * @param mixed $login
	 * @return void
	 */
	function onPlayerDisconnect($login) {
		if (isset($this->pm[$login])) {
			unset($this->pm[$login]);
		}
	}

	/**
	 * adminChat()
	 * Function providing the Admin Chat (/a).
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */
	function adminChat($login, $param = NULL) {
		
		$player = $this->storage->getPlayerObject($login);
		$fromNick = $player->nickName;

		if ($param == NULL || $param == "help") {
			$this->showHelp($login, $this->helpAdmin);
			return;
		}

		$admins = array();
// get admins to $admins array.
		foreach ($this->storage->players as $player) {
			$login = $player->login;
			if (AdminGroup::contains($login))
				$admins[] = $player;
		}

		foreach ($this->storage->spectators as $player) {
			$login = $player->login;
			if (AdminGroup::contains($login))
				$admins[] = $player;
		}

//send chat to adminchannel.
		try {
			$message = '$d00' . $fromNick . '$z$s$fff$w » $z$s$f22' . $param;
			$this->connection->chatSendServerMessage($message, $admins);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Sent message to the Admin Chat: ' . $param . '.');
		} catch (\Exception $e) {
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [Error]' . $login . 'admin chat message: ' . $param . '.');
		}
	}

	/*
	  $d4fPersonal Messages $fff$w»$z$s$fff testtest
	  $d00Admin Chat $fff$w»$z$s$f22 testtest
	  $48cGroup Chat $fff$w»$z$s$8cf testtest
	 */

	/**
	 * sendPersonalMessage()
	 * Function used for sending personal messages.
	 *
	 * @param mixed $login
	 * @param mixed $targetLogin
	 * @param mixed $param
	 * @return
	 */
	function sendPersonalMessage($login, $args = NULL) {
	

		$param = explode(" ", $args);
		$targetLogin = array_shift($param);
		$param = implode(" ", $param);
		
		$player = $this->storage->getPlayerObject($login);


		if ($login == $targetLogin) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Sorry, you can\'t send a message to yourself.', $player);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Tried to send him-/herself a message.');
			return;
		}

		if ($targetLogin == NULL) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Couldn\'t send message. The ID you have could not been mapped to any player on the server.', $player);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Tried to send a message to unknown user.');
			return;
		}

		if (!$this->playerExists($targetLogin)) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Couldn\'t send message to login $fff' . $targetLogin . '$fff, player not found on server.', $player);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Couldn\'t send message to login ' . $targetLogin . ', player not found on server.');
			return;
		}
		$targetPlayer = $this->storage->getPlayerObject($targetLogin);



//set reply address
		try {
			$this->connection->chatSendServerMessage('$d4f Message to ' . $targetPlayer->nickName . '$z$s $d4f($fff' . $targetLogin . '$d4f) $fff$w» $z$s$fff' . $param, $login);
			$this->connection->chatSendServerMessage('$d4f Message from ' . $player->nickName . '$z$s $d4f($fff' . $login . '$d4f) $fff$w» $z$s$fff' . $param, $targetLogin);
			$this->pm[$login] = $targetPlayer;
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Sent message to ' . $targetLogin . ': ' . $param . '');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Error: ' . $e->getMessage(), $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Error: ' . $e->getMessage());
		}
	}

	/**
	 * sendReply()
	 * Function providing the /r command.
	 *
	 * @param mixed $login
	 * @param mixed $param
	 * @return
	 */
	function sendReply($login, $param = NULL) {
	

		$player = $this->storage->getPlayerObject($login);
	
		
		if (!isset($this->pm[$login])) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Can\'t reply - no one to reply to.', $player);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Can\'t reply - no one to reply to.');
			return;
		}
		$targetPlayer = $this->pm[$login];
		
		if (!$this->playerExists($targetPlayer->login)) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Couldn\'t send message to login $fff' . $targetLogin . '$fff, player not found on server.', $player);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Couldn\'t send message to login ' . $targetLogin . ', player not found on server.');
			return;
		}

		try {
			$targetPlayer = $this->pm[$login];
			$targetLogin = $this->pm[$login]->login;
			$this->connection->chatSendServerMessage('$d4f Message to ' . $targetPlayer->nickName . '$z$s $d4f($fff' . $targetLogin . '$d4f) $fff$w» $z$s$fff' . $param, $login);
			$this->connection->chatSendServerMessage('$d4f Message from ' . $player->nickName . '$z$s $d4f($fff' . $login . '$d4f) $fff$w» $z$s$fff' . $param, $targetLogin);
			
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Sent message to ' . $targetLogin . ': ' . $param . '');
		} catch (\Exception $e) {
			$this->connection->chatSendServerMessage('$d4fPersonal Messages $fff$w»$z$s$fff Error: ' . $e->getMessage(), $login);
			Console::println('[' . date('H:i:s') . '] [MLEPP] [Communication] [' . $login . '] Error: ' . $e->getMessage());
		}
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

}

?>