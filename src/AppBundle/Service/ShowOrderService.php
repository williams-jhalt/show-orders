<?php

namespace AppBundle\Service;

use AppBundle\Entity\Customer;
use AppBundle\Entity\CustomerNote;
use AppBundle\Entity\Product;
use AppBundle\Entity\ShowOrderItem;
use Doctrine\ORM\EntityManager;
use Exception;

class ShowOrderService {

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
    
    public function addNotes($customerNumber, $vendorNumber, $notes) {
        
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $vendor = $this->em->getRepository('AppBundle:Vendor')->findOneByVendorNumber($vendorNumber);
        
        $note = new CustomerNote();
        $note->setNotes($notes);
        $note->setVendor($vendor);
        $note->setCustomer($customer);
        
        $this->em->persist($note);
        $this->em->flush($note);
        
    }
    
    public function getNotes($customerNumber, $vendorNumber) {
                
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        $vendor = $this->em->getRepository('AppBundle:Vendor')->findOneByVendorNumber($vendorNumber);
        
        return $this->em->getRepository('AppBundle:CustomerNote')->findBy(['customer' => $customer, 'vendor' => $vendor]);        
        
    }

    public function getItems($customerNumber, $vendorId = null) {

        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        
        $items = $customer->getShowOrder()->getItems();

        if ($vendorId !== null) {
            $vendor = $this->em->getRepository('AppBundle:Vendor')->find($vendorId);
            $result = [];
            foreach ($items as $item) {
                if ($item->getProduct()->getVendor() == $vendor) {
                    $result[] = $item;
                }
            }
            return $result;
        }

        return $items;
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

        if ($quantity == 0) {
            return $this->removeProductFromCart($customerNumber, $itemNumber);
        }

        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        
        if ($customer === null) {
            throw new Exception("Customer Not Found");
        }
        
        $product = $this->em->getRepository('AppBundle:Product')->findOneByItemNumber($itemNumber);
        
        if ($product === null) {
            throw new Exception("Product Not Found");
        }

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

        if (!$found && $quantity > 0) {
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
