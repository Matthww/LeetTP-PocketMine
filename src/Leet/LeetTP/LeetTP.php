<?php

namespace Leet\LeetTP;

use Leet\LeetTP\command\home\DelHomeCommand;
use Leet\LeetTP\command\home\SetHomeCommand;
use Leet\LeetTP\command\home\HomeCommand;

use Leet\LeetTP\command\teleport\TpAcceptCommand;
use Leet\LeetTP\command\teleport\TpDenyCommand;
use Leet\LeetTP\command\warp\DelWarpCommand;
use Leet\LeetTP\command\warp\ListWarpCommand;
use Leet\LeetTP\command\warp\SetWarpCommand;
use Leet\LeetTP\command\warp\WarpCommand;

use Leet\LeetTP\command\teleport\TpaHereCommand;
use Leet\LeetTP\command\teleport\TpToggleCommand;
use Leet\LeetTP\command\teleport\TpaCommand;

use Leet\LeetTP\listener\TPListener;
use Leet\LeetTP\util\HomeManager;
use Leet\LeetTP\util\MessageHandler;

use Leet\LeetTP\util\TeleportManager;
use Leet\LeetTP\util\WarpManager;
use pocketmine\plugin\PluginBase;

class LeetTP extends PluginBase {

    private static $plugin;

    /** @var MessageHandler */
    protected $messageHandler;
    /** @var HomeManager */
    protected $homeManager;
    /** @var WarpManager */
    protected $warpManager;
    /** @var TeleportManager */
    protected $teleportManager;

    public function onEnable() {

        self::$plugin = $this;

        $this->saveDefaultConfig();

        $this->messageHandler = new MessageHandler($this);
        $this->homeManager = new HomeManager($this);
        $this->warpManager = new WarpManager($this);
        $this->teleportManager = new TeleportManager($this);

        # Register commands.
        $this->getCommand('home')->setExecutor(new HomeCommand($this));
        $this->getCommand('sethome')->setExecutor(new SetHomeCommand($this));
        $this->getCommand('delhome')->setExecutor(new DelHomeCommand($this));

        $this->getCommand('warp')->setExecutor(new WarpCommand($this));
        $this->getCommand('setwarp')->setExecutor(new SetWarpCommand($this));
        $this->getCommand('delwarp')->setExecutor(new DelWarpCommand($this));
        $this->getCommand('listwarp')->setExecutor(new ListWarpCommand($this));

        $this->getCommand('tpa')->setExecutor(new TpaCommand($this));
        $this->getCommand('tpahere')->setExecutor(new TpaHereCommand($this));
        $this->getCommand('tptoggle')->setExecutor(new TpToggleCommand($this));
        $this->getCommand('tpaccept')->setExecutor(new TpAcceptCommand($this));
        $this->getCommand('tpdeny')->setExecutor(new TpDenyCommand($this));

        # TODO: Finish the commands below.
        #$this->getCommand('tpo')->setExecutor();
        #$this->getCommand('tpohere')->setExecutor();

        # Register event listeners.
        $this->getServer()->getPluginManager()->registerEvents(new TPListener($this), $this);

    }

    public function onDisable() {

        $this->homeManager->disable();
        $this->warpManager->disable();

        # Cleanup in case of a reload.
        unset($this->messageHandler);
        unset($this->homeManager);
        unset($this->warpManager);
        unset($this->teleportManager);

        self::$plugin = null;
    }

    public static function getPlugin() {
        return self::$plugin;
    }

    public function getMessageHandler() {
        return $this->messageHandler;
    }

    public function getHomeManager() {
        return $this->homeManager;
    }

    public function getWarpManager() {
        return $this->warpManager;
    }

    public function getTeleportManager() {
        return $this->teleportManager;
    }

}