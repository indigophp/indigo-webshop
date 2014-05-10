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

use Indigo\Webshop\Model\CartModel;
use Indigo\Cart\Cart;
use Indigo\Cart\Item;
use Indigo\Cart\Option\Option;
use Indigo\Cart\Store\OrmStore;
use Indigo\Cart\Store\FuelSessionStore;
use Fuel\Common\Table;
use Fuel\Common\Table\EnumRowType;

/**
 * Cart controller class
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

		// $this->store = new OrmStore;
		$this->store = new FuelSessionStore;

		$cart = \Session::get('active_cart', 'cart');
		$this->cart = new Cart($cart);

		$this->store->load($this->cart);
	}

	public function after($response)
	{
		$this->store->save($this->cart);

		\Session::set('active_cart', $this->cart->getId());

		return parent::after($response);
	}

	public function action_index()
	{
		$table = new Table;

		$table->createRow(EnumRowType::Header);
		$table->addCell('Id');
		$table->addCell('Name');
		$table->addCell('Price');
		$table->addCell('Quantity');
		$table->addCell('Subtotal');
		$table->addCell('Delete');
		$table->addRow();

		foreach ($this->cart as $item)
		{
			$table->createRow();
			$table->addCell($item['id']);
			$table->addCell($item['name']);
			$table->addCell($item->getPrice(true));
			$table->addCell($item['quantity']);
			$table->addCell($item->getSubtotal(true));
			$table->addCell(\Html::anchor('webshop/cart/delete/' . $item->getId(), 'Delete'));
			$table->addRow();
		}

		$render = new Table\Render\SimpleTable;

		echo $this->cart->getId() . '<br>';
		echo \Html::anchor('webshop/cart/save', 'Save');
		echo ' ';
		echo \Html::anchor('webshop/cart/add', 'Add');
		echo ' ';
		echo \Html::anchor('webshop/cart/reset', 'Delete');
		echo $render->renderTable($table);

		$table = new Table;

		$table->createRow(EnumRowType::Header);
		$table->addCell('Id');
		$table->addCell('Elements');
		$table->addCell('Total');
		$table->addCell('Load');
		$table->addRow();

		$store = new OrmStore;

		$carts = CartModel::query()->select('identifier')->get();

		foreach ($carts as $cart)
		{
			$cart = new Cart($cart->identifier);
			$store->load($cart);
			$table->createRow();
			$table->addCell($cart->getId());
			$table->addCell(count($cart));
			$table->addCell($cart->getTotal(true));
			$table->addCell(\Html::anchor('webshop/cart/load/' . $cart->getId(), 'Load'));
			$table->addRow();
		}

		echo $render->renderTable($table);
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

		$this->registerRedirect();
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

	public function action_delete($item)
	{
		$this->cart->delete($item);

		$this->registerRedirect();
	}

	public function action_reset()
	{
		$this->cart->reset();
		$this->store->delete($this->cart);

		\Session::set('active_cart', 'cart');

		return \Response::redirect('webshop/cart/');
	}

	public function registerRedirect()
	{
		\Event::register('controller_finished', function() {
			\Response::redirect('webshop/cart/');
		});
	}
}
