<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\SalesPerson;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * SalesPerson controller.
 *
 * @Route("/admin/salesperson")
 */
class SalesPersonController extends Controller {

    /**
     * Lists all salesperson entities.
     *
     * @Route("/", name="salesperson_index")
     * @Method("GET")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $salespersons = $em->getRepository('AppBundle:SalesPerson')->findAll();

        return $this->render('admin/salesperson/index.html.twig', array(
                    'salespersons' => $salespersons,
        ));
    }

    /**
     * Finds and displays a salesperson entity.
     *
     * @Route("/{id}", name="salesperson_show")
     * @Method("GET")
     */
    public function showAction(SalesPerson $salesPerson) {
        return $this->render('admin/salesperson/show.html.twig', array(
                    'salesperson' => $salesPerson
        ));
    }

    /**
     * Displays a form to edit an existing salesperson entity.
     *
     * @Route("/{id}/edit", name="salesperson_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, SalesPerson $salesPerson) {
        $deleteForm = $this->createDeleteForm($salesPerson);
        $editForm = $this->createForm('AppBundle\Form\SalesPersonType', $salesPerson);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('salesperson_show', array('id' => $salesPerson->getId()));
        }

        return $this->render('admin/salesperson/edit.html.twig', array(
                    'salesperson' => $salesPerson,
                    'edit_form' => $editForm->createView()
        ));
    }

    /**
     * Deletes a salesperson entity.
     *
     * @Route("/{id}", name="salesperson_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, SalesPerson $salesPerson) {
        $form = $this->createDeleteForm($salesPerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($salesPerson);
            $em->flush();
        }

        return $this->redirectToRoute('salesperson_index');
    }

    /**
     * Creates a form to delete a salesperson entity.
     *
     * @param SalesPerson $salesperson The salesperson entity
     *
     * @return Form The form
     */
    private function createDeleteForm(SalesPerson $salesperson) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('salesperson_delete', array('id' => $salesperson->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

}
