<?php

namespace Leet\LeetTP\listener;

use Leet\LeetTP\LeetTP;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\tile\Sign;
use pocketmine\utils\TextFormat;

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

    /**
     * Check if the player used a warp sign,
     * and teleport if they did.
     *
     * @param PlayerInteractEvent $event
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {

        # We only want to continue if the block in question is a Sign Post or Wall Sign.
        if ($event->getBlock()->getId() !== 63 and $event->getBlock()->getId() !== 68) return;

        $tile = $event->getBlock()->getLevel()->getTile(new Vector3(
                $event->getBlock()->getX(),
                $event->getBlock()->getY(),
                $event->getBlock()->getZ())
        );

        # Double check that the tile is a Sign.
        if (!$tile instanceof Sign) {
            $this->plugin->getLogger()->error('Tile was not a instance of Sign at X: ' .
                $event->getBlock()->getX() . ' Y: ' .
                $event->getBlock()->getY() . ' Z: ' .
                $event->getBlock()->getZ()
            );

            return;
        }

        # Only process further if the sign is actually containing text on line 2.
        if(empty($tile->getText()[1])) return;

        # Only process further is the first line of the sign is [warp].
        if(strtoupper($tile->getText()[0]) !== '[WARP]') return;

        $warps = $this->plugin->getWarpManager()->getPublic();

        # Check if there is a public warp with that name.
        if(!isset($warps[strtolower($tile->getText()[1])])) {
            $event->getPlayer()->sendMessage($this->plugin->getMessageHandler()->warp_not_exists);
            return;
        }

        $warp = $this->plugin->getWarpManager()->warpToLocation($warps[strtolower($tile->getText()[1])], $tile->getText()[1]);

        $event->getPlayer()->teleport($warp);

        $event->getPlayer()->sendMessage(sprintf($this->plugin->getMessageHandler()->warp_teleported, $tile->getText()[1]));

    }

    /**
     * Check if the sign created is a
     * warp sign and create a public
     * warp if it is.
     *
     * @param SignChangeEvent $event
     */
    public function onSignChange(SignChangeEvent $event) {

        if(!$event->getPlayer()->hasPermission('leettp.warp.public')) return;

        if(count($event->getLines()) < 2) return;

        if(strtoupper($event->getLine(0)) !== '[WARP]') return;

        $warps = $this->plugin->getWarpManager()->getPublic();

        # Check if there is a public warp with that name already.
        if(isset($warps[strtolower($event->getLine(1))])) {
            $event->getPlayer()->sendMessage($this->plugin->getMessageHandler()->warp_exists);
            return;
        }

        $this->plugin->getWarpManager()->addPublic($event->getPlayer()->getName(), explode(' ', $event->getLine(1))[0]);

        $location = $event->getPlayer()->getLocation();

        $result = $this->plugin->getWarpManager()->setWarp($event->getPlayer()->getName(), [
            'name' => explode(' ', $event->getLine(1))[0],
            'world' => $location->getLevel()->getName(),
            'x' => $location->getX(),
            'y' => $location->getY(),
            'z' => $location->getZ(),
            'yaw' => $location->getYaw(),
            'pitch' => $location->getPitch(),
            'public' => true
        ]);

        # Check if the warp already exists.
        if($result === null) {
            $event->getPlayer()->sendMessage(sprintf($this->plugin->getMessageHandler()->warp_exists, explode(' ', $event->getLine(1))[0]));
            return;
        }

        if($result === false) {
            $event->getPlayer()->sendMessage(TextFormat::RED.'Result is FALSE, some data is missing. Please report this.');
            return;
        }

        $event->getPlayer()->sendMessage($this->plugin->getMessageHandler()->warp_set);

    }
}