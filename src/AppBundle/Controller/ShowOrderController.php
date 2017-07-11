<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ShowOrder;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Showorder controller.
 *
 * @Route("/admin/showorder")
 */
class ShowOrderController extends Controller
{
    /**
     * Lists all showOrder entities.
     *
     * @Route("/", name="showorder_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $showOrders = $em->getRepository('AppBundle:ShowOrder')->findAll();

        return $this->render('showorder/index.html.twig', array(
            'showOrders' => $showOrders,
        ));
    }

    /**
     * Creates a new showOrder entity.
     *
     * @Route("/new", name="showorder_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $showOrder = new Showorder();
        $form = $this->createForm('AppBundle\Form\ShowOrderType', $showOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($showOrder);
            $em->flush();

            return $this->redirectToRoute('showorder_show', array('id' => $showOrder->getId()));
        }

        return $this->render('showorder/new.html.twig', array(
            'showOrder' => $showOrder,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a showOrder entity.
     *
     * @Route("/{id}", name="showorder_show")
     * @Method("GET")
     */
    public function showAction(ShowOrder $showOrder)
    {
        $deleteForm = $this->createDeleteForm($showOrder);

        return $this->render('showorder/show.html.twig', array(
            'showOrder' => $showOrder,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing showOrder entity.
     *
     * @Route("/{id}/edit", name="showorder_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShowOrder $showOrder)
    {
        $deleteForm = $this->createDeleteForm($showOrder);
        $editForm = $this->createForm('AppBundle\Form\ShowOrderType', $showOrder);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('showorder_show', array('id' => $showOrder->getId()));
        }

        return $this->render('showorder/edit.html.twig', array(
            'showOrder' => $showOrder,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a showOrder entity.
     *
     * @Route("/{id}", name="showorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ShowOrder $showOrder)
    {
        $form = $this->createDeleteForm($showOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($showOrder);
            $em->flush();
        }

        return $this->redirectToRoute('showorder_index');
    }

    /**
     * Creates a form to delete a showOrder entity.
     *
     * @param ShowOrder $showOrder The showOrder entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ShowOrder $showOrder)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('showorder_delete', array('id' => $showOrder->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
