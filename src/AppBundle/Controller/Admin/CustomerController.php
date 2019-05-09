<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Customer;
use AppBundle\Entity\SalesPerson;
use AppBundle\Service\ErpConnector;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Customer controller.
 *
 * @Route("/admin/customer")
 */
class CustomerController extends Controller {

    /**
     * Lists all customer entities.
     *
     * @Route("/", name="customer_index")
     * @Method("GET")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $customers = $em->getRepository('AppBundle:Customer')->findAll();

        $grandTotal = 0.0;

        foreach ($customers as $customer) {
            $grandTotal += $customer->getShowOrder()->orderTotal();
        }

        return $this->render('admin/customer/index.html.twig', array(
                    'customers' => $customers,
                    'grandTotal' => $grandTotal
        ));
    }

    /**
     * @Route("/import", name="customer_import")
     */
    public function importAction(Request $request, EntityManager $em, ErpConnector $erp) {

        $form = $this->createImportForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $repo = $em->getRepository(Customer::class);
            $spRepo = $em->getRepository(SalesPerson::class);

            $data = $form->getData();

            $customerNumbers = preg_split("/[\s,]+/", $data['customerList']);

            foreach ($customerNumbers as $customerNumber) {

                $customerData = $erp->getCustomer($customerNumber);

                if (empty($customerData)) {
                    continue;
                }

                $customer = $repo->findOneByCustomerNumber(trim($customerNumber));

                if ($customer == null) {
                    $customer = new Customer();
                }

                $customer->setCustomerNumber($customerData->customerNumber);
                $customer->setCompany($customerData->name);
                $customer->setFirstName($customerData->attentionFirstName);
                $customer->setLastName($customerData->attentionLastName);

                $salesPerson = $spRepo->findOneBySalesPersonNumber($customerData->salespersonId);

                if ($salesPerson == null) {

                    $spData = $erp->getSalesPerson($customerData->salespersonId);

                    $salesPerson = new SalesPerson();
                    $salesPerson->setSalesPersonNumber($spData->id);
                    $salesPerson->setName($spData->name);

                    $em->persist($salesPerson);
                    $em->flush($salesPerson);
                }

                $customer->setSalesPerson($salesPerson);

                $em->persist($customer);
                $em->flush();
            }

            return $this->redirectToRoute('customer_index');
        }

        return $this->render('admin/customer/import.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * Creates a new customer entity.
     *
     * @Route("/new", name="customer_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) {
        $customer = new Customer();
        $form = $this->createForm('AppBundle\Form\CustomerType', $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($customer);
            $em->flush();

            return $this->redirectToRoute('customer_show', array('id' => $customer->getId()));
        }

        return $this->render('admin/customer/new.html.twig', array(
                    'customer' => $customer,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a customer entity.
     *
     * @Route("/{id}", name="customer_show")
     * @Method("GET")
     */
    public function showAction(Customer $customer) {
        $deleteForm = $this->createDeleteForm($customer);

        return $this->render('admin/customer/show.html.twig', array(
                    'customer' => $customer,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing customer entity.
     *
     * @Route("/{id}/edit", name="customer_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Customer $customer) {
        $deleteForm = $this->createDeleteForm($customer);
        $editForm = $this->createForm('AppBundle\Form\CustomerType', $customer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('customer_show', array('id' => $customer->getId()));
        }

        return $this->render('admin/customer/edit.html.twig', array(
                    'customer' => $customer,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a customer entity.
     *
     * @Route("/{id}", name="customer_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Customer $customer) {
        $form = $this->createDeleteForm($customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($customer);
            $em->flush();
        }

        return $this->redirectToRoute('customer_index');
    }

    /**
     * Creates a form to delete a customer entity.
     *
     * @param Customer $customer The customer entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Customer $customer) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('customer_delete', array('id' => $customer->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    private function createImportForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('customer_import'))
                        ->setMethod('POST')
                        ->add('customerList', TextareaType::class)
                        ->getForm();
    }

}
