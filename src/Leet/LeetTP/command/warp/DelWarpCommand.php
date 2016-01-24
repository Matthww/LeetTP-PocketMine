<?php

namespace Leet\LeetTP\command\warp;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DelWarpCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot delete warps. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.delwarp')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if a name is specified.
        if(count($args) < 1 || $args[0] === '') {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_name_missing);
            return true;
        }

        $warp = $this->plugin->getWarpManager()->getWarp($sender->getName(), $args[0]);

        # Check if the player has a warp with that name.
        if($warp === null AND !$sender->isOp()) {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_not_exists);
            return true;
        }

        $another = false;
        $player = 'TemporaryPlayerNameThatShouldNotExist';

        # Sender may be attempting to delete a public warp that isn't theirs.
        if($warp === null) {
            $warps = $this->plugin->getWarpManager()->getPublic();
            if(isset($warps[strtolower($args[0])])) $player = $warps[strtolower($args[0])];
            $warp = $this->plugin->getWarpManager()->getWarp($player, $args[0]);
            if($warp === null) {
                $sender->sendMessage(TextFormat::RED.'Could not find a public warp named that from another player.');
                return true;
            }
            $another = true;
        }

        # If the warp is public, remove the pointer.
        if($warp['public'] === true) $this->plugin->getWarpManager()->removePublic($warp['name']);

        $this->plugin->getWarpManager()->deleteWarp(($another ? $player : $sender->getName()), $warp['name']) ? $sender->sendMessage($this->plugin->getMessageHandler()->warp_deleted) : $sender->sendMessage($this->plugin->getMessageHandler()->warp_not_deleted);

        return true;

    }
}