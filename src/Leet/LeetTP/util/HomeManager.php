<?php

namespace Leet\LeetTP\util;

use Leet\LeetTP\LeetTP;
use Leet\LeetTP\lib\flintstone\Flintstone;
use Leet\LeetTP\lib\flintstone\FlintstoneDB;
use pocketmine\level\Location;

class HomeManager {

    # TODO: Ensure that STRTOLOWER is called so case-sensitivity is not an issue.

    /** @var LeetTP $plugin */
    private $plugin;

    /** @var FlintstoneDB $homes */
    protected $homes;

    private $cooldown;

    public $bed_set_home;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->load();
        $this->cooldown = $plugin->getConfig()->getNested('home.cooldown', 5);
        $this->bed_set_home = $plugin->getConfig()->getNested('home.set-by-bed', true);
    }

    /**
     * Load homes from disk into memory.
     */
    public function load() {
        $this->homes = Flintstone::load('homes', ['dir' => $this->plugin->getDataFolder(), 'gzip' => true]);
    }

    public function disable() {
        Flintstone::unload('homes');
    }

    /**
     * Deletes all homes.
     */
    public function reset() {
        $this->homes->flush();
    }

    /**
     * Gets the specified home from the specified player
     * or returns null if it is not set.
     *
     * @param $player
     * @param $home
     * @return null
     */
    public function getHome($player, $home) {
        return isset($this->homes->get($player)[$home]) ? $this->homes->get($player)[$home] : null;
    }

    /**
     * Gets all homes from the specified player.
     *
     * @param $player
     * @return mixed
     */
    public function getHomes($player) {
        return $this->homes->get($player);
    }

    /**
     * Deletes the specified home from the specified player.
     *
     * @param $player
     * @param $home
     * @return bool|null
     */
    public function deleteHome($player, $home) {

        $homes = $this->homes->get($player);

        if(!isset($homes[$home])) return null;

        unset($homes[$home]);

        $this->homes->set($player, $homes);

        return !in_array($home, $this->homes->get($player));

    }

    /**
     * Sets a home for the specified player.
     *
     * @param $player
     * @param Array $home
     * @return bool|mixed
     */
    public function setHome($player, $home) {

        if(!isset($home['name']) or !isset($home['world']) or
            !isset($home['x']) or !isset($home['y']) or
            !isset($home['z']) or !isset($home['yaw']) or
            !isset($home['pitch'])) {

            return false;

        }

        # Check if the specified home exists already.
        if($this->getHome($player, $home['name']) !== null) return null;

        $homes = $this->getHomes($player);

        # Make homes an array if no homes exist.
        if($homes === false) $homes = [];

        $homes[$home['name']] = [
            'world' => $home['world'],
            'x' => $home['x'],
            'y' => $home['y'],
            'z' => $home['z'],
            'yaw' => $home['yaw'],
            'pitch' => $home['pitch']
        ];

        $this->homes->set($player, $homes);

        return $this->getHome($player, $home['name']) !== null;

    }

    /**
     * Converts a home to a location.
     *
     * @param $player
     * @param $home
     * @return Location
     */
    public function homeToLocation($player, $home) {

        $h = $this->homes->get($player)[$home];

        return new Location(
            $h['x'],
            $h['y'],
            $h['z'],
            $h['yaw'],
            $h['pitch'],
            $this->plugin->getServer()->getLevelByName($h['world'])
        );

    }

    /**
     * Returns the cooldown for using /home.
     *
     * @return mixed
     */
    public function getCooldown() {
        return $this->cooldown;
    }

}