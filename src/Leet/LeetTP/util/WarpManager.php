<?php

namespace Leet\LeetTP\util;

use Leet\LeetTP\LeetTP;
use Leet\LeetTP\lib\flintstone\Flintstone;
use Leet\LeetTP\lib\flintstone\FlintstoneDB;
use pocketmine\level\Location;

class WarpManager {

    /** @var LeetTP $plugin */
    private $plugin;

    /** @var FlintstoneDB $warps */
    protected $warps;
    protected $public;

    private $cooldown;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->load();
        $this->cooldown = $plugin->getConfig()->getNested('warp.cooldown', 5);
    }

    /**
     * Load warps from disk into memory.
     */
    public function load() {
        $this->warps = Flintstone::load('warps', ['dir' => $this->plugin->getDataFolder(), 'gzip' => true]);
        $this->public = [];

        # Load public warp pointers.
        foreach($this->warps->getAll() as $player => $warps) {
            foreach($warps as $name => $warp) {
                if($warp['public'] === false) continue;
                $this->public[$name] = $player;
            }
        }

    }

    public function disable() {
        Flintstone::unload('warps');
    }

    /**
     * Deletes all warps.
     */
    public function reset() {
        $this->warps->flush();
    }

    /**
     * Gets the specified warp from the specified player
     * or returns null if it is not set.
     *
     * @param $player
     * @param $warp
     * @return null
     */
    public function getWarp($player, $warp) {
        $warp = strtolower($warp);
        return isset($this->warps->get($player)[$warp]) ? $this->warps->get($player)[$warp] : null;
    }

    /**
     * Gets all warps from the specified player.
     *
     * @param $player
     * @return Array
     */
    public function getWarps($player) {
        return $this->warps->get($player);
    }

    /**
     * Deletes the specified warp from the specified player.
     *
     * @param $player
     * @param $warp
     * @return bool|null
     */
    public function deleteWarp($player, $warp) {

        $warp = strtolower($warp);

        $warps = $this->warps->get($player);

        if(!isset($warps[$warp])) return null;

        unset($warps[$warp]);

        $this->warps->set($player, $warps);

        return !in_array($warp, $this->warps->get($player));

    }

    /**
     * Sets a warp for the specified player.
     *
     * @param $player
     * @param Array $warp
     * @return bool|mixed
     */
    public function setWarp($player, $warp) {

        if(!isset($warp['name']) or !isset($warp['world']) or
            !isset($warp['x']) or !isset($warp['y']) or
            !isset($warp['z']) or !isset($warp['yaw']) or
            !isset($warp['pitch']) or !isset($warp['public'])) {

            return false;

        }

        # Check if the specified warp exists already.
        if($this->getWarp($player, $warp['name']) !== null) return null;

        $warps = $this->getWarps($player);

        # Make warps an array if no homes exist.
        if($warps === false) $warps = [];

        $warps[strtolower($warp['name'])] = [
            'world' => $warp['world'],
            'x' => $warp['x'],
            'y' => $warp['y'],
            'z' => $warp['z'],
            'yaw' => $warp['yaw'],
            'pitch' => $warp['pitch'],
            'public' => $warp['public']
        ];

        $this->warps->set($player, $warps);

        return $this->getWarp($player, $warp['name']) !== null;

    }

    /**
     * Converts a warp to a location.
     *
     * @param $player
     * @param $warp
     * @return Location|null
     */
    public function warpToLocation($player, $warp) {

        $w = $this->warps->get($player)[strtolower($warp)];

        if($w === null) return null;

        return new Location(
            $w['x'],
            $w['y'],
            $w['z'],
            $w['yaw'],
            $w['pitch'],
            $this->plugin->getServer()->getLevelByName($w['world'])
        );

    }

    /**
     * Returns the cooldown for using /warp.
     *
     * @return mixed
     */
    public function getCooldown() {
        return $this->cooldown;
    }

    /**
     * Returns the list of pointers to
     * public warps.
     *
     * @return mixed
     */
    public function getPublic() {
        return $this->public;
    }

    /**
     * Adds a public pointer to the
     * warp and returns NULL if
     * name is taken or BOOL
     * depending on the result.
     *
     * @param $player
     * @param $warp
     * @return bool|null
     */
    public function addPublic($player, $warp) {

        $warp = strtolower($warp);

        # Return NULL if the warp already exists.
        if(isset($this->public[$warp])) return null;

        $this->public[$warp] = $player;

        return isset($this->public[$warp]);

    }

    /**
     * Removes a public pointer to
     * the warp and returns NULL if
     * warp is not public and
     * BOOL depending on the result.
     *
     * @param $warp
     * @return bool|null
     */
    public function removePublic($warp) {

        $warp = strtolower($warp);

        # Return NULL if the warp does not exist.
        if(!isset($this->public[$warp])) return null;

        unset($this->public[$warp]);

        return !isset($this->public[$warp]);

    }


}