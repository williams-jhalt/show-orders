<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Product;
use AppBundle\Entity\Vendor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product controller.
 *
 * @Route("/admin/product")
 */
class ProductController extends Controller {

    /**
     * Lists all product entities.
     *
     * @Route("/", name="product_index")
     * @Method("GET")
     */
    public function indexAction(Request $request) {

        $vendorId = $request->get('vendor', null);

        $em = $this->getDoctrine()->getManager();

        if (!empty($vendorId)) {
            $vendor = $em->getRepository('AppBundle:Vendor')->find($vendorId);
            $products = $em->getRepository('AppBundle:Product')->findByVendor($vendor);
        } else {
            $products = $em->getRepository('AppBundle:Product')->findAll();
        }
        $vendors = $em->getRepository('AppBundle:Vendor')->findAll();

        return $this->render('product/index.html.twig', array(
                    'products' => $products,
                    'vendors' => $vendors
        ));
    }

    /**
     * @Route("/import", name="product_import")
     * 
     * import file should have the following fields, no header
     * 
     * sku
     * name
     * price
     * vendor
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
                
                $sku = trim($row[0]);
                $name = trim($row[1]);
                $price = trim($row[2]);
                $vendorId = trim($row[3]);

                $product = $em->getRepository('AppBundle:Product')->findOneByItemNumber($sku);
                if ($product === null) {
                    $product = new Product();
                    $product->setItemNumber($sku);
                }

                $vendor = $em->getRepository('AppBundle:Vendor')->findOneByVendorNumber($vendorId);
                if ($vendor === null) {
                    $vendor = new Vendor();
                    $vendor->setVendorNumber($vendorId);
                    $vendor->setCompany($vendorId);
                    $em->persist($vendor);
                    $em->flush($vendor);
                }

                $product->setName($name);
                $product->setPrice($price);
                $product->setVendor($vendor);

                $em->persist($product);
            }

            $em->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/import.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="product_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $product = new Product();

        $form = $this->createForm('AppBundle\Form\ProductType', $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', array('id' => $product->getId()));
        }

        return $this->render('product/new.html.twig', array(
                    'product' => $product,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a product entity.
     *
     * @Route("/{id}", name="product_show")
     * @Method("GET")
     */
    public function showAction(Product $product) {

        $deleteForm = $this->createDeleteForm($product);

        return $this->render('product/show.html.twig', array(
                    'product' => $product,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Product $product) {
        $deleteForm = $this->createDeleteForm($product);
        $editForm = $this->createForm('AppBundle\Form\ProductType', $product);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_edit', array('id' => $product->getId()));
        }

        return $this->render('product/edit.html.twig', array(
                    'product' => $product,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product) {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return Form The form
     */
    private function createDeleteForm(Product $product) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
                        ->setMethod('DELETE')
                        ->getForm()
        ;
    }

    private function createImportForm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('product_import'))
                        ->setMethod('POST')
                        ->add('importFile', FileType::class)
                        ->getForm();
    }

}
