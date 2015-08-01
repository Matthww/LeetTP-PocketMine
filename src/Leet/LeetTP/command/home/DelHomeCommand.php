<?php

namespace Leet\LeetTP\command\home;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class DelHomeCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot set home. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.delhome')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if a name has been specified.
        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->home_name_missing);
            return true;
        }

        if($args[0] === '') {
            $sender->sendMessage($this->plugin->getMessageHandler()->home_name_missing);
            return true;
        }

        $home = $this->plugin->getHomeManager()->getHome($sender->getName(), $args[0]);

        # A home by that name does not exist.
        if($home === null) {
            $sender->sendMessage($this->plugin->getMessageHandler()->home_not_exists);
            return true;
        }

        # Let the player know if it succeeded or not.
        $this->plugin->getHomeManager()->deleteHome($sender->getName(), $args[0]) ? $sender->sendMessage($this->plugin->getMessageHandler()->home_deleted) : $sender->sendMessage($this->plugin->getMessageHandler()->home_not_deleted);

        return true;

    }
}