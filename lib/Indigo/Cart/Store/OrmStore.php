<?php

/*
 * This file is part of the Indigo Webshop package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Cart\Store;

use Indigo\Cart\Cart;
use Indigo\Cart\Item;
use Indigo\Webshop\Model\CartModel;
use Indigo\Webshop\Model\CartItemModel;

/**
 * ORM Store
 *
 * Save cart content to database
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class OrmStore
{
	public function load(Cart $cart)
	{
		$model = CartModel::query()
			->related('items')
			// ->related('items.product')
			->where('identifier', $cart->getId())
			->get_one();

		if ($model)
		{
			foreach ($model->items as $item)
			{
				$cart->add($item->forgeItem());
			}
		}
	}

	public function save(Cart $cart)
	{
		$model = CartModel::query()
			->related('items')
			->where('identifier', $cart->getId())
			->get_one();

		$items = $cart->getContents();

		if ($model)
		{
			foreach ($model->items as $item)
			{
				$identifier = $item->forgeItem()->getId();

				if (array_key_exists($identifier, $items))
				{
					$item->quantity = $items[$identifier]->quantity;
					unset($items[$identifier]);
				}
				else
				{
					unset($model->items[$item->id]);
					$item->delete();
				}
			}
		}
		else
		{
			$model = CartModel::forgeFromCart($cart);
		}

		foreach ($items as $id => $item)
		{
			$model->items[] = CartItemModel::forgeFromItem($item);
		}

		return $model->save(true);
	}

	public function delete(Cart $cart)
	{
		$model = CartModel::query()
			->where('identifier', $cart->getId())
			->get_one();

		if ($model)
		{
			return (bool) $model->delete();
		}

		return false;
	}
}
