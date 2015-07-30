<?php

namespace Leet\LeetTP\listener;

use Leet\LeetTP\LeetTP;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerRespawnEvent;

class TPListener implements Listener {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onPlayerSleep(PlayerBedEnterEvent $event) {

        if($event->isCancelled()) return;

        if(!$this->plugin->getHomeManager()->bed_set_home) return;

        # Just dispatch the command, surely that is cleaner than writing the code twice?
        $this->plugin->getServer()->dispatchCommand($event->getPlayer(), 'sethome bed');

    }

    /**
     * Teleports a player back to bed when they respawn.
     *
     * @param PlayerRespawnEvent $event
     */
    public function onPlayerRespawn(PlayerRespawnEvent $event) {

        $homes = $this->plugin->getHomeManager()->getHomes($event->getPlayer()->getName());

        # Return if there are no bed.
        if(!isset($homes['bed'])) return;

        $event->getPlayer()->teleport($this->plugin->getHomeManager()->homeToLocation($event->getPlayer()->getName(), 'bed'));

    }

}