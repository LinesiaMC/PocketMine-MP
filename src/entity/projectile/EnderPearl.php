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
use pocketmine\math\Axis;
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

			$target = $event->getRayTraceResult()->getHitVector();

			if($this->wouldTeleportThroughFenceGate($owner->getPosition(), $target)){
				return;
			}

			$world = $this->getWorld();
			$world->addParticle($origin = $owner->getPosition(), new EndermanTeleportParticle());
			$world->addSound($origin, new EndermanTeleportSound());
			$owner->teleport($target);
			$world->addSound($target, new EndermanTeleportSound());

			$owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
		}
	}

	private function wouldTeleportThroughFenceGate(Vector3 $ownerPos, Vector3 $target) : bool{
		$halfWidth = 0.3;
		$height = 1.8;

		$world = $this->getWorld();
		$minX = (int) floor(min($ownerPos->x, $target->x) - $halfWidth);
		$minY = (int) floor(min($ownerPos->y, $target->y));
		$minZ = (int) floor(min($ownerPos->z, $target->z) - $halfWidth);
		$maxX = (int) floor(max($ownerPos->x, $target->x) + $halfWidth);
		$maxY = (int) floor(max($ownerPos->y, $target->y) + $height);
		$maxZ = (int) floor(max($ownerPos->z, $target->z) + $halfWidth);

		for($x = $minX; $x <= $maxX; $x++){
			for($y = $minY; $y <= $maxY; $y++){
				for($z = $minZ; $z <= $maxZ; $z++){
					$block = $world->getBlockAt($x, $y, $z);
					if(!($block instanceof FenceGate) || $block->isOpen()){
						continue;
					}

					$blockingAxis = Facing::axis($block->getFacing());
					if($blockingAxis === Axis::X){
						$gateCenter = $x + 0.5;
						$ownerCoord = $ownerPos->x;
						$targetCoord = $target->x;
					}elseif($blockingAxis === Axis::Z){
						$gateCenter = $z + 0.5;
						$ownerCoord = $ownerPos->z;
						$targetCoord = $target->z;
					}else{
						continue;
					}

					// Check if owner and target are on opposite sides of the gate
					if(($ownerCoord - $gateCenter) * ($targetCoord - $gateCenter) < 0){
						return true;
					}

					// Check if target is inside the gate block on the blocking axis
					// (player body would overlap the thin gate collision box)
					$blockCoord = ($blockingAxis === Axis::X) ? $x : $z;
					$gateMaxY = $y + 1.5; // fence gates are 1.5 blocks tall
					if($targetCoord > $blockCoord && $targetCoord < $blockCoord + 1
						&& $target->y < $gateMaxY && $target->y + $height > $y){
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @param Block $block
	 * @param Vector3 $start
	 * @param Vector3 $end
	 * @return ?RayTraceResult
	 */
	protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end): ?RayTraceResult {
		$player = $this->getOwningEntity();
		if ($player instanceof Player && $block->hasSameTypeId(VanillaBlocks::INVISIBLE_BEDROCK()) && $block->hasSameTypeId(VanillaBlocks::SPONGE())) {
			$this->flagForDespawn();
			return null;
		}

		$blockPosition = $block->getPosition();
		if($block instanceof FenceGate && !$block->isOpen()){
			$fullBox = AxisAlignedBB::one()->offset($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ());
			return $fullBox->calculateIntercept($start, $end);
		}
		return $block instanceof PressurePlate || $block instanceof Tripwire
			? new RayTraceResult(new AxisAlignedBB($blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ(), $blockPosition->getX(), $blockPosition->getY(), $blockPosition->getZ()), Facing::UP, $blockPosition)
			: $block->calculateIntercept($start, $end);
	}
}
