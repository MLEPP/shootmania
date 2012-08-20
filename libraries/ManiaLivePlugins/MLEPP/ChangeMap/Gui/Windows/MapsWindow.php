<?php
/**
 * Change map plugin for MLEPP v.0.2.1 (20/08/12)
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
namespace ManiaLivePlugins\MLEPP\ChangeMap\Gui\Windows;

use \ManiaLive\Gui\Controls\Pager;
use ManiaLive\Data\Storage;

class MapsWindow extends \ManiaLive\Gui\ManagedWindow {

    private $pager;
    public static $adminPlugin;

    function onConstruct() {
        parent::onConstruct();
        $this->setTitle('Change maps');        
        $this->pager = new Pager();
        $this->pager->setPosition(2, -24);
        $this->pager->setStretchContentX(true);
        $this->addComponent($this->pager);
        $this->updateData();
    }

    function onDraw() {
        parent::onDraw();
    }

    public function updateData() {
        $this->pager->clearItems();
        foreach (Storage::getInstance()->maps as $map)
            $this->pager->addItem(new \ManiaLivePlugins\MLEPP\ChangeMap\Gui\Controls\ViewMap($map, self::$adminPlugin));
        
        
        $this->redraw();
    }

    function destroy() {
        self::$adminPlugin = null;
        parent::destroy();
    }

    function onResize($oldX, $oldY) {
        parent::onResize($oldX, $oldY);
        $this->pager->setSize($this->sizeX - 4, $this->sizeY - 30);
    }


}