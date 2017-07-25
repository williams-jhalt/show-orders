<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ShowOrder;
use AppBundle\Entity\ShowOrderItem;
use AppBundle\Service\ShowOrderService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
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

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('set_customer_number'))
                ->setMethod('POST')
                ->add('customerNumber', TextType::class)
                ->getForm();

        $customer = null;

        if ($request->cookies->has('customerNumber')) {
            $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($request->cookies->get('customerNumber'));
        }

        $vendors = $this->getDoctrine()->getRepository('AppBundle:Vendor')->findBy([], ['company' => 'asc']);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
                    'form' => $form->createView(),
                    'customer' => $customer,
                    'vendors' => $vendors
        ]);
    }
    
    /**
     * @Route("/admin", name="admin_homepage")
     */
    public function adminAction() {
        return $this->render('default/admin.html.twig');
    }
    
    /**
     * @Route("/vendor/{id}", name="vendor_order_sheet")
     */
    public function vendorOrderSheetAction($id, Request $request) {
        
        if ($request->query->has('showAsList')) {
            $request->getSession()->set('showAsList', $request->query->get('showAsList'));
        }
        
        $showAsList = $request->getSession()->get('showAsList', true);
        
        $vendor = $this->getDoctrine()->getRepository('AppBundle:Vendor')->find($id);
        
        return $this->render('default/vendor.html.twig', [
            'vendor' => $vendor,
            'showAsList' => $showAsList
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
     * @Route("/add-to-cart/{id}", name="add_to_cart")
     */
    public function addToCartAction($id, Request $request, ShowOrderService $service) {
        
        $quantity = $request->get('quantity', 1);
        
        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        
        $service->addProductToCart($request->cookies->get('customerNumber'), $product, $quantity);
         
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
    public function cartAction(Request $request) {        
        
        $vendorId = $request->get('vendor');
        
        $customerNumber = $request->cookies->get('customerNumber');
        
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);   
        
        $combinedTotal = 0.0;
        
        foreach ($customer->getShowOrder()->getItems() as $item) {
            $combinedTotal += $item->getProduct()->getPrice() * $item->getQuantity();
        }
        
        if (!empty($vendorId)) {
             
            $vendor = $this->getDoctrine()->getRepository('AppBundle:Vendor')->find($vendorId);
            
            $items = $this->getDoctrine()->getRepository('AppBundle:ShowOrderItem')->findByShowOrderAndVendor($customer->getShowOrder(), $vendor);
            
        } else {
            
            $items = $customer->getShowOrder()->getItems();
            
        }
        
        $currentVendorTotal = 0.0;
        
        foreach ($items as $item) {
            $currentVendorTotal += $item->getProduct()->getPrice() * $item->getQuantity();            
        }
        
        return $this->render('default/cart.html.twig', [
            'showOrder' => $customer->getShowOrder(),
            'items' =>$items,
            'currentVendorTotal' => $currentVendorTotal,
            'combinedTotal' => $combinedTotal
        ]);
        
    }
    
    /**
     * @Route("/update-cart", name="update_cart")
     */
    public function updateCartAction(Request $request, ShowOrderService $service) {
        
        $customerNumber = $request->cookies->get('customerNumber');
        $cartItems = $request->get('quantity');

        $repo = $this->getDoctrine()->getRepository('AppBundle:ShowOrderItem');
        
        foreach ($cartItems as $key => $value) {
            $item = $repo->find($key);
            $service->addProductToCart($customerNumber, $item->getProduct(), $value);
        }
        
        return $this->redirectToRoute('cart');
        
    }
    
    /**
     * @Route("/update-cart/{id}", name="update_cart_inline")
     */
    public function updateCartInlineAction(ShowOrderItem $item, Request $request, ShowOrderService $service) {
        
        $quantity = $request->get('quantity');
        
        $service->addProductToCart($request->cookies->get('customerNumber'), $item->getProduct(), $quantity);
        
        if ($request->isXmlHttpRequest()) {
            return new Response($quantity . ' Added to Cart');
        }        
        
        return $this->redirectToRoute('vendor_order_sheet', [
            'id' => $item->getProduct()->getVendor()->getId()
        ]);
        
    }
    
    /**
     * @Route("/submit-order", name="submit_order")
     */
    public function submitOrderAction(Request $request) {
        
        $customerNumber = $request->cookies->get('customerNumber');
        
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);      
        
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
     * @Route("/delete-cart-item/{id}", name="delete_cart_item")
     */
    public function deleteCartItemAction(ShowOrderItem $item, Request $request) {  
        
        $customerNumber = $request->cookies->get('customerNumber');      
        
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);      
        
        if ($item->getShowOrder()->getCustomer() == $customer) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($item);
            $em->flush();
        }
        
        return $this->redirectToRoute('cart');
        
    }

}
