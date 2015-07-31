<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TpoHereCommand implements CommandExecutor {

    private $plugin;
    private $tpManager;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->tpManager = $plugin->getTeleportManager();
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot teleport. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.tpohere')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_name_missing);
            return true;
        }

        $target = $this->plugin->getServer()->getPlayer($args[0]);

        # Player is not online or may be auto-completed.
        if($target === null) {

            $players = [];
            /** @var Player $player */
            foreach($this->plugin->getServer()->getOnlinePlayers() as $player) array_push($players, $player->getName());
            sort($players);

            foreach($players as $player) {
                if(substr(strtolower($player), 0, strlen($args[0])) !== strtolower($args[0])) continue;
                $target = $this->plugin->getServer()->getPlayer($player);
                # We found our guy, let's get outta here!
                break;
            }

            # Ensure that we have a target before proceeding.
            if($target === null) {
                $sender->sendMessage(TextFormat::RED.'Player was not found, the player may be offline.');
                return true;
            }

        }

        $target->teleport($sender->getPosition());

        $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->tp_tpahere_success, $target->getName()));

        return true;

    }
}