<?php

/*
 * This file is part of the Indigo Webshop module.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Aliased classes to work with Fuel v1
 */

use Indigo\Core\Alias;

$manager = Alias::instance('default');

$manager->alias(array(
	'Webshop\\Controller_Cart' => 'Indigo\\Webshop\\Controller\\CartController',
	'Webshop\\Controller_Webshop' => 'Indigo\\Webshop\\Controller\\WebshopController',
));
