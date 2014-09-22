<?php

/*
 * This file is part of the Indigo Base package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webshop;

use Indigo\Fuel\Dependency\Container as DiC;
use Twig_Extension;

class TwigWebshopExtension extends Twig_Extension
{

	/**
	 * Gets the name of the extension.
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'webshop';
	}

	/**
	 * {@inheritdocs}
	 */
	public function getFunctions()
	{
		return array(
			'cart' => new \Twig_Function_Method($this, 'cart'),
			'categories' => new \Twig_Function_Method($this, 'categories'),
			'active' => new \Twig_Function_Method($this, 'active'),
			'random_products' => new \Twig_Function_Method($this, 'random_products'),
		);
	}

	public function cart()
	{
		return DiC::resolve('cart');
	}

	public function categories()
	{
		$em = DiC::resolve('doctrine.manager')->getEntityManager();

		$categories = $em->createQueryBuilder()
			->select('t')
			->from('Taxonomy\\Entity\\Taxon', 't')
			->where('t.id = ?1')
			->setParameter(1, 1)
			->getQuery()
			->getResult();

		return reset($categories);
	}

	public function active()
	{
		return implode('/', \Request::active()->route->method_params);
	}

	public function random_products()
	{
		$em = DiC::resolve('doctrine.manager')->getEntityManager();

		$products = $em->createQueryBuilder()
			->select('p')
			->from('Erp\\Stock\\Entity\\Product', 'p')
			->setMaxResults(20)
			->getQuery()
			->getResult();

		return $products;
	}
}
