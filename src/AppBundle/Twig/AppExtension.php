<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Customer;
use AppBundle\Entity\Product;
use AppBundle\Service\ShowOrderService;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Twig_SimpleFunction;

class AppExtension extends Twig_Extension implements \Twig_Extension_GlobalsInterface {

    /**
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ShowOrderService
     */
    private $service;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(RequestStack $requestStack, ShowOrderService $service, EntityManager $em) {
        $this->requestStack = $requestStack;
        $this->service = $service;
        $this->em = $em;
    }

    public function getFunctions() {
        return [
            new Twig_SimpleFunction('checkCart', array($this, 'checkCart'))
        ];
    }

    public function checkCart($itemNumber) {
        return $this->service->checkCart($this->getCustomer()->getCustomerNumber(), $itemNumber);
    }

    public function getGlobals() {
        return [
            'active_customer' => $this->getCustomer()
        ];
    }

    /**
     * 
     * @return Customer|null
     */
    public function getCustomer() {
        $request = $this->requestStack->getCurrentRequest();
        $customerNumber = $request->cookies->get('customerNumber', null);
        $customer = $this->em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
        return $customer;
    }

}
