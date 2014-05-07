<?php

/*
 * This file is part of the Indigo Webshop package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Webshop\Controller;

use Indigo\Cart\Cart;
use Indigo\Cart\Item;
use Indigo\Cart\Store\OrmStore;

/**
 * Some class
 *
 * Some description
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CartController extends \Controller
{
	protected $cart;
	protected $store;

	public function before($data = null)
	{
		parent::before($data);

		$this->store = new OrmStore;
		$this->cart = new Cart('cart');

		$this->store->load($this->cart);
	}

	public function after($response)
	{
		$this->store->save($this->cart);

		return parent::after($response);
	}

	public function action_index()
	{
		$item = new Item(array(
			'id'       => 1,
			'name'     => 'Name',
			'price'    => 1.0,
			'tax'      => 27,
			'quantity' => 1
		));

		// $this->cart->add($item);

		// exit;
	}

	public function action_delete()
	{
		$this->store->delete($this->cart);
		exit;
	}
}
