<?php

namespace Leet\LeetTP\command\warp;

use Leet\LeetTP\LeetTP;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class WarpsCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!$sender->hasPermission('leettp.command.warps')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $public = $this->plugin->getWarpManager()->getPublic();
        $warps = $this->plugin->getWarpManager()->getWarps($sender->getName());

        $sender->sendMessage(TextFormat::YELLOW.'Your warps:');

        $own = '';

        $color = true;
        if($warps === null or $warps === false) {
            $own = TextFormat::GRAY.'You have no warps.';
        } else {
            foreach($warps as $name => $warp) {
                $own = $own.($color ? TextFormat::WHITE : TextFormat::GRAY).$name.', ';
                $color = !$color;
            }
        }

        $sender->sendMessage(rtrim($own, ','));

        $sender->sendMessage(TextFormat::YELLOW.'Public warps:');

        $other = '';

        $color = true;
        foreach($public as $name => $player) {
            if($name === $sender->getName()) continue;
            $other = $other.($color ? TextFormat::WHITE : TextFormat::GRAY).$name.', ';
            $color = !$color;
        }
        $sender->sendMessage(rtrim($other, ','));

        return true;
    }
}