<?php

/**
 * MLEPP - ManiaLive Extending Plugin Pack
 *
 * -- MLEPP Plugin --
 * @name IRC
 * @date 06-09-2011
 * @version r1045
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

namespace ManiaLivePlugins\MLEPP\IRC;

class Config extends \ManiaLib\Utils\Singleton {
    public $hostname = 0;
    public $server = 'irc.tweakers.net';
    public $port = 6667;
    public $realname = 'Botje';
    public $nickname = 'testbot.';
    public $ident = 'testbot';
    public $channels = array('#bots');
    
    public $authmethod = '';
    public $authpass = '';
    
    public $messageLength = 'long';
    public $disable = array();
    
    public $operCredentials = 'User Pass';
    public $operChghost = 'test.test.net';
}

?>