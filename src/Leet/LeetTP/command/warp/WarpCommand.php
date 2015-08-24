<?php

namespace Leet\LeetTP\command\warp;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WarpCommand implements CommandExecutor {

    private $plugin;
    private $cooldown;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->cooldown = [];
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot warp. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.warp')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if the player has a cooldown.
        if(isset($this->cooldown[$sender->getName()])) {
            $time = time() - $this->cooldown[$sender->getName()];
            if($time < $this->plugin->getHomeManager()->getCooldown()) {
                $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->cooldown_wait, $this->plugin->getHomeManager()->getCooldown() - $time));
                return true;
            }
        }

        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_name_missing);
            return true;
        }

        if($args[0] === '') {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_name_missing);
            return true;
        }

        $isPublic = true;

        if(count($args) > 1) {

            foreach($args as $argument) {
                if(strtolower($argument) !== '-p') continue;
                $isPublic = false;
                break;
            }

        }

        $warp = null;
        $privateExists = false;

        if($isPublic === true) {
            $warps = $this->plugin->getWarpManager()->getPublic();

            # Check if there is a public warp with that name.
            if(!isset($warps[strtolower($args[0])])) {
                $sender->sendMessage($this->plugin->getMessageHandler()->warp_not_exists);
                return true;
            }

            $warp = $this->plugin->getWarpManager()->warpToLocation($warps[strtolower($args[0])], $args[0]);

        }

        # If warp is NULL, then it is either private or does not exist.
        if($warp === null) {

            $warp = $this->plugin->getWarpManager()->warpToLocation($sender->getName(), $args[0]);

            # Warp does not exist.
            if($warp === null) {
                $sender->sendMessage($this->plugin->getMessageHandler()->warp_not_exists);
                return true;
            }

            # Check if a warp with the same name exists that is public.
            if($isPublic === true AND $warp !== null) {
                $privateExists = true;
            }

        }

        if($privateExists === true) $sender->sendMessage($this->plugin->getMessageHandler()->warp_public_exists);

        # Check if world is loaded.
        if(!$this->plugin->getServer()->isLevelLoaded($warp->getLevel()->getName())) {
            $sender->sendMessage($this->plugin->getMessageHandler()->world_not_loaded);
            return true;
        }

        $sender->teleport($warp);

        $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->warp_teleported, $args[0]));

        # Attach timestamp for last used /warp.
        $this->cooldown[$sender->getName()] = time();

        return true;

    }
}