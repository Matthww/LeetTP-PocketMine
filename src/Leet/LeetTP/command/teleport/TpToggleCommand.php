<?php

namespace Leet\LeetTP\command\teleport;

use Leet\LeetTP\LeetTP;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TpToggleCommand implements CommandExecutor {

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

        if(!$sender->hasPermission('leettp.command.tptoggle')) {
            $sender->sendMessage($this->plugin->getMessageHandler()->no_permission);
            return true;
        }

        $status = !$this->tpManager->getStatus($sender->getName());

        $this->tpManager->setStatus($sender->getName(), $status);

        $sender->sendMessage(
            $status ? sprintf($this->plugin->getMessageHandler()->tp_status_changed, 'now') : sprintf($this->plugin->getMessageHandler()->tp_status_changed, 'no longer')
        );

        return true;

    }
}