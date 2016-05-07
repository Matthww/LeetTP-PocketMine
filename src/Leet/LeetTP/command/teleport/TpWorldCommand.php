<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TpWorldCommand implements CommandExecutor {
    
    private $plugin;
    
    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot teleport. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.tpworld')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_world_name_missing);
            return true;
        }

        $world = $args[0];

        if(!$this->plugin->getServer()->isLevelLoaded($world)) {
            $sender->sendMessage($this->plugin->getMessageHandler()->world_not_loaded);
            return true;
        }

        $sender->teleport($this->plugin->getServer()->getLevelByName($world)->getSpawnLocation());

        $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->tp_world_success, $world));

        return true;
    }
}