<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Customer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Customer controller.
 *
 * @Route("/admin/customer")
 */
class CustomerController extends Controller
{
    /**
     * Lists all customer entities.
     *
     * @Route("/", name="customer_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $customers = $em->getRepository('AppBundle:Customer')->findAll();

        return $this->render('admin/customer/index.html.twig', array(
            'customers' => $customers,
        ));
    }

    /**
     * @Route("/import", name="customer_import")
     * 
     * import file should have the following fields, no header
     * 
     * customerNumber
     * company
     * 
     */
    public function importAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createImportForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $file = $data['importFile']->openFile('r');

            while (!$file->eof()) {
                $row = $file->fgetcsv();

                if (count($row) != 4) {
                    continue;
                }
                
                $customerNumber = trim($row[0]);
                $company = preg_replace('/[[:^print:]]/', '', trim($row[1]));
                $firstName = trim($row[2]);
                $lastName = trim($row[3]);

                $customer = $em->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);
                
                if ($customer === null) {
                    $customer = new Customer();
                    $customer->setCustomerNumber($customerNumber);
                }

                $customer->setCompany($company);
                $customer->setFirstName($firstName);
                $customer->setLastName($lastName);

                $em->persist($customer);
            }

            $em->flush();

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
    public function newAction(Request $request)
    {
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
    public function showAction(Customer $customer)
    {
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
    public function editAction(Request $request, Customer $customer)
    {
        $deleteForm = $this->createDeleteForm($customer);
        $editForm = $this->createForm('AppBundle\Form\CustomerType', $customer);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('customer_edit', array('id' => $customer->getId()));
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
    public function deleteAction(Request $request, Customer $customer)
    {
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
    private function createDeleteForm(Customer $customer)
    {
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
                        ->add('importFile', FileType::class)
                        ->getForm();
    }
}
