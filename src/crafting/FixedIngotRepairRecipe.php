<?php

// CUSTOM POCKETMINE LINESIA

declare(strict_types=1);

namespace pocketmine\crafting;

use pocketmine\item\Durable;
use pocketmine\item\Item;

final class FixedIngotRepairRecipe implements AnvilRecipe{

	public function __construct(
		private RecipeIngredient $base,
		private RecipeIngredient $material
	){}

	public function getResultFor(Item $input, Item $material) : ?AnvilCraftResult{
		if(!$this->base->accepts($input) || !$this->material->accepts($material)){
			return null;
		}
		if(!$input instanceof Durable){
			return null;
		}
		if($input->getDamage() <= 0){
			return null;
		}

		if($material->getCount() < 1){
			return null;
		}
		$sacrificeResult = clone $material;
		$sacrificeResult->setCount($material->getCount() - 1);

		$resultItem = clone $input;
		$resultItem->setDamage(0);

		$xpCost = 5;

		return new AnvilCraftResult($xpCost, $resultItem, $sacrificeResult);
	}
}
