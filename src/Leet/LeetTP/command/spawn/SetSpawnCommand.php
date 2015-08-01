<?php

namespace Leet\LeetTP\command\spawn;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetSpawnCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot set spawn. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.setspawn')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $sender->getLevel()->setSpawnLocation($sender->getPosition());

        $sender->sendMessage($this->plugin->getMessageHandler()->spawn_set);

        return true;

    }
}