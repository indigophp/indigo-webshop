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
use Fuel\Validation\Validator;

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

		$this->template->content = $this->view('frontend/webshop/product.twig');
		$this->template->content->set('product', $product, false);
	}

	public function action_products()
	{
		$manager = DiC::multiton('doctrine.manager');
		$em = $manager->getEntityManager();

		$permalink = implode('/', $this->request->route->method_params);

		// $taxon = $em->getRepository('Taxonomy\\Entity\\Taxon')
		// 	->findOneByPermalink($permalink);

		$query = $em->createQueryBuilder()
			->select('product')
			->from('Erp\\Stock\\Entity\\Product', 'product')
			->innerJoin('product.taxons', 'taxon')
			->andWhere('taxon.permalink LIKE :taxon')
			->setParameter('taxon', $permalink . '%');

		$adapter = new DoctrineORMAdapter($query);

		$pager = new Pagerfanta($adapter);
		$pager->setMaxPerPage(16);

		$pager->setCurrentPage(\Input::get('page', 1));
		$products = $pager->getCurrentPageResults();

		$view = new TwitterBootstrap3View;

		$this->template->content = $this->view('frontend/webshop/products.twig');
		$this->template->content->title = dgettext('webshop', 'Products');
		$this->template->content->set('products', $products, false);
		$this->template->content->set('pager', $pager, false);
		$this->template->content->set('pager_view', $view, false);
		$this->template->content->set('pager_router', function($page) use ($permalink)
		{
			return \Uri::create('webshop/products/'.$permalink, [], ['page' => $page]);
		}, false);
	}

	public function action_order()
	{
		$logger = DiC::resolve('logger.alert');

		if (\Input::method() === 'POST')
		{
			$validator = new Validator;

			$validator->addField('name', 'Név')
				->required();

			$validator->addField('email', 'Email cím')
				->required()
				->email();

			$validator->addField('phone', 'Telefonszám')
				->required();

			$validator->addField('billName', 'Számlázási név')
				->required();

			$validator->addField('billPostal', 'Irányítószám')
				->required();

			$validator->addField('billCity', 'Város')
				->required();

			$validator->addField('billAddress', 'Számlázási cím')
				->required();

			$data = \Input::post();

			$result = $validator->run($data);

			if ($result->isValid())
			{
				$view = $this->view('email/webshop/order.twig', $data);
				$view->set('cart', $cart = DiC::resolve('cart'), false);

				$email = \Email::forge();
				$email->from('info@partibuli.hu');
				$email->to('mark.sagikazar@gmail.com');
				$email->html_body($view);

				try
				{
					$email->send();
				}
				catch (\EmailSendingFailedException $e) {}

				$context = ['template' => 'success'];

				$logger->info('Megrendelés sikeres.', $context);

				$cart->reset();

				return \Response::redirect('webshop/success');
			}

			else
			{
				$context = ['errors' => $result->getErrors()];
				$logger->error('Hiba történt a megrendelés közben:', $context);
			}

		}

		$this->template->content = $this->view('frontend/webshop/order.twig');
	}

	public function action_success()
	{
		$this->template->content = $this->view('frontend/webshop/success.twig');
	}
}
