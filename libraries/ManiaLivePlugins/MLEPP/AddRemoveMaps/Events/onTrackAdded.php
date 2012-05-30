<?php

namespace ManiaLivePlugins\MLEPP\AddRemoveMaps\Events;

class onTrackAdded extends \ManiaLive\Event\Event {
	protected $login;
	protected $filename;
	protected $isTmx;
	protected $gameversion;
	
	function __construct($login, $filename, $isTmx = false, $gameversion = NULL) {
		$this->login = $login;
		$this->filename = $filename;
		$this->isTmx = $isTmx;
		$this->gameversion = $gameversion;
	}
	
	function fireDo($listener) {
		call_user_func_array(array($listener, 'onTrackAdded'), array($this->login, $this->filename, $this->isTmx, $this->gameversion));
	}
}


?>