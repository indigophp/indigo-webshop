<?php

/*
 * This file is part of the Indigo Webshop package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webshop\Entity;

/**
 * Order entity
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Order
{
	use \Indigo\Doctrine\Field\Id;
	use \Indigo\Doctrine\Behavior\Timestamp\DateTime;

	/**
	 * @var Collection
	 *
	 * @ORM\OneToMany
	 */
	protected $items;

	/**
	 * @var string
	 *
	 * @ORM\Column(name="note", type="text")
	 */
	protected $note;
}
