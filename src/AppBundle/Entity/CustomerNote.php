<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerNote
 *
 * @ORM\Table(name="customer_note")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CustomerNoteRepository")
 */
class CustomerNote {

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
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     *
     * @var Customer
     * 
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="notes", cascade={"persist"})
     */
    private $customer;

    /**
     *
     * @var Vendor
     * 
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="notes", cascade={"persist"})
     */
    private $vendor;

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

    public function getNotes() {
        return $this->notes;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
        return $this;
    }

    public function getVendor() {
        return $this->vendor;
    }

    public function setVendor(Vendor $vendor) {
        $this->vendor = $vendor;
        return $this;
    }

}
