<?php

namespace LUKAY\DailyRewards\commands;

use JsonException;
use LUKAY\DailyRewards\DailyRewards;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;

class RewardCommand extends Command implements PluginOwned {

    public function __construct(string $name) {
        parent::__construct($name);
        $this->setPermission("use.reward");
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     * @throws JsonException
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player || !$this->testPermission($sender)) {
            return;
        }
        if (DailyRewards::getInstance()->hasRedeemed($sender)) {
            $sender->sendMessage("§cYou have already claimed your daily reward. You can claim the next reward in " . DailyRewards::getInstance()->getTimer($sender));
            return;
        }
        DailyRewards::getInstance()->redeem($sender);
        $sender->sendMessage('You have successfully claimed the daily reward.');
    }

    public function getOwningPlugin(): Plugin {
        return DailyRewards::getInstance();
    }
}