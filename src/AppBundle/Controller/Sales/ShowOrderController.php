<?php

namespace AppBundle\Controller\Sales;

use AppBundle\Entity\ShowOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SplTempFileObject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Showorder controller.
 *
 * @Route("/sales/showorder")
 */
class ShowOrderController extends Controller {

    /**
     * Lists all showOrder entities.
     *
     * @Route("/", name="sales_showorder_index")
     * @Method("GET")
     */
    public function indexAction() {

        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        $customers = $em->getRepository('AppBundle:Customer')->findBy(['salesPerson' => $user->getSalesPerson()]);

        $showOrders = [];

        foreach ($customers as $customer) {
            $showOrders = array_merge($showOrders, $em->getRepository('AppBundle:ShowOrder')->findBy(['customer' => $customer]));
        }

        return $this->render('sales/showorder/index.html.twig', array(
                    'showOrders' => $showOrders,
        ));
    }

    /**
     * Creates a new showOrder entity.
     *
     * @Route("/new", name="sales_showorder_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) {
        $showOrder = new Showorder();
        $form = $this->createForm('AppBundle\Form\ShowOrderType', $showOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($showOrder);
            $em->flush();

            return $this->redirectToRoute('showorder_show', array('id' => $showOrder->getId()));
        }

        return $this->render('sales/showorder/new.html.twig', array(
                    'showOrder' => $showOrder,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a showOrder entity.
     *
     * @Route("/{id}", name="sales_showorder_show")
     * @Method("GET")
     */
    public function showAction(ShowOrder $showOrder) {

        return $this->render('sales/showorder/show.html.twig', array(
                    'showOrder' => $showOrder
        ));
    }

    /**
     * Displays a form to edit an existing showOrder entity.
     *
     * @Route("/{id}/edit", name="sales_showorder_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShowOrder $showOrder) {
        $deleteForm = $this->createDeleteForm($showOrder);
        $editForm = $this->createForm('AppBundle\Form\ShowOrderType', $showOrder);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('showorder_show', array('id' => $showOrder->getId()));
        }

        return $this->render('sales/showorder/edit.html.twig', array(
                    'showOrder' => $showOrder,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a showOrder entity.
     *
     * @Route("/{id}", name="sales_showorder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ShowOrder $showOrder) {
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
     * @return Form The form
     */
    private function createDeleteForm(ShowOrder $showOrder) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('showorder_delete', array('id' => $showOrder->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    /**
     * @Route("/export/{id}", name="sales_showorder_export")
     */
    public function exportAction(ShowOrder $order) {

        $file = new SplTempFileObject();
        $file->fputcsv([
            'sku',
            'name',
            'quantity',
            'vendor'
        ]);

        foreach ($order->getItems() as $item) {
            $file->fputcsv([
                $item->getProduct()->getItemNumber(),
                $item->getProduct()->getName(),
                $item->getQuantity(),
                $item->getProduct()->getVendor()->getVendorNumber()
            ]);
        }

        $file->rewind();

        $response = new StreamedResponse(function() use ($file) {
            foreach ($file as $line) {
                echo $line;
            }
        });
        $dispositionHeader = $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $order->getCustomer()->getCustomerNumber() . "-" . $order->getId() . ".csv"
        );
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

}
