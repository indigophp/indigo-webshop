<?php

/*
 * This file is part of the Indigo Cart package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webshop;

use Indigo\Cart\Item;
use Indigo\Cart\TotalCalculator;
use Erp\Stock\Entity\Product;
use InvalidArgumentException;

/**
 * Item implementation based on Indigo ERP Stock Product
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class ProductItem implements Item, TotalCalculator
{
    use \Indigo\Cart\Quantity;

    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product, $quantity)
    {
        $this->setQuantity($quantity);

        $this->product = $product;
    }

    /**
     * Returns the Product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->product->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->product->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getPrice()
    {
        return $this->product->getPrice();
    }

    /**
     * {@inheritdoc}
     */
    public function getSubtotal()
    {
        return $this->product->getPrice()->multiply($this->quantity);
    }

    /**
     * {@inheritdoc}
     */
    public function calculateTotal($total = null)
    {
        if (is_null($total)) {
            return $this->getSubtotal();
        }

        return $total->add($this->getSubtotal());
    }
}
