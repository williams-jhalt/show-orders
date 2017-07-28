<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="product")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 * @UniqueEntity("itemNumber")
 */
class Product {
    
    const DEFAULT_IMAGE = 'default_product.png';

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
     * @ORM\Column(name="itemNumber", type="string", length=255, unique=true)
     */
    private $itemNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal")
     */
    private $price;

    /**
     *
     * @var Vendor
     * 
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="products")
     */
    private $vendor;

    /**
     *
     * @var ArrayCollection
     * 
     * @ORM\OneToMany(targetEntity="ShowOrderItem", mappedBy="product")
     */
    private $showOrderItems;

    /**
     * @var string
     *
     * @ORM\Column(name="imageUrl", type="string", length=255)
     */
    private $imageUrl = self::DEFAULT_IMAGE;

    public function __construct() {
        $this->showOrderItems = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    public function getItemNumber() {
        return $this->itemNumber;
    }

    public function getName() {
        return $this->name;
    }

    public function getVendor() {
        return $this->vendor;
    }

    public function getShowOrderItems() {
        return $this->showOrderItems;
    }

    public function setItemNumber($itemNumber) {
        $this->itemNumber = $itemNumber;
        return $this;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setVendor(Vendor $vendor) {
        $this->vendor = $vendor;
        return $this;
    }

    public function setShowOrderItems(ArrayCollection $showOrderItems) {
        $this->showOrderItems = $showOrderItems;
        return $this;
    }

    public function getImageUrl() {
        return $this->imageUrl;
    }

    public function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
        return $this;
    }
    
    public function getImageSet() {
        return ($this->imageUrl != self::DEFAULT_IMAGE);
    }

}
