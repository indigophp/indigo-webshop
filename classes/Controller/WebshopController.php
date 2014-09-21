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
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap3View;

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
		// var_dump($this->request); exit;
		// var_dump(\Erp\Stock\Model\CategoryModel::generate_options()); exit;
		// $segments = \Arr::to_assoc($this->request->route->method_params);

		$manager = DiC::multiton('doctrine.manager');
		$em = $manager->getEntityManager();

		$permalink = implode('/', $this->request->route->method_params);

		$categories = $em->createQueryBuilder()
			->select('t')
			->from('Taxonomy\\Entity\\Taxon', 't')
			->where('t.id = ?1')
			->setParameter(1, 1)
			->getQuery()
			->getResult();

		$taxon = $em->getRepository('Taxonomy\\Entity\\Taxon')
			->findOneByPermalink($permalink);

		$query = $em->createQueryBuilder()
			->select('product')
			->from('Erp\\Stock\\Entity\\Product', 'product')
			->innerJoin('product.taxons', 'taxon')
			->andWhere('taxon = :taxon')
			->setParameter('taxon', $taxon);

		// $query = $em->createQueryBuilder()
		// 	->select('p')
		// 	->from('Erp\\Stock\\Entity\\Product', 'p');
			// ->innerJoin('p.Taxon', t)
			// ->where('t.permalink = ?1')
			// ->setParameter(1, 'kategoriak/'.implode('/', $this->request->route->method_params));

		$adapter = new DoctrineORMAdapter($query);

		$pager = new Pagerfanta($adapter);
		$pager->setMaxPerPage(16);

		$pager->setCurrentPage(\Input::get('page', 1));
		$products = $pager->getCurrentPageResults();

		$view = new TwitterBootstrap3View;

		$this->template->content = $this->view('webshop/products.twig');
		$this->template->content->title = dgettext('webshop', 'Products');
		$this->template->content->set('categories', reset($categories), false);
		$this->template->content->set('active', $permalink);
		$this->template->content->set('products', $products, false);
		$this->template->content->set('pager', $pager, false);
		$this->template->content->set('pager_view', $view, false);
		$this->template->content->set('pager_router', function($page)
		{
			return \Uri::update_query_string(['page' => $page]);
		}, false);
	}
}
