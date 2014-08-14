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
		$product = \Model\ProductModel::find($id);

		if ($product === null)
		{
			throw new \HttpNotFoundException();
		}

		$this->template->content = $this->view('webshop/product.twig');
	}
}
