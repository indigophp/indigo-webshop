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
// use Session;

/**
 * Fuel Session Store
 *
 * SAve cart using Fuel Session
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class FuelSessionStore extends SessionStore
{
	public function load(Cart $cart)
	{
		$items = \Session::get($this->sessionKey . '.' . $cart->getId(), array());

		$cart->setContents($items);

		return true;
	}

	public function save(Cart $cart)
	{
		$data = $cart->getContents();
		\Session::set($this->sessionKey . '.' . $cart->getId(), $data);

		return true;
	}

	public function delete(Cart $cart)
	{
		\Session::delete($this->sessionKey . '.' . $cart->getId());

		return true;
	}
}
