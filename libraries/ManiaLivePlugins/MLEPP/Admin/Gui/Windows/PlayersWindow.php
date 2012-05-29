<?php

namespace ManiaLivePlugins\MLEPP\Admin\Gui\Windows;

use \ManiaLive\Gui\Controls\Pager;
use ManiaLive\Data\Storage;

class PlayersWindow extends \ManiaLive\Gui\ManagedWindow {

    private $pager;
    public static $adminPlugin;

    function onConstruct() {
        parent::onConstruct();
        $this->setTitle('List of players');        
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
        foreach (Storage::getInstance()->players as $player)
            $this->pager->addItem(new \ManiaLivePlugins\MLEPP\Admin\Gui\Controls\ViewPlayer($player, self::$adminPlugin));
        foreach (Storage::getInstance()->spectators as $player)
            $this->pager->addItem(new \ManiaLivePlugins\MLEPP\Admin\Gui\Controls\ViewPlayer($player, self::$adminPlugin));
        
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