<?php

namespace AppBundle\Controller\Sales;

use AppBundle\Entity\Customer;
use AppBundle\Entity\SalesPerson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Customer controller.
 *
 * @Route("/sales/customer")
 */
class CustomerController extends Controller
{
    /**
     * Lists all customer entities.
     *
     * @Route("/", name="sales_customer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        
        $user = $this->getUser();
        
        $em = $this->getDoctrine()->getManager();

        $customers = $em->getRepository('AppBundle:Customer')->findBy(['salesPerson' => $user->getSalesPerson()]);

        $grandTotal = 0.0;

        foreach ($customers as $customer) {
            $grandTotal += $customer->getShowOrder()->orderTotal();
        }

        return $this->render('sales/customer/index.html.twig', array(
            'customers' => $customers,
            'grandTotal' => $grandTotal
        ));
    }

    /**
     * Finds and displays a customer entity.
     *
     * @Route("/{id}", name="sales_customer_show")
     * @Method("GET")
     */
    public function showAction(Customer $customer) {

        return $this->render('sales/customer/show.html.twig', array(
            'customer' => $customer
        ));
    }

    /**
     * Displays a form to edit an existing customer entity.
     *
     * @Route("/{id}/edit", name="sales_customer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Customer $customer)
    {
        $editForm = $this->createForm('AppBundle\Form\CustomerType', $customer)->remove('salesPerson');
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sales_customer_show', array('id' => $customer->getId()));
        }

        return $this->render('sales/customer/edit.html.twig', array(
            'customer' => $customer,
            'edit_form' => $editForm->createView()
        ));
    }
    
}
