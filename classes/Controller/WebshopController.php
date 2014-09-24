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
	protected $shipping = [
		'1' => 'Személyes átvétel (PARTI BULI BOLT)',
		'2' => 'Személyes átvétel (BULI PONT)',
		'3' => 'Buli futár',
		'4' => 'Futárszolgálat',
		'5' => 'Budapesti lufi kiszállítás',
	];

	protected $payment = [
		'1' => 'Készpénz',
		'2' => 'Banki utalás',
	];

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

	public function action_search()
	{
		$manager = DiC::multiton('doctrine.manager');
		$em = $manager->getEntityManager();

		$search = \Input::get('search');
		if (empty($search))
		{
			return \Response::redirect('');
		}

		$query = $em->createQueryBuilder()
			->select('product')
			->from('Erp\\Stock\\Entity\\Product', 'product')
			->andWhere('product.name LIKE :p')
			->orWhere('product.description LIKE :p')
			->setParameter('p', '%' . $search . '%');

		$adapter = new DoctrineORMAdapter($query);

		$pager = new Pagerfanta($adapter);
		$pager->setMaxPerPage(16);

		$pager->setCurrentPage(\Input::get('page', 1));
		$products = $pager->getCurrentPageResults();

		$view = new TwitterBootstrap3View;

		$this->template->content = $this->view('frontend/webshop/products.twig');
		$this->template->content->title = 'Keresés';
		$this->template->content->set('products', $products, false);
		$this->template->content->set('pager', $pager, false);
		$this->template->content->set('pager_view', $view, false);
		$this->template->content->set('pager_router', function($page)
		{
			return \Uri::create('webshop/search/', [], ['page' => $page, 'search' => \Input::get('search')]);
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

			$validator->addField('shipping', 'Szállítási mód')
				->required();

			$validator->addField('payment', 'Fizetési mód')
				->required();

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

			$validator->addField('aszf', 'Ászf')
				->required();

			$data = \Input::post();

			$result = $validator->run($data);
			$error = false;

			if ($result->isValid())
			{
				$view = $this->view('email/webshop/order.twig', $data);
				$view->set('cart', $cart = DiC::resolve('cart'), false);

				switch (\Input::post('shipping')) {
					case 1:
					case 2:
						$cost = 0;
						break;
					case 3:
						$cost = 800;
						break;
					case 4:
						if ($cart->getTotal()->getAmount() >= 20000)
						{
							$cost = 0;
						}
						else
						{
							$cost = 1500;
						}
						break;
					case 5:
						switch (\Input::post('shipPostal', \Input::post('billPostal'))) {
							case 1170:
							case 1171:
							case 1172:
							case 1173:
							case 1174:
							case 1175:
							case 1176:
							case 1177:
							case 1178:
							case 1179:
								$cost = 0;
								break;
							case 1160:
							case 1161:
							case 1162:
							case 1163:
							case 1164:
							case 1165:
							case 1166:
							case 1167:
							case 1168:
							case 1169:
							case 1180:
							case 1181:
							case 1182:
							case 1183:
							case 1184:
							case 1185:
							case 1186:
							case 1187:
							case 1188:
							case 1189:
							case 1190:
							case 1191:
							case 1192:
							case 1193:
							case 1194:
							case 1195:
							case 1196:
							case 1197:
							case 1198:
							case 1199:
							case 1200:
							case 1201:
							case 1202:
							case 1203:
							case 1204:
							case 1205:
							case 1206:
							case 1207:
							case 1208:
							case 1209:
								$cost = 2500;
								break;
							default:
								$cost = 4000;
								break;
						}
						break;
					default:
						// Valaki hackelt
						$cost = 10000000000;
						break;
				}

				$view->cost = $cost;
				$view->shipping_modes = $this->shipping;
				$view->payment_modes = $this->payment;

				$email = \Email::forge();
				$email->from('info@partibuli.hu', 'Parti Buli Bolt');
				$email->to(\Input::post('email'));
				$email->bcc('info@partibuli.hu');
				$email->subject('Megrendelés');
				$email->html_body($view);

				try
				{
					$email->send();
				}
				catch (\EmailSendingFailedException $e)
				{
					$error = true;
					$errors = ['email' => 'Email küldés sikertelen'];
				}
			}
			else
			{
				$error = true;
				$errors = $result->getErrors();
			}

			if ($error) {
				$context = ['errors' => $errors];
				$logger->error('Hiba történt a megrendelés közben:', $context);
			}
			else
			{

				$context = ['template' => 'success'];

				$logger->info('Megrendelés sikeres.', $context);

				$cart->reset();

				return \Response::redirect('webshop/success');
			}
		}

		$this->template->content = $this->view('frontend/webshop/order.twig');
		$this->template->content->shipping = $this->shipping;
		$this->template->content->payment = $this->payment;
	}

	public function action_success()
	{
		$this->template->content = $this->view('frontend/webshop/success.twig');
	}
}
