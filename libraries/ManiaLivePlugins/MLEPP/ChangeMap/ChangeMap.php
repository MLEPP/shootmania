<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name ChangeMap
 * @date 08-13-2012
 * @version 0.2.0
 * @author MuNgLo
 *
 * -- MLEPP Core --
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
 *
 * Notes by MuNgLo
 * ChangeMap plugin was based on the original votemap plugin for MLEPP (v0.2.2)
 *
 */

namespace ManiaLivePlugins\BAZINGA\ChangeMap;

use ManiaLib\Gui\Elements\Icons128x128_1;
use ManiaLive\Data\Storage;
use ManiaLive\Utilities\Console;
use ManiaLive\Utilities\Time;
use ManiaLive\Features\Admin\AdminGroup;
use ManiaLib\Utils\Formatting as String;
use DedicatedApi\Connection;
use ManiaLivePlugins\BAZINGA\ChangeMap\Gui\Windows\changemapWindow;
use ManiaLivePlugins\BAZINGA\ChangeMap\Gui\Windows\addmapWindow;
use ManiaLivePlugins\BAZINGA\ChangeMap\Gui\Windows\playlistWindow;
use ManiaLivePlugins\BAZINGA\ChangeMap\Gui\Windows\bazWindow;
use ManiaLive\Event\Dispatcher;

class ChangeMap extends \ManiaLive\PluginHandler\Plugin {

	protected $listNominatedChallenges = array();
	protected $listNextChallenges = array();
	private $vote;
	private $votes = array();
	private $Lastvote = array("Playlist" => "none", "specpass" => "none");
	private $mlepp;
	private $config;
	
	// Workaround : And this should be the path to the folder the maps are stored in
	// 				relative to Userfiles
	private $mapFolder = "ShootMania\\Elite\\";
	private $GameMode = "Elite";
	private $import = "TRUE";
	private $PlaylistArray = array();
	

	function onInit() {
		$this->setVersion('0.4.4');
		$this->config = Config::getInstance();
	}

	function onLoad() {		
		$admins = AdminGroup::get();
		Console::println('[' . date('H:i:s') . '] [BAZINGA] Plugin: ChangeMap v' . $this->getVersion());
		$this->enableDedicatedEvents();
		 if ($this->isPluginLoaded('MLEPP\Admin', '0.5.0') | $this->isPluginLoaded('Standard\Menubar', '1.1') ) {
			if($this->isPluginLoaded('Standard\Menubar')) {
					$this->buildMenu(); 
					$this->registerChatCommand("maplistsave", "maplistSave", 1, true, $admins);
				}else{
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'displayBazWindow'), 'baz', true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'displayWindowPlaylist'), 'Playlist', true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'displayWindowChangeMap'), 'ChangeMap', true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'displayWindowAddMap'), 'AddMap', true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'displayWindowRemoveMap'), 'RemoveMap', true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'maplistSave'), array("set", "maplist", "save"), true, false, false);
					$this->callPublicMethod('MLEPP\Admin', 'addAdminCommand', array($this, 'maplistLoad'), array("set", "maplist", "load"), true, false, false);
					$this->callPublicMethod('MLEPP\Core', 'registerPlugin', ' ChangeMap', $this);
					}
			}else{
			// register chatcommands for admins
			$this->registerChatCommand("changemap", "displayWindowChangeMap", 0, true, $admins);
			$this->registerChatCommand("addmap", "displayWindowAddMap", 0, true, $admins);
			$this->registerChatCommand("removemap", "displayWindowRemoveMap", 0, true, $admins);
			
			$this->registerChatCommand("maplistsave", "maplistSave", 1, true, $admins);
			$this->registerChatCommand("maplistload", "maplistLoad", 1, true, $admins);
			}
		if(!in_array('VoteMap', $this->config->disable)) {
			$this->registerChatCommand("votemap", "mapvote", 1, true);
			$this->registerChatCommand("votemap", "mapvote", 0, true);
			$this->registerChatCommand("vm", "mapvote", 1, true);
			$this->registerChatCommand("vm", "mapvote", 0, true);
		}
		if(!in_array('VotePCW', $this->config->disable)) {
		$this->registerChatCommand("pcw", "pcwvotehelp", 0, true);
		$this->registerChatCommand("pcw", "pcwvote", 1, true);
		}
		if(!in_array('VotePrivate', $this->config->disable)) {
		$this->registerChatCommand("voteprivate", "privvote", 0, true);
		$this->registerChatCommand("voteprivate", "privvote", 1, true);
		}
		$this->gameMode();
		$this->importMapPool();
		$this->importPlaylistPool();
	}

	function gameMode() {
		$mode = $this->connection->getScriptName();
		switch ($mode['CurrentValue'])	{
			case "ShootMania\Elite.Script.txt":
				$this->GameMode = "Elite";
				break;
			case "ShootMania\BattleWaves.Script.txt":
				$this->GameMode = "BattleWaves";
				break;
			case "ShootMania\Royal.Script.txt":
				$this->GameMode = "Royal";
				break;
			case "ShootMania\Melee.Script.txt":
				$this->GameMode = "Melee";
				break;
			case "ShootMania\Battle.Script.txt":
				$this->GameMode = "Battle";
				break;
			default:
				$this->GameMode = "MixedScripts";
		}
	}
	
public function displayWindowChangeMap($login) {
		$window = changemapWindow::Create($login);
		$challenges = $this->connection->getMapList(-1, 0);
		$window->setInfos($challenges, 'Choose a map to change to');
		$window->setAction(array($this, 'changeMap'));
		$window->show();
	}
	
public function displayWindowRemoveMap($login) {
		$window = changemapWindow::Create($login);
		$challenges = $this->connection->getMapList(-1, 0);
		$window->setInfos($challenges, 'Choose a map to remove it');
		$window->setAction(array($this, 'removeMap'));
		$window->show();
	}
	// Show playlist Window
public function displayWindowPlaylist($login) {
		$this->importPlaylistPool();
		$window = PlaylistWindow::Create($login);
		$Playlists = $this->PlaylistArray;
		$x = 0;
		$window->setInfos($Playlists, 'Choose a Playlist to Load');
		$window->setAction(array($this, 'maplistLoad'));
		$window->show();
	}
	// Show Baz Window
public function displayBazWindow($login) {
		$this->importPlaylistPool();
		$window = BazWindow::Create($login);
		$Playlists = $this->PlaylistArray;
		$x = 0;
		$window->setInfos($Playlists, 'BazWindow');
		$window->setAction(array($this, 'maplistLoad'));
		$window->show();
	}
	// Function to make array of available playlists
	function importPlaylistPool() {
			$this->PlaylistArray = array();
			if ($handle = opendir('libraries\ManiaLivePlugins\BAZINGA\ChangeMap\Playlists\\'.$this->GameMode)) {
			/* This is the correct way to loop over the directory. */
				while (false !== ($entry = readdir($handle))) {
					if(!($entry=="MapPool.txt" | $entry=="." | $entry==".." )) {
						array_push($this->PlaylistArray, str_replace(".txt", "",$entry));
						}
				}
			closedir($handle);
			}
	}
	// Show Addmap window
public function displayWindowAddMap($login) {
		if ($this->import == "false" ) {
			$this->connection->chatSendServerMessage('$f00»» $fffNo MapPool.txt was imported on load.', $login); return;
			}
		$window = addmapWindow::Create($login);
		$playlist = $this->connection->getMapList(-1, 0);
		$maps = $this->mapArray;
		$x = 0;
		// TODO  : filter to ignore already added maps
		$window->setInfos($maps, 'Choose a map to add');
		$window->setAction(array($this, 'addMap'));
		$window->show();
	}
	// Function to insert new maps into the active playlist
	function addMap($login, $i, $mapName, $fileName) {
				$this->connection->chatSendServerMessage('$z$s$080 added the map $fff' . $mapName . '$z$s$080 to the playlist!', $login);
				$this->connection->insertMap($this->mapFolder.$fileName);
	}
	// Function for changing to clicked map
	function changeMap($login, $i, $challengeName, $fileName) {
		$this->listNominatedChallenges[] = array('id' => $i, 'name' => $challengeName, 'login' => $login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename' => $fileName);
		$this->connection->chatSendServerMessage('$fff»» $fff' . $this->connection->getPlayerInfo($login)->nickName . '$z$s$080 changed map to $fff' . $challengeName . '$z$s$080!');
		$this->connection->removeMap($fileName);
		$this->connection->insertMap($fileName);
		$this->connection->nextMap();
	}
	// Function for removing clicked map
	function removeMap($login, $i, $challengeName, $fileName) {
		$this->listNominatedChallenges[] = array('id' => $i, 'name' => $challengeName, 'login' => $login, 'nickname' => $this->connection->getPlayerInfo($login)->nickName, 'filename' => $fileName);
		$this->connection->chatSendServerMessage('$fff»» $fff' . $this->connection->getPlayerInfo($login)->nickName . '$z$s$080 removed the map $fff' . $challengeName . ' $z$s$080from the playlist!', $login);
		$this->connection->removeMap($fileName);
	}
	// Function to import MapPool.txt
	function importMapPool() {
		$filename = "libraries\ManiaLivePlugins\BAZINGA\ChangeMap\Playlists\\".$this->GameMode."\MapPool.txt";
		if (file_exists($filename)) {
			$MapPool = explode("\n", file_get_contents($filename));
			Console::println('[' . date('H:i:s') . '] [BAZINGA] Plugin: ChangeMap: importing MapPool.txt');
			$this->mapArray = $MapPool;
		}else{
			Console::println('[' . date('H:i:s') . '] [BAZINGA] Plugin: ChangeMap: MapPool.txt not Located for '.$this->GameMode.' mode.');
			$this->import = "false";
		}
	}
	// Function to Load a saved playlist
	function maplistLoad($login, $text=NULL, $gog=NULL, $gig=NULL) {
		if($text == null){ $text = "default"; }
		if(!$gog == null){ $text = $gog; }
		$filename = "libraries\ManiaLivePlugins\BAZINGA\ChangeMap\Playlists\\".$this->GameMode."\\".$text.".txt";
		if (file_exists($filename)) {
			$playlist = explode("\n", $this->currentPlaylistArray());
			$this->connection->removeMapList($playlist);
			
			$MapPool = explode("\n", file_get_contents($filename));
			$this->connection->addMapList($MapPool);
			$this->connection->chatSendServerMessage('$fff»» Playlistfile "'.$text.'" was loaded.');
		}else{
			// File could not be find
			$this->connection->chatSendServerMessage('$f00»» $fffPlaylistfile "'.$text.'" was not found.');
		}
	}
	// Function to save a playlist
	function maplistSave($login, $text=NULL) {
		if($text == null){ $text = "default"; }
		if(!preg_match("/^[a-zA-Z0-9]+$/", $text) == 1) {
				$this->connection->chatSendServerMessage('$f00»» $fff"'.strtolower($text).'" contains invalid characters. Please choose another name', $login); 
				return;}
		if(strtolower($text) == "mappool"){
				$this->connection->chatSendServerMessage('$f00»» $fff"'.strtolower($text).'" is an invalid name. Please choose another name', $login); 
				return;}
		Console::println('[' . date('H:i:s') . '] [BAZINGA] [ChangeMap] '.$login.' saved a playlist. filename: '.$text);
		$fileloc = "libraries\ManiaLivePlugins\BAZINGA\ChangeMap\Playlists\\".$this->GameMode."\\";
		$file = $fileloc . strtolower($text) . ".txt";
		$playlist = $this->currentPlaylistArray();
		$playlistfile = fopen($file, "w");
		fwrite($playlistfile, $playlist);
		fclose($playlistfile);
		$this->connection->chatSendServerMessage('$fff»» Playlistfile "'.strtolower($text).'" was saved under folder '.$this->GameMode.'.', $login);
	}
	// Get current playlist in simple array
	function currentPlaylistArray() {
		$challenges = $this->connection->getMapList(-1, 0);
		$x = 0;$PLA = "";
		while($x < count($challenges)){	$PLA = $PLA . $challenges[$x]->fileName . "\n";$x++; }
		return $PLA;
	}
	/*
	*
	*		CUSTOM MAPVOTE of all in playlist and mappool
	*
	*/
	function mapvote($login, $text=NULL) {
		if($text==NULL){ 
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fffYou need to give a searchfilter of minimum 4', $login);
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fffcharacters. ie "$6fc/votemap arch$fff" or "$6fc/votemap hway$fff"', $login);
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fffThen arch/hway gets matched to Archways and vote is started.', $login);
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fffAlso the searchfilter must only have one match.', $login);
				return;
				}
		if(strlen($text)<3){
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fff'.$text.' was to short. It needs to be 4 characters.', $login);
				return;
				}
		$hits = array(); $i = 0;
		foreach($this->mapArray as $value) {
				$search = '/'.strtolower($text).'/';
				if(preg_match($search, strtolower($value), $match)) {
					$value = str_replace(".Map.Gbx", "" ,$value);
					array_push($hits, $value);
					$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fff'.$text.' match to '.$value, $login);
				}
				$i++;
			}
			if(count($hits)>1){
				$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fff'.$text.' gave to many matches. try to be more specific.', $login);
				return;
				}
			if(isset($hits[0])){
				$this->connection->chatSendServerMessage('$fff»» $z $6FCMapVote : $fff'.$this->connection->getPlayerInfo($login)->nickName.' $z$fffstarted a vote to change to '.$hits[0]);

				$vote = new \DedicatedApi\Structures\Vote();
				$vote->cmdName = 'Echo';
				$vote->cmdParam = array('Change map to '.$hits[0], 'baz_mapvote');
				// $this->connection->callVote($vote, $ratio = 0.7, $timeout = 20, $voters = 0, $multicall = false);
				$this->connection->callVote($vote);
				
				// $vote = new \DedicatedApi\Structures\Vote();
				// $vote->cmdName = 'Kick';
				// $vote->cmdParam = array('munglo');
				// if(is_object($vote)) {Console::println( print_r($vote) );}
				// $this->connection->callVote($vote);
				return;
				}
			$this->connection->chatSendServerMessage('$099»» $z $6FCMapVote : $fffSorry but no match could be made with $6fc'.$text.'$fff. Please try something else.', $login);
	}
	function privvote($login, $text=NULL) {
		$this->connection->chatSendServerMessage('$099»» $z $6FCPrivVote : $fffcalled to set server private. Kick specs and lock down spectator slots with a code.');
		$vote = new \DedicatedApi\Structures\Vote();
				$vote->cmdName = 'Echo';
				$vote->cmdParam = array('Do you want to make this match private?', 'baz_privvote');
				$this->connection->callVote($vote, 0.5, 30000, 1);
		if(isset($text)){$this->Lastvote['specpass'] = $text;}
	}
	function pcwvote($login, $text) {
		$this->connection->chatSendServerMessage('$099»» $z $6FCPrivVote : $fffcalled to start a PCW with '.$text.' as playlist.');
		$vote = new \DedicatedApi\Structures\Vote();
				$vote->cmdName = 'Echo';
				$vote->cmdParam = array('Setup a match with '.$text.' as playlist?', 'baz_pcwvote');
				$this->connection->callVote($vote, 0.5, 30000, 1);
		$this->Lastvote['Playlist'] = $text;
	}
	function pcwvotehelp($login) {
		$this->connection->chatSendServerMessage('$f00»» $z $6FCPCW-Vote : $fffYou need to choose playlist. Choose between "ESL", "IPL" and "ESWC".', $login);
		$this->connection->chatSendServerMessage('$f00»» $z $6FCPCW-Vote : $fffExample -> "/pcw esl" or "/pcw ipl"', $login);
	}
	function onEcho($what, $map) {
		// Console::println('[' . date('H:i:s') . '] [BAZINGA] ChangeMap onEcho : text:'.$what.'   text2:'.$map);
		switch ($what)	{
			case "baz_mapvote":
				$map = trim(str_replace("Change map to ", "", $map)).".Map.Gbx";
				try {
					$this->connection->insertMap($this->mapFolder . $map);
					} catch(\Exception $e) {}
				$this->connection->removeMap($this->mapFolder . $map);
				$this->connection->insertMap($this->mapFolder . $map);
				$this->connection->nextMap();
				break;
			case "baz_privvote":
				$specPIN = trim(" ".floor(rand(1000, 9999)));
				$players = $this->connection->getPlayerList(20,0);
				foreach($players as $player) {
					$isSpec = $player->spectator;
					if($isSpec=="1") {
						$msg = "This server is goin private.";
						$this->connection->kick($player->login, $msg);
						}
					}
				$specpass = trim(strtolower($this->Lastvote['specpass']));
				if($this->Lastvote['specpass'] != "none"){$specPIN = $specpass;}
				if (empty($specPIN)) {
					$specPIN = "";
					}

					try {
						$this->connection->setServerPasswordForSpectator($specPIN);
						$this->connection->chatSendServerMessage('$f6c»» $fffSpectator Password is now ' . $specPIN);
					} catch (\Exception $e) {
						$this->connection->chatSendServerMessage('$f00» $fffSpectator Password was not set.');
					}
				$this->connection->setServerPasswordForSpectator($specPIN);
				$this->connection->chatSendServerMessage('$099»» $z $6FCPrivVote : $fffServer is now private and Spectator password is '.$specPIN);
				$this->Lastvote['specpass'] = "none";
				break;
			case "baz_pcwvote":
				$map = trim(strtolower($this->Lastvote['Playlist']));
				$specPIN = trim(" ".floor(rand(1000, 9999)));
				$players = $this->connection->getPlayerList(20,0);
				foreach($players as $player) {
					$isSpec = $player->spectator;
					if($isSpec=="1") {
						$msg = "This server is goin private.";
						$this->connection->kick($player->login, $msg);
						}
					}
				if (empty($specPIN)) {
					$specPIN = "";
					}

					try {
						$this->connection->setServerPasswordForSpectator($specPIN);
						$this->connection->chatSendServerMessage('$f6c»» $fffSpectator Password is now ' . $specPIN);
					} catch (\Exception $e) {
						$this->connection->chatSendServerMessage('$f00» $fffSpectator Password was not set.');
					}
				$this->connection->setServerPasswordForSpectator($specPIN);
				$this->connection->chatSendServerMessage('$099»» $z $6FCPrivVote : $fffServer is now private and Spectator password is '.$specPIN);
				$this->maplistLoad("--BAZINGA--", $map);
				$this->Lastvote['Playlist'] = "none";
				break;
			}
	}
	function mode_onEndMap($param=null){
		$specPIN = "";
		if($this->connection->getServerPasswordForSpectator()==""){Console::println('[' . date('H:i:s') . '] [BAZINGA] ChangeMap onendMap : specpass reset not needed.');return;}
			try {
				$this->connection->setServerPasswordForSpectator($specPIN);
				
				$this->connection->chatSendServerMessage('$f6c»» $fffSpectator Password is reseted ');
			} catch (\Exception $e)	{
						Console::println('[' . date('H:i:s') . '] [BAZINGA] ChangeMap onendMap : specpass WAS NOT reset.');
					}
	}
	//		Add button to Standard MenuBar
		function buildMenu()
	{
		$this->callPublicMethod('Standard\Menubar',
			'initMenu',
			Icons128x128_1::Options);
		
		$this->callPublicMethod('Standard\Menubar',
			'addButton',
			'Change map',
			array($this, 'displayWindowChangeMap'),
			true);

		$this->callPublicMethod('Standard\Menubar',
			'addButton',
			'Add map',
			array($this, 'displayWindowAddMap'),
			true);
			
		$this->callPublicMethod('Standard\Menubar',
			'addButton',
			'Remove map',
			array($this, 'displayWindowRemoveMap'),
			true);
		$this->callPublicMethod('Standard\Menubar',
			'addButton',
			'Load Playlist',
			array($this, 'displayWindowPlaylist'),
			true);
	}
// EOF
}
?>