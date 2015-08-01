<?php

namespace Leet\LeetTP\command\warp;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetWarpCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot set warps. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.setwarp')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if a name is specified.
        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_name_missing);
            return true;
        }

        # Check if player has a warp with that name already.
        if($this->plugin->getWarpManager()->getWarp($sender->getName(), $args[0]) !== null) {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_exists);
            return true;
        }

        $isPublic = false;

        # Iterate arguments and see if the public modifier is present.
        if(count($args) > 1) {
            foreach($args as $argument) {
                if(strtolower($argument) !== '-p') continue;
                $isPublic = true;
                break;
            }
        }

        if($args[0] === '') {
            $sender->sendMessage($this->plugin->getMessageHandler()->warp_name_missing);
            return true;
        }

        if($isPublic === true) {

            $warps = $this->plugin->getWarpManager()->getPublic();

            # Check if there is a public warp with that name already.
            if(isset($warps[strtolower($args[0])])) {
                $sender->sendMessage($this->plugin->getMessageHandler()->warp_exists);
                return true;
            }

            $this->plugin->getWarpManager()->addPublic($sender->getName(), $args[0]);

        }

        $location = $sender->getLocation();

        $result = $this->plugin->getWarpManager()->setWarp($sender->getName(), [
            'name' => $args[0],
            'world' => $location->getLevel()->getName(),
            'x' => $location->getX(),
            'y' => $location->getY(),
            'z' => $location->getZ(),
            'yaw' => $location->getYaw(),
            'pitch' => $location->getPitch(),
            'public' => $isPublic
        ]);

        # Check if the warp already exists.
        if($result === null) {
            $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->warp_exists, $args[0]));
            return true;
        }

        if($result === false) {
            $sender->sendMessage(TextFormat::RED.'Result is FALSE, some data is missing. Please report this.');
            return true;
        }

        $sender->sendMessage($this->plugin->getMessageHandler()->warp_set);

        return true;

    }
}