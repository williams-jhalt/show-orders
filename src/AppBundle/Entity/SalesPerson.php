<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="sales_person")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\SalesPersonRepository")
 */
class SalesPerson {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="salesPersonNumber", type="string", length=255, unique=true)
     */
    private $salesPersonNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Customer", mappedBy="salesPerson", cascade={"remove"})
     */
    private $customers;

    public function __construct() {
        $this->notes = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getSalesPersonNumber() {
        return $this->salesPersonNumber;
    }

    public function setSalesPersonNumber($salesPersonNumber) {
        $this->salesPersonNumber = $salesPersonNumber;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function getCustomers() {
        return $this->customers;
    }

    public function setCustomers(ArrayCollection $customers) {
        $this->customers = $customers;
        return $this;
    }

    public function orderTotal() {
        $total = 0.0;
        foreach ($this->customers as $customer) {
            $total += $customer->getShowOrder()->orderTotal();
        }
        return $total;
    }

    public function __toString() {
        return $this->name;
    }

}
