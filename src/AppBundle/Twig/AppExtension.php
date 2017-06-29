<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Product;
use AppBundle\Service\ShowOrderService;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Twig_SimpleFunction;

class AppExtension extends Twig_Extension {
    
    /**
     *
     * @var RequestStack
     */
    private $requestStack;
    
    /**
     * @var ShowOrderService
     */
    private $service;
    
    public function __construct(RequestStack $requestStack, ShowOrderService $service) {
        $this->requestStack = $requestStack;
        $this->service = $service;
    }
    
    public function getFunctions() {
        return [
            new Twig_SimpleFunction('checkCart', array($this, 'checkCart'))
        ];
    }
    
    public function checkCart(Product $product) {
        
        $request = $this->requestStack->getCurrentRequest();
        $customerNumber = $request->cookies->get('customerNumber');
        return $this->service->checkCart($customerNumber, $product);
        
    }
    
}