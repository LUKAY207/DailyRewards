<?php

namespace LUKAY\DailyRewards;

use JsonException;
use LUKAY\DailyRewards\commands\RewardCommand;
use pocketmine\item\StringToItemParser;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;

class DailyRewards extends PluginBase {
    use SingletonTrait;

    private Config $config;
    private Config $data;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        $this->saveResource("config.yml");
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        $this->data = new Config($this->getDataFolder() . "data.json", Config::JSON);
        $this->getServer()->getCommandMap()->register("DailyRewards", new RewardCommand("reward"));
    }

    public function getConfig(): Config {
        return $this->config;
    }

    /**
     * @throws JsonException
     */
    public function redeem(Player $player): void {
        $this->data->set($player->getName(), [true, time()]);
        $this->data->save();
        $this->data->reload();

        foreach (array_keys($this->getConfig()->get("items")) as $itemName) {
            $player->getInventory()->addItem(StringToItemParser::getInstance()->parse($itemName)->setCount($this->getConfig()->getNested("items." . $itemName)));
        }
    }

    /**
     * @throws JsonException
     */
    public function hasRedeemed(Player $player): bool {
        if (!$this->data->exists($player->getName())) {
            $this->data->set($player->getName(), [false, time()]);
            $this->data->save();
            $this->data->reload();
        }
        $array = $this->data->get($player->getName());
        if ((int)$array[1] + (int)$this->getConfig()->get("waiting_period") <= time()) {
            $this->data->set($player->getName(), [false, time()]);
            $this->data->save();
            $this->data->reload();
        }
        return $array[0];
    }

    public function getTimer(Player $player): string {
        $array = $this->data->get($player->getName());
        return date("H:i:s", ($array[1] - (time() - $this->getConfig()->get("waiting_period"))));
    }
}