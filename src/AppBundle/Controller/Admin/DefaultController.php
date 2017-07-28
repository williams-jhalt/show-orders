<?php

namespace AppBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    /**
     * @Route("/admin", name="admin_homepage")
     */
    public function indexAction() {
        return $this->render('admin/admin.html.twig');
    }

}
