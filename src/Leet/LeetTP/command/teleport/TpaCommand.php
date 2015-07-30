<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TpaCommand implements CommandExecutor {

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

        if(!$sender->hasPermission('leettp.command.tpa')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $cooldown = $this->tpManager->getCooldowns($sender->getName());

        # Check if the player has a cooldown.
        if($cooldown !== null AND !$sender->isOp()) {
            $time = time() - $cooldown;
            if($time < $this->tpManager->getCooldown()) {
                $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->cooldown_wait, $this->tpManager->getCooldown() - $time));
                return true;
            }
        }

        if(count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_name_missing);
            return true;
        }

        $target = $this->plugin->getServer()->getPlayer($args[0]);

        # Player is not online or may be auto-completed.
        if($target === null) {

            $players = [];
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

        # Check if the target allows teleportation requests.
        if($this->tpManager->getStatus($target->getName()) === false) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_not_allowed);
            return true;
        }

        # Get all requests for the target.
        $requests = $this->tpManager->getRequests($args[0]);

        # The target has requests waiting.
        if($requests !== null) {

            foreach($requests as $player => $request) {

                # The target already has a request waiting form the target.
                if($sender->getName() === $player) {
                    $sender->sendMessage($this->plugin->getMessageHandler()->tp_request_exists);
                    return true;
                }

            }

        }

        $result = $this->tpManager->addRequest($args[0], $sender->getName(), 1);

        if($result === false) {
            $this->plugin->getServer()->getLogger()->alert('Failed to create a tpa request.');
            return true;
        }

        # Let both parties know that the request has been sent.
        $sender->sendMessage(sprintf($this->plugin->getMessageHandler()->tp_request_sent, $target->getName()));
        $target->sendMessage(sprintf($this->plugin->getMessageHandler()->tp_to_received, $sender->getName(), $sender->getName()));

        # Attach timestamp for last used tp command.
        $this->tpManager->setCooldown($sender->getName(), time());

        return true;

    }
}