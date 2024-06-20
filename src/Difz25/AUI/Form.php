<?php

namespace Difz25\AUI;

use pocketmine\block\Anvil;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Tool;
use pocketmine\item\Armor;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use onebone\economyapi\EconomyAPI;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class Form extends PluginBase implements Listener
{

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onPlayerInteract(PlayerInteractEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        if($block instanceof Anvil) {
            $event->cancel();
            $this->openUIForm($player);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if (strtolower($command->getName()) === "anvil") {
            if ($sender instanceof Player) {
                $this->openUIForm($sender);
                return true;
            } else {
                $sender->sendMessage(TF::RED . "Please use this command in-game.");
                return false;
            }
        }
        return true;
    }

    public function openUIForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                return true;
            }
            switch ($data) {
                case 0:
                    $this->openAnvilMenu($player);
                    break;
                case 1:
                    $this->openLoreForm($player);
                    break;
                case 2:
                    $this->openRenameForm($player);
                    break;
            }
            return true;
        });
        $form->setTitle(TF::GREEN . "AnvilUI");
        $form->setContent(TF::GREEN . "AnvilUI");
        $form->addButton(TF::GOLD . "Repair", 0, "https://images.app.goo.gl/gB5heLrVq6HirDkr7");
        $form->addButton(TF::GOLD . "Lore", 0, "https://images.app.goo.gl/gB5heLrVq6HirDkr7");
        $form->addButton(TF::GOLD . "Rename", 0, "https://images.app.goo.gl/gB5heLrVq6HirDkr7");
        $player->sendForm($form);
    }

    public function openAnvilMenu(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, $data = null) {
            if ($data === null) {
                return true;
            }
            switch ($data) {
                case 0:
                    $eco = EconomyAPI::getInstance();
                    $money = $eco->myMoney($player);
                    if ($money > 750000) {
                        $items = $player->getInventory()->getItemInHand();
                        if ($items instanceof Tool || $items instanceof Armor) {
                            $eco->reduceMoney($player, 750000);
                            $items->setDamage(0);
                            $player->getInventory()->setItemInHand($items);
                            $player->sendMessage("Item has be repaired");
                        }    
                    } else {
                        $player->sendMessage(TF::GREEN . "You does not have enough money");
                    }
                    break;
                case 1:
                    $xp = $player->getXpManager();
                    $pexp = $xp->getXpLevel();
                    if ($pexp > 25) {
                        $items = $player->getInventory()->getItemInHand();
                        if ($items instanceof Tool || $items instanceof Armor) {
                            $xp->subtractXpLevels(25);
                            $items->setDamage(0);
                            $player->getInventory()->setItemInHand($items);
                            $player->sendMessage("Item has be repaired");
                        }
                    } else {
                        $player->sendMessage(TF::GREEN . "You does not have enough level");
                    }
                    break;
            }
            return true;
        });
        $form->setTitle(TF::GREEN . "AnvilUI");
        $form->setContent("Select the payments");
        $form->addButton("Money Repair", 0, "https://images.app.goo.gl/TbYt4eg7Fp9rJCiN9");
        $form->addButton("LeveL Repair", 0, "https://images.app.goo.gl/TbYt4eg7Fp9rJCiN9");
        $player->sendForm($form);
    }

    public function openRenameForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, array $data = null) {
            if ($data === null) {
                return true;
            }
            $rename = $data[0];
            $eco = EconomyAPI::getInstance();
            $money = $eco->myMoney($player);
            $cost = 300000;
            if ($money >= $cost) {
                $item = $player->getInventory()->getItemInHand();
                if ($item instanceof Tool || $item instanceof Armor) {
                    $eco->reduceMoney($player, 300000);
                    $item->setCustomName(str_replace(["{line}"], ["\n"], TF::colorize($rename)));
                    $player->getInventory()->setItemInHand($item);
                    $player->sendMessage("Item has be renamed");
                }
            } else {
                $player->sendMessage(TF::GREEN . "Invalid item");
            }
            return true;
        });
        $form->setTitle(TF::GREEN . "AnvilUI");
        $form->addInput("Rename:");
        $player->sendForm($form);
    }

    public function openLoreForm(Player $player): void
    {
        $form = new CustomForm(function (Player $player, $data = null) {
            if ($data === null) {
                return true;
            }
            $lore = $data[0];
            $money = EconomyAPI::getInstance();
            $eco = $money->myMoney($player);
            $price = 250000;
            if ($eco >= $price) {
                $money->reduceMoney($player, $price);
                $item = $player->getInventory()->getItemInHand();
                if ($item instanceof Tool || $item instanceof Armor) {
                    $money->reduceMoney($player, 250000);
                    $loreText = str_replace(["{line}"], ["\n"], TF::colorize($lore));
                    $loreArray = explode("\n", $loreText);
                    $item->setLore($loreArray);
                    $player->getInventory()->setItemInHand($item);
                    $player->sendMessage("Item has be lored");
                }
            } else {
                $player->sendMessage(TF::GREEN . "Invalid item");
            }
            return true;
        });
        $form->setTitle(TF::GREEN . "AnvilUI");
        $form->addInput("Lore:");
        $player->sendForm($form);
    }
}