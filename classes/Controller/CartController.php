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
use Indigo\Cart\Option\Option;
use Indigo\Cart\Store\OrmStore;
use Indigo\Cart\Store\FuelSessionStore;
use Fuel\Common\Table;
use Fuel\Common\Table\EnumRowType;

/**
 * Cart Controller
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CartController extends \Controller\BaseController
{
	protected $cart;
	protected $store;

	public function before($data = null)
	{
		parent::before($data);

		// $this->store = new OrmStore;
		$store = new FuelSessionStore;

		$cart = \Session::get('active_cart', 'cart');
		$cart = new Cart($cart);

		$store->load($cart);

		\Event::register('shutdown', function() use ($store, $cart) {
			$store->save($cart);
		});

		$this->store = $store;
		$this->cart = $cart;
	}

	public function action_index()
	{
		$this->template->content = $this->view('webshop/cart/index.twig');
		$this->template->content->set('cart', $this->cart, false);
	}

	public function action_saved()
	{
		$carts = \Model\CartModel::query()->select('identifier')->get();

		$this->template->content = $this->view('webshop/cart/saved.twig');

		if ($carts)
		{
			$store = new OrmStore;

			foreach ($carts as &$cart)
			{
				$cart = new Cart($cart->identifier);
				$store->load($cart);
			}

			$this->template->content->set('carts', $carts, false);
		}
	}

	public function action_add()
	{
		$item = new Item(array(
			'id'       => 1,
			'name'     => 'Name',
			'price'    => 1.0,
			'quantity' => 1,
			'option'   => new Option(array(
				'id'    => 1,
				'name'  => 'Test',
				'value' => 1.0
			)),
		));

		$this->cart->add($item);

		$this->registerRedirect();
	}

	public function action_load($id)
	{
		$this->cart->reset();
		$this->cart->setId($id);

		$store = new OrmStore;

		$store->load($this->cart);

		\Session::set('active_cart', $id);

		return \Response::redirect('webshop/cart/');
	}

	public function action_save()
	{
		$store = new OrmStore;

		if ($this->cart->getId() == 'cart')
		{
			$this->cart->setId();
		}

		$store->save($this->cart);

		\Session::set('active_cart', 'cart');

		return \Response::redirect('webshop/cart/');
	}

	public function action_remove($item)
	{
		$this->cart->delete($item);

		return \Response::redirect('webshop/cart/');
	}

	public function action_delete()
	{
		$store = new OrmStore;

		$store->delete($this->cart);

		\Session::set('active_cart', 'cart');

		return \Response::redirect('webshop/cart/');
	}

	public function action_reset()
	{
		$this->cart->reset();

		return \Response::redirect('webshop/cart/');
	}
}
