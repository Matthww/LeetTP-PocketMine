<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TpDenyCommand implements CommandExecutor {

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

        if(!$sender->hasPermission('leettp.command.tpdeny')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $requests = $this->tpManager->getRequests($sender->getName());

        # Check if the sender has any pending requests.
        if($requests === null or count($requests) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_no_request);
            return true;
        }

        # We need to specify the player if there are more than one request pending.
        if(count($requests) > 1 and count($args) < 1) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_name_missing);
            return true;
        }

        $target = null;

        # Sort alphabetically if there are more than one request.
        if(count($requests) > 1) sort($requests);

        foreach($requests as $player => $request) {

            if(count($args) > 0) if(substr(strtolower($player), 0, strlen($args[0])) !== strtolower($args[0])) continue;

            # We found our request, let's finish up.
            $target = [
                'sender' => $request['sender'],
                'type' => $request['type'],
                'time' => $request['time']
            ];
            break;

        }

        # We didn't find the request.
        if($target === null) {
            $sender->sendMessage($this->plugin->getMessageHandler()->tp_no_request);
            return true;
        }

        # Get rid of the request.
        switch($this->tpManager->removeRequest($sender->getName(), $target['sender'], $target['type'], $target['time'])) {

            # No request by that player, shouldn't be possible to reach.
            case -2:
                break;

            # Provided data did not match the request.
            case -1:
                $this->plugin->getLogger()->alert('Data for teleportion did somehow not match! Dumping data.');
                $this->plugin->getLogger()->alert('Target: '.$sender->getName());
                $this->plugin->getLogger()->alert($target);
                break;

            # No error but entry is STILL set.
            case 0:
                $this->plugin->getLogger()->alert('Failed to remove request from TeleportManager, dumping data.');
                $this->plugin->getLogger()->alert('Target: '.$sender->getName());
                $this->plugin->getLogger()->alert($target);
                break;

            # It succeeded.
            case 1:
                $sender->sendMessage($this->plugin->getMessageHandler()->tp_target_rejected);
                $player = $this->plugin->getServer()->getPlayer($target['sender']);
                if($player !== null) $player->sendMessage(sprintf($this->plugin->getMessageHandler()->tp_rejected, $sender->getName()));
                break;

        }

        return true;

    }
}