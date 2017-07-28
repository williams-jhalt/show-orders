<?php

namespace AppBundle\Service;

use AppBundle\Entity\Customer;
use AppBundle\Entity\Product;
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
    
    public function getItems($customerNumber) {
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        return $customer->getShowOrder()->getItems();
        
    }

    public function removeProductFromCart($customerNumber, $itemNumber) {
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $product = $this->em->getRepository('AppBundle:Product')->findOneByItemNumber($itemNumber);
        
        foreach ($customer->getShowOrder()->getItems() as $item) {
            if ($item->getProduct() == $product) {
                $this->em->remove($item);
                $this->em->flush();
                return;
            }
        }
        
    }

    public function addProductToCart($customerNumber, $itemNumber, $quantity = 1) {        
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $product = $this->em->getRepository('AppBundle:Product')->findOneByItemNumber($itemNumber);

        $showOrder = $customer->getShowOrder();

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
        
        return $item;
    }

    public function checkCart($customerNumber, $itemNumber) {
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);

        $showOrder = $customer->getShowOrder();

        foreach ($showOrder->getItems() as $item) {
            if ($item->getProduct()->getItemNumber() == $itemNumber) {
                return $item->getQuantity();
            }
        }

        return false;
    }

}
