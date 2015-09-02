<?php

namespace Leet\LeetTP\command\home;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class HomesCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!$sender->hasPermission('leettp.command.homes')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $homes = $this->plugin->getHomeManager()->getHomes($sender->getName());
        $message = '';

        $color = true;
        if($homes === null or $homes === false) {
            $message = TextFormat::GRAY.'You have no homes.';
        } else {
            foreach($homes as $name => $home) {
                $message = $message.($color ? TextFormat::WHITE : TextFormat::GRAY).$name.', ';
                $color = !$color;
            }
            $sender->sendMessage(TextFormat::GRAY.'Your homes:');
        }

        $sender->sendMessage(rtrim($message, ','));

        return true;

    }

}