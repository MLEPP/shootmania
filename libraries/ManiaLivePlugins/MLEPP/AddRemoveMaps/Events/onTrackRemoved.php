<?php

namespace ManiaLivePlugins\MLEPP\AddRemoveMaps\Events;

class onTrackRemoved extends \ManiaLive\Event\Event {
	protected $login;
	protected $filename;
	
	function __construct($login, $filename) {
		$this->login = $login;
		$this->filename = $filename;
	}
	
	function fireDo($listener) {
		call_user_func_array(array($listener, 'onTrackRemoved'), array($this->login, $this->filename));
	}
}


?>