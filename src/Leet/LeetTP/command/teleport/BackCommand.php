<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BackCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot teleport. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.back')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        if(!isset($this->plugin->deaths[$sender->getName()])) {
            $sender->sendMessage($this->plugin->getMessageHandler()->back_empty);
            return true;
        }

        $sender->teleport($this->plugin->deaths[$sender->getName()]);

        $sender->sendMessage($this->plugin->getMessageHandler()->back_teleported);

        # Clean up.
        unset($this->plugin->deaths[$sender->getName()]);

        return true;

    }
}