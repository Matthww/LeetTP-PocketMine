<?php

namespace Leet\LeetTP\listener;

use Leet\LeetTP\LeetTP;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\Player;

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

    /**
     * Records the location of the death of the player
     * so it can be returned to using /back.
     *
     * @param PlayerDeathEvent $event
     */
    public function onPlayerDeath(PlayerDeathEvent $event) {

        # Unsure if PocketMine fires PlayerDeathEvent on other DeathEvents... It may.
        if(!($event->getEntity() instanceof Player)) return;

        # Only process if they can access the back command.
        if(!$event->getEntity()->hasPermission('leettp.command.back')) return;

        $this->plugin->deaths[$event->getEntity()->getName()] = $event->getEntity()->getPosition();

    }

}