<?php

// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToOne(targetEntity="SalesPerson")
     */
    private $salesPerson;

    public function __construct() {
        parent::__construct();
    }

    public function __toString() {
        return $this->username;
    }

    public function getSalesPerson() {
        return $this->salesPerson;
    }

    public function setSalesPerson($salesPerson) {
        $this->salesPerson = $salesPerson;
        return $this;
    }

}
