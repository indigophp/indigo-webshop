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

use Indigo\Fuel\Dependency\Container as DiC;
use Fuel\Validation\Validator;

/**
 * Cart Controller
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class CartController extends \Controller\BaseController
{
	/**
	 * Cart object
	 *
	 * @var Indigo\Cart\Cart
	 */
	protected $cart;

	/**
	 * {@inheritdoc}
	 */
	public function before($data = null)
	{
		parent::before();

		// This is not real DI, just a mimic
		$this->cart = DiC::resolve('cart');
	}

	public function action_index()
	{
		$this->template->content = $this->view('webshop/cart.twig');
		$this->template->content->set('cart', $this->cart, false);
	}

	public function post_add()
	{
		$val = new Validator;

		$val->addField('id', gettext('ID'))
			->required()
			->numericMin(1);

		$val->addField('quantity', gettext('Quantity'))
			->required()
			->numericMin(1);

		$result = $val->run(\Input::post());

		$logger = DiC::resolve('logger.alert');

		if ( ! $result->isValid())
		{
			$context = ['errors' => $result->getErrors()];

			$logger->error(gettext('Product cannot be added to the cart.'), $context);

			return \Response::redirect_back();
		}

		$id = (int) \Input::post('id');
		$quantity = (int) \Input::post('quantity');

		// Product is already in the cart
		if ($this->cart->hasItem($id))
		{
			$logger->notice(gettext('Product is already in the cart.'));

			return \Response::redirect_back();
		}

		$em = DiC::resolve('doctrine.manager')->getEntityManager();

		$product = $em->find('Erp\\Stock\\Entity\\Product', $id);

		if ($product === null)
		{
			$logger->error(gettext('Product cannot be found.'));

			return \Response::redirect('webshop/cart');
		}

		$item = new \Webshop\ProductItem($product, $quantity);

		$this->cart->addItem($item);

		$context = ['template' => 'success'];

		$logger->info(gettext('Product successfully added to the cart.'), $context);

		return \Response::redirect_back();
	}

	public function post_update()
	{
		// It is possible to only update one item
		// Will make sense with ajax support
		$ids = (array) \Input::post('id', array());
		$quantities = (array) \Input::post('quantity', array());

		// Checking that the two arrays have equals item count is not necessary
		// Different count means someone is trying to do nasty
		$items = array_combine($ids, $quantities);

		$notUpdated = 0;

		foreach ($items as $id => $quantity)
		{
			// If an item cannot be found return with partial success
			if ( ! $item = $this->cart->getItem($id))
			{
				$notUpdated++;

				continue;
			}

			$quantity = (int) $quantity;

			// Invalid quantities are skipped
			if ($quantity < 1)
			{
				$notUpdated++;

				continue;
			}

			$item->setQuantity($quantity);
		}

		$logger = DiC::resolve('logger.alert');

		// Return with partial success
		if ($notUpdated > 0)
		{
			$context = [
				'from' => [
					'%all%'     => count($items),
					'%updated%' => count($items) - $notUpdated,
				],
			];

			$logger->notice(gettext('%updated% out of %all% items updated.'), $context);
		}
		else
		{
			$context = ['template' => 'success'];

			$logger->info(gettext('Cart successfully updated.'), $context);
		}

		return \Response::redirect_back();
	}

	public function action_remove($id)
	{
		$logger = DiC::resolve('logger.alert');

		if ($this->cart->removeItem($id))
		{
			$context = ['template' => 'success',];

			$logger->info(gettext('Item successfully removed from the cart.'), $context);
		}
		else
		{
			$context = [
				'from' => '%id%',
				'to'   => $id,
			];

			$logger->warning(gettext('Item #%id% cannot be removed from the cart.'), $context);
		}

		return \Response::redirect('webshop/cart');
	}

	public function action_reset()
	{
		if($this->cart->reset())
		{
			$context = ['template' => 'success'];

			DiC::resolve('logger.alert')
				->info(gettext('Cart successfully cleared.'), $context);
		}

		return \Response::redirect('webshop/cart/');
	}
}
