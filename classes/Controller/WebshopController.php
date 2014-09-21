<?php

/*
 * This file is part of the Indigo Webshop module.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Webshop\Controller;

use Indigo\Fuel\Dependency\Container as DiC;

/**
 * Webshop Controller
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class WebshopController extends \Controller\BaseController
{
	/**
	 * Product action
	 *
	 * @param integer $id
	 */
	public function action_product($id)
	{
		$manager = DiC::multiton('doctrine.manager');
		$em = $manager->getEntityManager();

		$product = $em->find('Erp\\Stock\\Entity\\Product', $id);

		if ($product === null)
		{
			throw new \HttpNotFoundException();
		}

		$this->template->content = $this->view('webshop/product.twig');
		$this->template->content->set('product', $product, false);
	}

	public function action_products()
	{
		$segments = \Arr::to_assoc($this->request->route->method_params);

		$manager = DiC::multiton('doctrine.manager');
		$em = $manager->getEntityManager();

		$repository = $em->getRepository('Erp\\Stock\\Entity\\Product');
		$products = $repository->findAll();

		$this->template->content = $this->view('webshop/products.twig');
		$this->template->content->set('products', $products, false);
	}
}
