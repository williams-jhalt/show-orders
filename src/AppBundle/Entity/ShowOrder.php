<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ShowOrder
 *
 * @ORM\Table(name="show_order")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ShowOrderRepository")
 */
class ShowOrder {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var Customer
     * 
     * @ORM\OneToOne(targetEntity="Customer", inversedBy="showOrder")
     */
    private $customer;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="ShowOrderItem", mappedBy="showOrder", cascade={"persist"})
     */
    private $items;

    public function __construct() {
        $this->items = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getCustomer() {
        return $this->customer;
    }

    public function setCustomer(Customer $customer) {
        $this->customer = $customer;
        return $this;
    }

    public function getItems() {
        return $this->items;
    }

    public function setItems(ArrayCollection $items) {
        $this->items = $items;
        return $this;
    }

}
