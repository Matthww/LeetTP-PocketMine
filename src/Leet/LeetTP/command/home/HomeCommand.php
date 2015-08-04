<?php

namespace Leet\LeetTP\command\home;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class HomeCommand implements CommandExecutor {

    private $plugin;
    private $cooldown;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->cooldown = [];
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

        if(!($sender instanceof Player)) {
            $sender->sendMessage(TextFormat::RED.'CONSOLE cannot set home. :(');
            return true;
        }

        if(!$sender->hasPermission('leettp.command.home')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        # Check if the player has a cooldown.
        if(isset($this->cooldown[$sender->getName()])) {
            $time = time() - $this->cooldown[$sender->getName()];
            if($time < $this->plugin->getHomeManager()->getCooldown()) {
                $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->cooldown_wait, $this->plugin->getHomeManager()->getCooldown() - $time));
                return true;
            }
        }

        $homes = $this->plugin->getHomeManager()->getHomes($sender->getName());

        $home = null;

        # Check if a name has been given.
        if(count($args) < 1) {
            if($homes !== false and !isset($homes['home'])) {
                $sender->sendMessage($this->plugin->getMessageHandler()->home_name_missing);
                return true;
            }
            $home = 'home';
        }

        if(count($args) >= 1) {
            if($args[0] === '') {
                $home = 'home';
            }
        }

        # Use name if home is still not set.
        if($home === null) {
            $home = $args[0];
        }

        # A home by that name does not exist.
        if(!isset($homes[$home])) {
            $sender->sendMessage($this->plugin->getMessageHandler()->home_not_exists);
            return true;
        }

        # Check if world is loaded.
        if(!$this->plugin->getServer()->isLevelLoaded($homes[$home]['world'])) {
            $sender->sendMessage($this->plugin->getMessageHandler()->world_not_loaded);
            return true;
        }

        $sender->teleport($this->plugin->getHomeManager()->homeToLocation($sender->getName(), $home));

        $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->home_teleported, $home));

        # Attach timestamp for last used /home.
        $this->cooldown[$sender->getName()] = time();

        return true;

    }
}