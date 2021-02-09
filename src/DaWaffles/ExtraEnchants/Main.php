<?php

declare(strict_types=1);

namespace DaWaffles\ExtraEnchants;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\entity\Human;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase implements Listener{
	
	/** @var array */
	private $ids = [
		Block::COAL_ORE,
		Block::DIAMOND_ORE,
		Block::EMERALD_ORE,
		Block::REDSTONE_ORE,
		Block::LAPIS_ORE,
		Block::NETHER_QUARTZ_ORE
	];
	
	public function onLoad(){
    $this->getLogger()->info(TextFormat::GREEN . "Loading Plugin!");
    }

  public function onDisable(){
    $this->getLogger()->info(TextFormat::RED . "Plugin Disabled!");
    }
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		Enchantment::registerEnchantment(new Enchantment(Enchantment::LOOTING, 'Looting', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ALL, Enchantment::SLOT_NONE, 3));
		Enchantment::registerEnchantment(new Enchantment(Enchantment::FORTUNE, 'Fortune', Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ALL, Enchantment::SLOT_NONE, 3));
		}
	/**
	 * @param BlockBreakEvent $event
	 */
		public function onBlockBreak(BlockBreakEvent $event) : void{
		if($event->isCancelled()){
			return;
		}
		$item = $event->getItem();
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!$player instanceof Player){
			return;
		}
		$blockId = $block->getId();
		if(($level = $item->getEnchantmentLevel(Enchantment::FORTUNE)) > 0){
			if(!in_array($blockId, $this->ids)){
				return;
			}
			$id = 0;
			switch($blockId){
				case Block::COAL_ORE:
					$id = Item::COAL;
					break;
				case Block::DIAMOND_ORE:
					$id = Item::DIAMOND;
					break;
				case Block::EMERALD_ORE:
					$id = Item::EMERALD;
					break;
				case Block::REDSTONE_ORE:
					$id = Item::REDSTONE;
					break;
				case Block::LAPIS_ORE:
					$id = Item::DYE;
					break;
				case Block::NETHER_QUARTZ_ORE:
					$id = Item::NETHER_QUARTZ;
					break;
			}
			$item = Item::get($id, 0, 1 + mt_rand(0, $level + 2));
			if($item->getId() === Item::DYE){
				$item->setDamage(4);
				$item->setCount(2 + mt_rand(0, $level + 2) * 2);
			}
			$drops = [$item];
			$event->setDrops($drops);
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 */
	public function onEntityDamage(EntityDamageEvent $event) : void{
		if($event->isCancelled()){
			return;
		}
		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if(!$damager instanceof Player){
				return;
			}
			$item = $damager->getInventory()->getItemInHand();
			if(($level = $item->getEnchantmentLevel(Enchantment::LOOTING)) <= 0){
				return;
			}
			/** @var Living $entity */
			$entity = $event->getEntity();
			if($entity instanceof Player){
				return;
			}
			if($event->getFinalDamage() >= $entity->getHealth()){
				foreach($entity->getDrops() as $drop){
					$drop->setCount($drop->getCount() + mt_rand(3, $level));
					$entity->getLevel()->dropItem($entity, $drop);
				}
			}
		}
	}

}
