<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\block\Block;
use pocketmine\block\FenceGate;
use pocketmine\block\PressurePlate;
use pocketmine\block\Tripwire;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\sound\EndermanTeleportSound;

class EnderPearl extends Throwable{
	public static function getNetworkTypeId() : string{ return EntityIds::ENDER_PEARL; }

	protected function onHit(ProjectileHitEvent $event) : void{
		$owner = $this->getOwningEntity();
		if($owner !== null){
			//TODO: check end gateways (when they are added)
			//TODO: spawn endermites at origin

			$this->getWorld()->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
			$this->getWorld()->addSound($origin, new EndermanTeleportSound());
			$owner->teleport($target = $event->getRayTraceResult()->getHitVector());
			$this->getWorld()->addSound($target, new EndermanTeleportSound());

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}
	}

	/**
	 * @param Block $block
	 * @param Vector3 $start
	 * @param Vector3 $end
	 * @return ?RayTraceResult
	 */
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end): ?RayTraceResult {
		$player = $this->getOwningEntity();
		if ($player instanceof Player && ($block->hasSameTypeId(VanillaBlocks::INVISIBLE_BEDROCK()) || $block->hasSameTypeId(VanillaBlocks::CONCRETE()))) {
			$this->flagForDespawn();
			return null;
		}

		$blockPosition = $block->getPosition();
		return $block instanceof PressurePlate || $block instanceof Tripwire || $block instanceof FenceGate
			? new RayTraceResult(new AxisAlignedBB($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ(), $blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ()), Facing::UP, $blockPosition)
			: $block->calculateIntercept($start, $end);
	}
}
