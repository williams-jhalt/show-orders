<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShowOrderItem
 *
 * @ORM\Table(name="show_order_item")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShowOrderItemRepository")
 */
class ShowOrderItem {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity;

    /**
     *
     * @var Product
     * 
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="showOrderItems")
     */
    private $product;

    /**
     *
     * @var ShowOrder
     * 
     * @ORM\ManyToOne(targetEntity="ShowOrder", inversedBy="items", cascade={"persist"})
     */
    private $showOrder;

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return ShowOrderItem
     */
    public function setQuantity($quantity) {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity() {
        return $this->quantity;
    }

    public function getProduct() {
        return $this->product;
    }

    public function setProduct(Product $product) {
        $this->product = $product;
        return $this;
    }

    public function getShowOrder() {
        return $this->showOrder;
    }

    public function setShowOrder(ShowOrder $showOrder) {
        $this->showOrder = $showOrder;
        return $this;
    }

}
