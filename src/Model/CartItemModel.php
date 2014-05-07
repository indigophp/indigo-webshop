<?php

/*
 * This file is part of the Indigo Webshop package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Webshop\Model;

use Indigo\Cart\Item;
use Orm\Model;

/**
 * Cart Item Model
 *
 * Cart Item Model definition
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CartItemModel extends Model
{
	protected static $_belongs_to = array(
		'cart' => array(
			'key_from' => 'cart_id',
			'model_to' => 'Indigo\\Webshop\\Model\\CartModel',
		)
	);

	protected static $_properties = array(
		'id',
		'cart_id',
		'product_id',
		'quantity',
	);

	protected static $_table_name = 'cart_items';

	public static function forgeFromItem(Item $item)
	{
		return static::forge(array(
			'identifier' => $item->getId(),
			'product_id' => $item->id,
			'quantity'   => $item->quantity,
		));
	}

	public function forgeItem()
	{
		return new Item(array(
			'id'       => (int) $this->product_id,
			'name'     => 'Name',
			'price'    => 1.0,
			'quantity' => (int) $this->quantity,
			'tax'      => 27,
		));
	}
}
