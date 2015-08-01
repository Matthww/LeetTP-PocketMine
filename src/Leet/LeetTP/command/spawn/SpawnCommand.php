<?php

namespace Leet\LeetTP\command\spawn;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SpawnCommand implements CommandExecutor {

    private $plugin;
    private $cooldown;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->cooldown = [];
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot teleport to spawn. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.spawn')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if the player has a cooldown.
        if(isset($this->cooldown[$sender->getName()]) AND !$sender->isOp()) {
            $time = time() - $this->cooldown[$sender->getName()];
            if($time < $this->plugin->spawnCooldown) {
                $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->cooldown_wait, $this->plugin->spawnCooldown - $time));
                return true;
            }
        }

        $sender->teleport($sender->getLevel()->getSpawnLocation());

        $sender->sendMessage($this->plugin->getMessageHandler()->spawn_teleported);

        return true;

    }
}