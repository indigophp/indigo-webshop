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

use Indigo\Cart\Cart;
use Orm\Model;

/**
 * Cart Model
 *
 * Cart Model definition
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CartModel extends Model
{
	protected static $_has_many = array(
		'items' => array(
			'model_to' => 'Indigo\\Webshop\\Model\\CartItemModel',
			'key_to'   => 'cart_id',
			'cascade_delete' => true,
		),
	);

	protected static $_observers = array(
		'Orm\Observer_CreatedAt' => array(
			'events' => array('before_insert'),
			'mysql_timestamp' => false,
		),
		'Orm\Observer_UpdatedAt' => array(
			'events' => array('before_save'),
			'mysql_timestamp' => false,
			'relations' => array('items'),
		),
	);

	protected static $_properties = array(
		'id',
		'user_id',
		'identifier',
		'created_at',
		'updated_at',
	);

	protected static $_table_name = 'carts';

	public static function forgeFromCart(Cart $cart)
	{
		return static::forge(array(
			'identifier' => $cart->getId()
		));
	}
}
