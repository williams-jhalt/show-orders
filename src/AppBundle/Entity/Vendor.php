<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Vendor
 *
 * @ORM\Table(name="vendor")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VendorRepository")
 */
class Vendor {

    const DEFAULT_IMAGE = 'default_vendor.png';

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
     * @ORM\Column(name="vendorNumber", type="string", length=255, unique=true)
     */
    private $vendorNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="company", type="string", length=255)
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(name="imageUrl", type="string", length=255)
     */
    private $imageUrl = self::DEFAULT_IMAGE;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="Product", mappedBy="vendor", cascade={"persist", "remove"})
     */
    private $products;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="CustomerNote", mappedBy="vendor")
     */
    private $notes;

    /**
     * 
     * @var string
     * 
     * @ORM\Column(name="booth", type="string", length=255, nullable=true)
     */
    private $booth;

    /**
     * 
     * @var string
     * 
     * @ORM\Column(name="sponserhipLevel", type="string", length=255, nullable=true)
     */
    private $sponsershipLevel;

    /**
     *
     * @var string
     * 
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;

    public function __construct() {
        $this->products = new ArrayCollection();
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
     * Set vendorNumber
     *
     * @param string $vendorNumber
     *
     * @return Vendor
     */
    public function setVendorNumber($vendorNumber) {
        $this->vendorNumber = $vendorNumber;

        return $this;
    }

    /**
     * Get vendorNumber
     *
     * @return string
     */
    public function getVendorNumber() {
        return $this->vendorNumber;
    }

    /**
     * Set company
     *
     * @param string $company
     *
     * @return Vendor
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

    public function getProducts() {
        return $this->products;
    }

    public function setProducts(ArrayCollection $products) {
        $this->products = $products;
        return $this;
    }

    public function getImageUrl() {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function __toString() {
        return $this->company;
    }

    public function getImageSet() {
        return ($this->imageUrl != self::DEFAULT_IMAGE);
    }

    public function getNotes() {
        return $this->notes;
    }

    public function setNotes(ArrayCollection $notes) {
        $this->notes = $notes;
        return $this;
    }

    public function getOrderTotal() {

        $total = 0;

        foreach ($this->products as $product) {

            $subtotal = 0;

            foreach ($product->getShowOrderItems() as $item) {
                $subtotal += ($item->getQuantity() * $product->getPrice());
            }

            $total += $subtotal;
        }

        return $total;
    }

    public function getSponsershipLevel() {
        return $this->sponsershipLevel;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setSponsershipLevel($sponsershipLevel) {
        $this->sponsershipLevel = $sponsershipLevel;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    public function getBooth() {
        return $this->booth;
    }

    public function setBooth($booth) {
        $this->booth = $booth;
        return $this;
    }

}
