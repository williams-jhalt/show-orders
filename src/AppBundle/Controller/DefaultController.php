<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Customer;
use AppBundle\Entity\Product;
use AppBundle\Entity\ShowOrder;
use AppBundle\Entity\ShowOrderItem;
use AppBundle\Entity\Vendor;
use AppBundle\Service\ShowOrderService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, SessionInterface $session) {

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_homepage');
        }

        if ($this->isGranted('ROLE_SALES')) {
            return $this->redirectToRoute('sales_homepage');
        }

        if ($request->cookies->has('customerNumber')) {
            return $this->redirectToRoute('list_vendors');
        }

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('set_customer_number'))
                ->setMethod('POST')
                ->add('customerNumber', TextType::class)
                ->getForm();

        return $this->render('default/index.html.twig', [
                    'form' => $form->createView()
        ]);
    }
    
    /**
     * @Route("/itinerary", name="itinerary")
     */
    public function itineraryAction() {
        return $this->render('default/itinerary.html.twig');
    }
    
    
    /**
     * @Route("/discounts", name="discounts")
     */
    public function discountsAction() {
        return $this->render('default/discounts.html.twig');
    }
    
    
    /**
     * @Route("/themes", name="themes")
     */
    public function themesAction() {
        return $this->render('default/themes.html.twig');
    }

    /**
     * @Route("/add-note/{vendorId}", name="add_note")
     */
    public function addNoteAction($vendorId, Request $request, ShowOrderService $service) {

        $vendor = $this->getDoctrine()->getRepository('AppBundle:Vendor')->find($vendorId);
        $customerNumber = $request->cookies->get('customerNumber');
        $notes = $request->get('notes');
        $service->addNotes($customerNumber, $vendor->getVendorNumber(), $notes);

        return $this->redirectToRoute('vendor_order_sheet', ['id' => $vendorId]);
    }

    /**
     * @Route("/help", name="help")
     */
    public function helpAction() {

        return $this->render('default/help.html.twig', []);
    }

    /**
     * @Route("/vendor", name="list_vendors")
     */
    public function listVendorsAction() {

        $vendors = $this->getDoctrine()->getRepository('AppBundle:Vendor')->findBy([], ['company' => 'asc']);

        return $this->render('default/vendors.html.twig', [
                    'vendors' => $vendors
        ]);
    }

    /**
     * @Route("/products", name="vendor_order_sheet")
     */
    public function vendorOrderSheetAction(Request $request, ShowOrderService $service) {

        $id = $request->get('id');

        $customerNumber = $request->cookies->get('customerNumber');
        $vendor = $this->getDoctrine()->getRepository('AppBundle:Vendor')->find($id);

        if ($request->query->has('showAsList')) {
            $request->getSession()->set('showAsList', $request->query->get('showAsList'));
        }

        $showAsList = $request->getSession()->get('showAsList', true);

        $vendors = $this->getDoctrine()->getRepository('AppBundle:Vendor')->findBy([], ['company' => 'asc']);
        $newProducts = $this->getDoctrine()->getRepository('AppBundle:Product')->findBy(['vendor' => $vendor, 'bestSeller' => false], ['itemNumber' => 'asc']);
        $bestSellers = $this->getDoctrine()->getRepository('AppBundle:Product')->findBy(['vendor' => $vendor, 'bestSeller' => true], ['itemNumber' => 'asc']);

        $notes = $service->getNotes($customerNumber, $vendor->getVendorNumber());

        return $this->render('default/products.html.twig', [
                    'vendors' => $vendors,
                    'vendor' => $vendor,
                    'newProducts' => $newProducts,
                    'bestSellers' => $bestSellers,
                    'showAsList' => $showAsList,
                    'notes' => $notes
        ]);
    }

    /**
     * @Route("/set-customer", name="set_customer_number")
     */
    public function setCustomerNumber(Request $request) {

        $customerNumber = $request->get('customerNumber');

        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);

        $response = new Response();

        if ($customer) {
            $response->headers->setCookie(new Cookie('customerNumber', $customerNumber, time() + 31536000));
        } else {
            $this->addFlash('notice', 'Customer Not Found');
        }
        $response->setStatusCode(303);
        $response->headers->set('Location', $this->generateUrl('homepage'));

        return $response;
    }

    /**
     * @Route("/clear-customer", name="clear_customer_number")
     */
    public function clearCustomerNumber() {

        $response = new Response();

        $response->headers->clearCookie('customerNumber');
        $response->setStatusCode(303);
        $response->headers->set('Location', $this->generateUrl('homepage'));

        return $response;
    }

    /**
     * @Route("/add-to-cart-manual", name="add_to_cart_manual")
     */
    public function addToCartManual(Request $request, ShowOrderService $service) {

        $itemNumber = $request->get('itemNumber');
        $quantity = $request->get('quantity');
        $customer = $request->cookies->get('customerNumber');

        try {
            $service->addProductToCart($customer, $itemNumber, $quantity);
        } catch (Exception $e) {
            $this->addFlash('notice', $e->getMessage());
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/add-to-cart/{id}", name="add_to_cart")
     */
    public function addToCartAction(Product $product, Request $request, ShowOrderService $service) {

        $quantity = $request->get('quantity', 1);

        $service->addProductToCart($this->customer, $product, $quantity);

        if ($request->isXmlHttpRequest()) {
            return new Response($quantity . ' Added to Cart');
        }

        return $this->redirectToRoute('vendor_order_sheet', [
                    'id' => $product->getVendor()->getId()
        ]);
    }

    /**
     * @Route("/cart", name="cart")
     */
    public function cartAction(Request $request, ShowOrderService $service) {

        $vendorId = $request->get('vendor', null);

        $items = $service->getItems($request->cookies->get('customerNumber'), $vendorId);

        return $this->render('default/cart.html.twig', [
                    'items' => $items
        ]);
    }

    /**
     * @Route("/update-cart", name="update_cart")
     */
    public function updateCartAction(Request $request, ShowOrderService $service) {

        $itemNumber = $request->get('itemNumber');
        $quantity = $request->get('quantity', 1);

        $item = $service->addProductToCart($request->cookies->get('customerNumber'), $itemNumber, $quantity);

        if ($request->isXmlHttpRequest()) {
            return new Response($quantity . ' Added to Cart');
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/submit-order", name="submit_order")
     */
    public function submitOrderAction(Request $request) {


        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($request->cookies->get('customerNumber'));


        $showOrder = $customer->getShowOrder();

        $form = $this->createFormBuilder($showOrder)
                ->add('notes', TextareaType::class, [
                    'required' => false,
                    'attr' => ['style' => 'min-height: 10em;']
                ])
                ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $showOrder->setSubmitted(true);

            $em->persist($showOrder);
            $em->flush();

            return $this->redirectToRoute('cart');
        }

        return $this->render('default/submit.html.twig', [
                    'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete-cart-item", name="delete_cart_item")
     */
    public function deleteCartItemAction(Request $request, ShowOrderService $service) {

        $itemNumber = $request->get('itemNumber');

        $service->removeProductFromCart($request->cookies->get('customerNumber'), $itemNumber);

        if ($request->isXmlHttpRequest()) {
            return new Response($itemNumber . ' Removed from Cart');
        }

        return $this->redirectToRoute('cart');
    }

    /**
     * @Route("/cart-total", name="cart_total")
     */
    public function cartTotalAction(Request $request, ShowOrderService $service) {

        $items = $service->getItems($request->cookies->get('customerNumber'));

        $totals = [];
        $grandTotal = 0.0;

        foreach ($items as $item) {
            $grandTotal += $item->getProduct()->getPrice() * $item->getQuantity();
            if (isset($totals[$item->getProduct()->getVendor()->getVendorNumber()])) {
                $totals[$item->getProduct()->getVendor()->getVendorNumber()]['total'] += $item->getProduct()->getPrice() * $item->getQuantity();
            } else {
                $totals[$item->getProduct()->getVendor()->getVendorNumber()] = [
                    'total' => $item->getProduct()->getPrice() * $item->getQuantity(),
                    'vendor' => $item->getProduct()->getVendor()
                ];
            }
        }

        return $this->render('default/_cart_total.html.twig', [
                    'totals' => $totals,
                    'grand_total' => $grandTotal
        ]);
    }

}
