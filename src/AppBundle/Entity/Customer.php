<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Customer
 *
 * @ORM\Table(name="customer")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerRepository")
 */
class Customer {

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
     * @ORM\Column(name="customerNumber", type="string", length=255, unique=true)
     */
    private $customerNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="firstName", type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="lastName", type="string", length=255)
     */
    private $lastName;

    /**
     *
     * @var ShowOrder
     * 
     * @ORM\OneToOne(targetEntity="ShowOrder", mappedBy="customer", cascade={"persist","remove"})
     */
    private $showOrder;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="CustomerNote", mappedBy="customer")
     */
    private $notes;

    /**
     *
     * @var SalesPerson
     * 
     * @ORM\ManyToOne(targetEntity="SalesPerson", inversedBy="customers", cascade={"remove"})
     */
    private $salesPerson;

    public function __construct() {
        $this->showOrder = new ShowOrder();
        $this->showOrder->setCustomer($this);
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

    /**
     * Set customerNumber
     *
     * @param string $customerNumber
     *
     * @return Customer
     */
    public function setCustomerNumber($customerNumber) {
        $this->customerNumber = $customerNumber;

        return $this;
    }

    /**
     * Get customerNumber
     *
     * @return string
     */
    public function getCustomerNumber() {
        return $this->customerNumber;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return Customer
     */
    public function setCompany($company) {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany() {
        return $this->company;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return Customer
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Customer
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    public function getShowOrder() {
        return $this->showOrder;
    }

    public function setShowOrders(ShowOrder $showOrder) {
        $this->showOrder = $showOrder;
        return $this;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function setNotes(ArrayCollection $notes) {
        $this->notes = $notes;
        return $this;
    }
    
    public function getSalesPerson() {
		return $this->salesPerson;
	}
	
	public function setSalesPerson(SalesPerson $salesPerson) {
		$this->salesPerson = $salesPerson;
		return $this;
	}

}
