<?php

namespace Leet\LeetTP\util;

use Leet\LeetTP\LeetTP;

class TeleportManager {

    private $plugin;

    private $cooldown;
    private $cooldowns;

    private $requests;
    private $status;

    public function __construct(LeetTP $plugin) {
        $this->plugin = $plugin;
        $this->cooldown = $plugin->getConfig()->getNested('tpa.cooldown', 30);
        $this->requests = [];
        $this->status = [];
    }

    /**
     * Gets requests for the specified player
     * and returns an Array or NULL.
     * @param $player
     * @return Array|null
     */
    public function getRequests($player) {
        return isset($this->requests[strtolower($player)]) ? $this->requests[strtolower($player)] : null;
    }

    public function addRequest($target, $sender, $type) {

        $this->requests[strtolower($target)][$sender] = [
            'sender' => $sender,
            'type' => $type,
            'time' => time()
        ];

        if(!isset($this->requests[strtolower($target)]) or !isset($this->requests[strtolower($target)][$sender])) return false;

        return $this->requests[strtolower($target)][$sender]['sender'] === $sender;

    }

    /**
     * Removes a request and returns a int
     * specifying the status of the action.
     *
     * @param $target
     * @param $sender
     * @param $type
     * @param $time
     * @return int
     */
    public function removeRequest($target, $sender, $type, $time) {

        if(!isset($this->requests[strtolower($target)][$sender])) return -2;

        $request = $this->requests[strtolower($target)][$sender];

        if($type !== $request['type'] or $time !== $request['time']) return -1;

        unset($this->requests[strtolower($target)][$sender]);

        return !isset($this->requests[strtolower($target)][$sender]) ? 1 : 0;

    }

    /**
     * Returns the cooldown as an integer.
     *
     * @return mixed
     */
    public function getCooldown() {
        return $this->cooldown;
    }

    /**
     * Returns the cooldown for using /tpa and /tpahere.
     *
     * @param $player
     * @return mixed
     */
    public function getCooldowns($player) {
        return isset($this->cooldown[$player]) ? $this->cooldown[$player] : null;
    }

    /**
     * Sets the cooldown time to the specified timestamp
     * for the specified player.
     *
     * @param $player
     * @param $time
     */
    public function setCooldown($player, $time) {
        $this->cooldowns[$player] = $time;
    }

    /**
     * Gets the status of a specified player or
     * returns true.
     * True means that teleportation is allowed.
     *
     * @param $player
     * @return bool
     */
    public function getStatus($player) {
        $player = strtolower($player);
        return (isset($this->status[$player])) ? $this->status[$player] : true;
    }

    /**
     * Sets the status of a specified player.
     *
     * @param $player
     * @param $status
     */
    public function setStatus($player, $status) {
        $player = strtolower($player);
        $this->status[$player] = $status;
    }

}