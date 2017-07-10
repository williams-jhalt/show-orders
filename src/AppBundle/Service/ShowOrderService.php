<?php

namespace AppBundle\Service;

use AppBundle\Entity\Product;
use AppBundle\Entity\ShowOrder;
use AppBundle\Entity\ShowOrderItem;
use Doctrine\ORM\EntityManager;

class ShowOrderService {

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function getShowOrder($customerNumber) {
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        
        if ($customer) {
            return $customer->getShowOrder();            
        }
        
        return null;
        
    }

    public function addProductToCart($customerNumber, Product $product, $quantity = 1) {

        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $showOrder = $customer->getShowOrder();

        if (!$showOrder) {
            $showOrder = new ShowOrder();
            $showOrder->setCustomer($customer);
        }

        $found = false;
        foreach ($showOrder->getItems() as $item) {
            if ($item->getProduct() == $product) {
                $item->setQuantity($quantity);
                $found = true;
                $this->em->persist($showOrder);
                break;
            }
        }

        if (!$found) {
            $item = new ShowOrderItem();
            $item->setProduct($product);
            $item->setQuantity($quantity);
            $item->setShowOrder($showOrder);
            $this->em->persist($item);
        }

        $this->em->flush();
    }

    public function checkCart($customerNumber, Product $product) {

        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $showOrder = $customer->getShowOrder();

        if ($showOrder) {

            foreach ($showOrder->getItems() as $item) {
                if ($item->getProduct() == $product) {
                    return $item->getQuantity();
                }
            }
        }

        return false;
    }

}
