<?php

namespace Leet\LeetTP\util;

use Leet\LeetTP\LeetTP;
use pocketmine\utils\TextFormat;

class MessageHandler {

    private $plugin;
    private static $colors;
    private $version;

    public $no_permission;

    public $home_name_missing;
    public $home_exists;
    public $home_not_exists;
    public $home_not_deleted;
    public $home_teleported;
    public $home_set;
    public $home_deleted;

    public $warp_set;
    public $warp_name_missing;
    public $warp_exists;
    public $warp_not_exists;
    public $warp_deleted;
    public $warp_not_deleted;
    public $warp_private_exists;
    public $warp_teleported;
    public $warp_sign_created;

    public $cooldown_wait;

    public $world_not_loaded;

    public $tp_name_missing;
    public $tp_request_exists;
    public $tp_not_allowed;
    public $tp_request_sent;
    public $tp_to_received;
    public $tp_status_changed;
    public $tp_no_request;
    public $tp_not_exists;
    public $tp_not_online;
    public $tp_tpa_success;
    public $tp_tpahere_success;
    public $tp_tpa_target_success;
    public $tp_tpahere_target_success;
    public $tp_target_rejected;
    public $tp_rejected;

    public $back_empty;
    public $back_teleported;

    public $spawn_set;
    public $spawn_teleported;

    public function __construct(LeetTP $plugin) {

        self::$colors = (new \ReflectionClass(TextFormat::class))->getConstants();
        $this->plugin = $plugin;

        $this->version = $plugin->getConfig()->get('version');

        $this->no_permission = static::parseColors($plugin->getConfig()->getNested('messages.error.no-permission', '%red%You don\'t have permission to do that.'));

        $this->cooldown_wait = static::parseColors($plugin->getConfig()->getNested('messages.error.cooldown-wait', '%red%You need to wait %s second(s) before doing that.'));

        $this->home_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.home-exists', '%red%A home with that name (%s) already exists.'));
        $this->home_not_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.home-not-exists', '%red%You don\'t have a home by that name.'));
        $this->home_not_deleted = static::parseColors($plugin->getConfig()->getNested('messages.error.home-not-deleted', '%red%Failed to delete that home.'));
        $this->home_name_missing = static::parseColors($plugin->getConfig()->getNested('messages.error.home-name-missing', '%red%You have to specify a name for your home.'));
        $this->home_set = static::parseColors($plugin->getConfig()->getNested('messages.success.home-set', '%green%Home set! Use /home <name> to return to it.'));
        $this->home_deleted = static::parseColors($plugin->getConfig()->getNested('messages.success.home-deleted', '%green%Home successfully deleted!'));
        $this->home_teleported = static::parseColors($plugin->getConfig()->getNested('messages.success.home-teleported', '%green%Welcome back to %s'));

        $this->warp_set = static::parseColors($plugin->getConfig()->getNested('messages.success.warp-set', '%green%Warp set! Use /warp <name> to warp to it.'));
        $this->warp_name_missing = static::parseColors($plugin->getConfig()->getNested('messages.error.warp-name-missing', '%red%Warp name is missing.'));
        $this->warp_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.warp-exists', '%red%A warp with that name already exists.'));
        $this->warp_not_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.warp-not-exists', '%red%Found no warp with that name.'));
        $this->warp_deleted = static::parseColors($plugin->getConfig()->getNested('messages.success.warp-deleted', '%green%Warp successfully deleted!'));
        $this->warp_not_deleted = static::parseColors($plugin->getConfig()->getNested('messages.error.warp-not-deleted', '%red%Failed to delete that warp.'));
        $this->warp_private_exists = static::parseColors($plugin->getConfig()->getNested('messages.notify.warp-private-exists', '%yellow%A private warp with the same name exists, add \'-p\' to warp to it.'));
        $this->warp_teleported = static::parseColors($plugin->getConfig()->getNested('messages.success.warp-teleported', '%green%Warped to %s'));
        $this->warp_sign_created = static::parseColors($plugin->getConfig()->getNested('messages.success.warp-sign-created', '%green%Warp sign created.%green%'));

        $this->world_not_loaded = static::parseColors($plugin->getConfig()->getNested('messages.error.world-not-loaded', '%red%Target world NOT loaded! Prevented a server crash.'));

        $this->tp_name_missing = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-name-missing', '%red%You need to specify a target player.'));
        $this->tp_request_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-request-exists', '%red%Your target already has a teleportation request from you.'));
        $this->tp_not_allowed = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-not-allowed', '%red%Your target does not allow teleportation requests.'));
        $this->tp_request_sent = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-request-sent', '%green%Sent teleportation request to %s.'));
        $this->tp_to_received = static::parseColors($plugin->getConfig()->getNested('messages.notify.tp-to-received', '%yellow%%s has asked to teleport to you, type \'/tpaccept %s\''));
        $this->tp_status_changed = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-status-changed', '%green%You %s allow teleportation requests.'));
        $this->tp_no_request = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-no-request', '%red%You do not have any teleportation requests.'));
        $this->tp_not_exists = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-not-exists', '%red%You have no requests by that player.'));
        $this->tp_not_online = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-not-online', '%red%You can only accept requests from online players.'));
        $this->tp_tpa_success = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-tpa-success', '%green%You teleported to %s.'));
        $this->tp_tpahere_success = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-tpahere-success', '%green%You teleported %s to you.'));
        $this->tp_tpa_target_success = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-tpa-target-success', '%green%%s were teleported to you.'));
        $this->tp_tpahere_target_success = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-tpahere-target-success', '%green%You were teleported to %s.'));
        $this->tp_target_rejected = static::parseColors($plugin->getConfig()->getNested('messages.success.tp-target-rejected', '%green%Teleportation request has been rejected.'));
        $this->tp_rejected = static::parseColors($plugin->getConfig()->getNested('messages.error.tp-rejected', '%red%Your teleportation request made to %s has been rejected.'));

        $this->back_empty = static::parseColors($plugin->getConfig()->getNested('messages.error.back-empty', '%red%You have to die before using /back.'));
        $this->back_teleported = static::parseColors($plugin->getConfig()->getNested('messages.success.back-teleported', '%green%You returned to your last death location.'));

        $this->spawn_set = static::parseColors($plugin->getConfig()->getNested('messages.success.spawn-set', '%green%Spawn location set.'));
        $this->spawn_teleported = static::parseColors($plugin->getConfig()->getNested('messages.success.spawn-teleported', '%green%Teleported to spawn.'));

        # Migrate config.
        if($this->version == 1) {
            $this->plugin->getLogger()->info('Migrating configuration to version 2.');
            $this->plugin->getConfig()->setNested('messages.notify.warp-private-exists', '%yellow%A private warp with the same name exists, add \'-p\' to warp to it.');
            # Get the messages array so we can remove content from it.
            $messages = $this->plugin->getConfig()->get('messages');
            unset($messages['notify']['warp-public-exists']);
            # All done editing, now put the array back into the file.
            $this->plugin->getConfig()->set('messages', $messages);
            # Assign message so no reload is needed.
            $this->warp_private_exists = static::parseColors($plugin->getConfig()->getNested('messages.notify.warp-private-exists', '%yellow%A private warp with the same name exists, add \'-p\' to warp to it.'));


            # Up version number.
            $this->plugin->getConfig()->set('version', 2);
            $this->plugin->saveConfig();
            $this->plugin->reloadConfig();
        }
    }

    /**
     * Iterates the color array and replaces the color codes from the provided String.
     * @param $message
     * @return String
     */
    private static function parseColors($message) {
        $msg = $message;
        foreach(self::$colors as $color => $value) {
            $key = '%' .strtolower($color). '%';
            if(strpos($msg, $key) !== false) {
                $msg = str_replace($key, $value, $msg);
            }
        }
        return $msg;
    }
}