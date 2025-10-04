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

namespace pocketmine\crafting;

use linesia\wyze\items\ExtraItems;
use pocketmine\item\Durable;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaArmorMaterials;
use pocketmine\item\VanillaItems;
use pocketmine\world\format\io\GlobalItemDataHandlers;

final class AnvilCraftingManagerDataFiller{
	public static function fillData(CraftingManager $manager) : CraftingManager{
		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ArmorRecipeIngredient(VanillaArmorMaterials::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND())
		));

		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND_AXE())
		));
		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND_HOE())
		));
		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND_PICKAXE())
		));
		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND_SHOVEL())
		));
		$manager->registerAnvilRecipe(new FixedIngotRepairRecipe(
			new ExactRecipeIngredient(VanillaItems::DIAMOND()),
			new ExactRecipeIngredient(VanillaItems::DIAMOND_SWORD())
		));

		foreach(VanillaItems::getAll() as $item){
			if($item instanceof Durable){
				$itemId = GlobalItemDataHandlers::getSerializer()->serializeType($item)->getName();
				$manager->registerAnvilRecipe(new ItemSelfCombineRecipe(
					new MetaWildcardRecipeIngredient($itemId)
				));
			}
		}

		return $manager;
	}
}
