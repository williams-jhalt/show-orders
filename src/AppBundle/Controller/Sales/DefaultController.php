<?php

namespace AppBundle\Controller\Sales;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    /**
     * @Route("/sales", name="sales_homepage")
     */
    public function indexAction() {
        return $this->render('sales/sales.html.twig');
    }

}
