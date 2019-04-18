<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Vendor;
use AppBundle\Service\ErpConnector;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Vendor controller.
 *
 * @Route("/admin/vendor")
 */
class VendorController extends Controller {

    /**
     * @Route("/import", name="vendor_import")
     */
    public function importAction(Request $request, EntityManager $em, ErpConnector $erp) {

        $form = $this->createImportForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $repo = $em->getRepository(Vendor::class);

            $data = $form->getData();

            $vendorNumbers = preg_split("/[\s,]+/", $data['vendorList']);

            foreach ($vendorNumbers as $vendorNumber) {

                $vendorData = $erp->getVendor($vendorNumber);
                
                if (empty($vendorData)) {
                    continue;
                }

                $vendor = $repo->findOneByVendorNumber($vendorNumber);

                if ($vendor == null) {
                    $vendor = new Vendor();
                }

                $vendor->setVendorNumber($vendorData->vendorNumber);
                $vendor->setCompany($vendorData->name);
                $vendor->setEmail($vendorData->email);

                $em->persist($vendor);
            }

            $em->flush();

            return $this->redirectToRoute('vendor_index');
        }

        return $this->render('admin/vendor/import.html.twig', [
                    'form' => $form->createView()
        ]);
    }
    
    /**
     * Lists all vendor entities.
     *
     * @Route("/", name="vendor_index")
     * @Method("GET")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $vendors = $em->getRepository('AppBundle:Vendor')->findAll();

        return $this->render('admin/vendor/index.html.twig', array(
                    'vendors' => $vendors,
        ));
    }

    /**
     * Creates a new vendor entity.
     *
     * @Route("/new", name="vendor_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) {
        $vendor = new Vendor();

        $existingFilename = $vendor->getImageUrl();

        if (!empty($existingFilename)) {
            $vendor->setImageUrl(new File($this->getParameter('vendor_image_dir') . '/' . $vendor->getImageUrl()));
        }

        $form = $this->createForm('AppBundle\Form\VendorType', $vendor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $vendor->getImageUrl();

            if ($file !== null) {
                $filename = md5(uniqid()) . "." . $file->guessExtension();

                if ($file->move($this->getParameter('vendor_image_dir'), $filename)) {
                    $vendor->setImageUrl($filename);
                }
            } else {
                $vendor->setImageUrl(Vendor::DEFAULT_IMAGE);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($vendor);
            $em->flush();

            return $this->redirectToRoute('vendor_show', array('id' => $vendor->getId()));
        }

        return $this->render('admin/vendor/new.html.twig', array(
                    'vendor' => $vendor,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a vendor entity.
     *
     * @Route("/{id}", name="vendor_show")
     * @Method("GET")
     */
    public function showAction(Vendor $vendor) {
        $deleteForm = $this->createDeleteForm($vendor);

        return $this->render('admin/vendor/show.html.twig', array(
                    'vendor' => $vendor,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing vendor entity.
     *
     * @Route("/{id}/edit", name="vendor_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction($id, Request $request) {

        $vendor = $this->getDoctrine()->getRepository('AppBundle:Vendor')->find($id);

        $existingFilename = $vendor->getImageUrl();

        if (!empty($existingFilename)) {
            $vendor->setImageUrl(new File($this->getParameter('vendor_image_dir') . '/' . $vendor->getImageUrl()));
        }

        $deleteForm = $this->createDeleteForm($vendor);
        $editForm = $this->createForm('AppBundle\Form\VendorType', $vendor);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $file = $vendor->getImageUrl();

            if ($file !== null) {
                $filename = md5(uniqid()) . "." . $file->guessExtension();
                if ($file->move($this->getParameter('vendor_image_dir'), $filename)) {
                    $vendor->setImageUrl($filename);
                }
            } else {
                $vendor->setImageUrl($existingFilename);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($vendor);
            $em->flush();

            return $this->redirectToRoute('vendor_show', array('id' => $vendor->getId()));
        }

        return $this->render('admin/vendor/edit.html.twig', array(
                    'vendor' => $vendor,
                    'existing_image' => $existingFilename,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a vendor entity.
     *
     * @Route("/{id}", name="vendor_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Vendor $vendor) {
        $form = $this->createDeleteForm($vendor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vendor);
            $em->flush();
        }

        return $this->redirectToRoute('vendor_index');
    }

    /**
     * Creates a form to delete a vendor entity.
     *
     * @param Vendor $vendor The vendor entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Vendor $vendor) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('vendor_delete', array('id' => $vendor->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    private function createImportForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('vendor_import'))
                        ->setMethod('POST')
                        ->add('vendorList', TextareaType::class)
                        ->getForm();
    }

}
