<?php

namespace LobbySystem;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\math\Vector3;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\entity\Effect;

class Main extends PluginBase implements Listener {

    private $teleporting = [];

    public function onEnable() {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->formatMessage("Este comando solo puede ser usado dentro del juego."));
            return true;
        }

        switch ($command->getName()) {
            case "lobby":
                $this->startTeleportProcess($sender);
                return true;

            case "hub":
                $sender->setHealth(0);
                $sender->sendMessage($this->formatMessage($this->getConfig()->get("hub_message", "Has sido eliminado y enviado al lobby")));
                return true;
        }

        return false;
    }

    public function onMove(PlayerMoveEvent $event) {
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();

        if (isset($this->teleporting[$player->getName()]) &&
            ($from->x != $to->x || $from->y != $to->y || $from->z != $to->z)) {
            $this->cancelTeleport($player, $this->formatMessage($this->getConfig()->get("teleport_cancelled_message", "Teletransporte cancelado porque te moviste.")));
        }
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if (isset($this->teleporting[$player->getName()])) {
            unset($this->teleporting[$player->getName()]);
        }
    }

    private function startTeleportProcess(Player $player) {
        $time = (int)$this->getConfig()->get("teleport_time", 5);
        if ($time < 1) $time = 1;

        $effect = Effect::getEffect(Effect::BLINDNESS);
        $effect->setDuration(999999);
        $effect->setAmplifier(1);
        $effect->setVisible(false);
        $player->addEffect($effect);

        $noMoveMsg = $this->getConfig()->get("dont_move_message", "No te muevas para poder llevarte al Lobby correctamente.");
        $player->sendMessage($this->formatMessage($noMoveMsg));

        $task = new TeleportTask($this, $player, $time);
        $taskId = $this->getServer()->getScheduler()->scheduleRepeatingTask($task, 20)->getTaskId();
        $this->teleporting[$player->getName()] = $taskId;
    }

    public function finishTeleport(Player $player) {
        unset($this->teleporting[$player->getName()]);

        $level = $this->getServer()->getDefaultLevel();
        $player->teleport($level->getSafeSpawn());

        $successMsg = $this->getConfig()->get("teleport_success_message", "Has sido teletransportado al Lobby.");
        $player->sendPopup($this->formatMessage($successMsg));

        $this->getServer()->getScheduler()->scheduleDelayedTask(new FadeOutBlindnessTask($this, $player), 10);
    }

    public function cancelTeleport(Player $player, $message) {
        if (isset($this->teleporting[$player->getName()])) {
            $this->getServer()->getScheduler()->cancelTask($this->teleporting[$player->getName()]);
            unset($this->teleporting[$player->getName()]);
            $player->removeEffect(Effect::BLINDNESS);
            $player->sendMessage($message);
        }
    }

    public function updateTeleportPopup(Player $player, int $timeLeft) {
        $popupTemplate = $this->getConfig()->get("teleport_popup_message", "§fLlevándote al Lobby... §e{time}s");
        $popup = str_replace("{time}", $timeLeft, $popupTemplate);
        $player->sendPopup($popup);
    }

    private function formatMessage($message) {
        $prefix = $this->getConfig()->get("prefix", "§l§fLight§fTP §l§8| §r§f");
        return " \n" . $prefix . $message . "\n \n";
    }
}

class TeleportTask extends PluginTask {
    private $plugin;
    private $player;
    private $timeLeft;

    public function __construct(Main $plugin, Player $player, int $time) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->player = $player;
        $this->timeLeft = $time;
    }

    public function onRun($currentTick) {
        if (!$this->player->isOnline()) {
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        if ($this->timeLeft <= 0) {
            $this->plugin->finishTeleport($this->player);
            $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
            return;
        }

        $this->player->getLevel()->addSound(new BlazeShootSound(new Vector3($this->player->getX(), $this->player->getY(), $this->player->getZ())));
        $this->plugin->updateTeleportPopup($this->player, $this->timeLeft);
        $this->timeLeft--;
    }
}

class FadeOutBlindnessTask extends PluginTask {
    private $player;
    private $step = 0;

    public function __construct(Main $plugin, Player $player) {
        parent::__construct($plugin);
        $this->player = $player;
    }

    public function onRun($currentTick) {
        if (!$this->player->isOnline()) {
            return;
        }

        switch ($this->step) {
            case 0:
                $this->player->removeEffect(Effect::BLINDNESS);
                $effect = Effect::getEffect(Effect::BLINDNESS);
                $effect->setDuration(20);
                $effect->setAmplifier(0);
                $effect->setVisible(false);
                $this->player->addEffect($effect);
                $this->getOwner()->getServer()->getScheduler()->scheduleDelayedTask($this, 10);
                $this->step++;
                break;

            case 1:
                $this->player->removeEffect(Effect::BLINDNESS);
                break;
        }
    }
}