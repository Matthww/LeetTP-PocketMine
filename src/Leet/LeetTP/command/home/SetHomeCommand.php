<?php

namespace Leet\LeetTP\command\home;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetHomeCommand implements CommandExecutor {

    private $plugin;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot set home. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.sethome')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $homes = $this->plugin->getHomeManager()->getHomes($sender->getName());

        $home = null;

        # Check if a name has been given.
        if(count($args) < 1) {
            if($homes !== false) {
                $sender->sendMessage($this->plugin->getMessageHandler()->home_name_missing);
                return true;
            }
            $home = 'home';
        }

        if(count($args) >= 1) {
            if($args[0] === '') {
                $sender->sendMessage($this->plugin->getMessageHandler()->home_name_missing);
                return true;
            }
        }

        # Use name if home is still not set.
        if($home === null) {
            $home = $args[0];
        }

        $location = $sender->getLocation();

        $result = $this->plugin->getHomeManager()->setHome($sender->getName(), [
            'name' => $home,
            'world' => $location->getLevel()->getName(),
            'x' => $location->getX(),
            'y' => $location->getY(),
            'z' => $location->getZ(),
            'yaw' => $location->getYaw(),
            'pitch' => $location->getPitch()
        ]);

        # Check if the home already exists.
        if($result === null) {
            $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->home_exists, $home));
            return true;
        }

        $sender->sendMessage($this->plugin->getMessageHandler()->home_set);

        return true;

    }
}