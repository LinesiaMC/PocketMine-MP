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

namespace pocketmine\item;

use pocketmine\block\Liquid;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\world\sound\EndermanTeleportSound;
use function abs;
use function cos;
use function min;
use function mt_rand;
use function round;
use function sin;
use const M_PI;

class ChorusFruit extends Food{

	public function getFoodRestore() : int{
		return 4;
	}

	public function getSaturationRestore() : float{
		return 2.4;
	}

	public function requiresHunger() : bool{
		return false;
	}

	/*public function onConsume(Living $consumer) : void{
		$world = $consumer->getWorld();

		$origin = $consumer->getPosition();
		$minX = $origin->getFloorX() - 8;
		$minY = min($origin->getFloorY(), $consumer->getWorld()->getMaxY()) - 8;
		$minZ = $origin->getFloorZ() - 8;

		$maxX = $minX + 16;
		$maxY = $minY + 16;
		$maxZ = $minZ + 16;

		$worldMinY = $world->getMinY();

		for($attempts = 0; $attempts < 16; ++$attempts){
			$x = mt_rand($minX, $maxX);
			$y = mt_rand($minY, $maxY);
			$z = mt_rand($minZ, $maxZ);

			while($y >= $worldMinY && !$world->getBlockAt($x, $y, $z)->isSolid()){
				$y--;
			}
			if($y < $worldMinY){
				continue;
			}

			$blockUp = $world->getBlockAt($x, $y + 1, $z);
			$blockUp2 = $world->getBlockAt($x, $y + 2, $z);
			if($blockUp->isSolid() || $blockUp instanceof Liquid || $blockUp2->isSolid() || $blockUp2 instanceof Liquid){
				continue;
			}

			//Sounds are broadcasted at both source and destination
			$world->addSound($origin, new EndermanTeleportSound());
			$consumer->teleport($target = new Vector3($x + 0.5, $y + 1, $z + 0.5));
			$world->addSound($target, new EndermanTeleportSound());

			break;
		}
	}*/

	public function onConsume(Living $consumer) : void{
		$world = $consumer->getWorld();

		$origin = $consumer->getPosition();
		$worldMinY = $world->getMinY();
		$worldMaxY = $world->getMaxY();

		$maxRadius = 20;
		$maxVertical = 22;

		// Distribution biaisée (u^2) : forte probabilité de tp proche, faible probabilité de tp loin.
		for($attempts = 0; $attempts < 32; ++$attempts){
			$u = mt_rand(0, 10000) / 10000;
			$r = $maxRadius * $u * $u;
			$angle = (mt_rand(0, 10000) / 10000) * M_PI * 2;

			$uy = mt_rand(0, 10000) / 10000;
			$dy = (int) round($maxVertical * $uy * $uy * (mt_rand(0, 1) === 0 ? -1 : 1));

			$x = $origin->getFloorX() + (int) round(cos($angle) * $r);
			$y = min($origin->getFloorY() + $dy, $worldMaxY);
			$z = $origin->getFloorZ() + (int) round(sin($angle) * $r);

			if($this->tryTeleport($consumer, $origin, $x, $y, $z, $worldMinY)){
				return;
			}
		}

		// Fallback déterministe : recherche en couches concentriques pour garantir une téléportation.
		for($r = 1; $r <= $maxRadius; ++$r){
			for($dx = -$r; $dx <= $r; ++$dx){
				for($dz = -$r; $dz <= $r; ++$dz){
					if(abs($dx) !== $r && abs($dz) !== $r){
						continue; // ne tester que la couche extérieure
					}
					$x = $origin->getFloorX() + $dx;
					$z = $origin->getFloorZ() + $dz;
					$y = min($origin->getFloorY(), $worldMaxY);

					if($this->tryTeleport($consumer, $origin, $x, $y, $z, $worldMinY)){
						return;
					}
				}
			}
		}
	}

	private function tryTeleport(Living $consumer, Vector3 $origin, int $x, int $y, int $z, int $worldMinY) : bool{
		$world = $consumer->getWorld();

		while($y >= $worldMinY && !$world->getBlockAt($x, $y, $z)->isSolid()){
			$y--;
		}
		if($y < $worldMinY){
			return false;
		}

		$blockUp = $world->getBlockAt($x, $y + 1, $z);
		$blockUp2 = $world->getBlockAt($x, $y + 2, $z);
		if($blockUp->isSolid() || $blockUp instanceof Liquid || $blockUp2->isSolid() || $blockUp2 instanceof Liquid){
			return false;
		}

		$world->addSound($origin, new EndermanTeleportSound());
		$consumer->teleport($target = new Vector3($x + 0.5, $y + 1, $z + 0.5));
		$world->addSound($target, new EndermanTeleportSound());
		return true;
	}

	public function getCooldownTicks() : int{
		return 20;
	}

	public function getCooldownTag() : ?string{
		return ItemCooldownTags::CHORUS_FRUIT;
	}
}
