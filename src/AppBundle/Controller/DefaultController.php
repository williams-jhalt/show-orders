<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ShowOrder;
use AppBundle\Service\ShowOrderService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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

        $vendors = $this->getDoctrine()->getRepository('AppBundle:Vendor')->findAll();

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
        
        $showAsList = $request->getSession()->get('showAsList', false);
        
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
        
        $response = new Response();

        $response->headers->setCookie(new Cookie('customerNumber', $customerNumber, time() + 31536000));
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
        
        $product = $this->getDoctrine()->getRepository('AppBundle:Product')->find($id);
        
        $service->addProductToCart($request->cookies->get('customerNumber'), $product, $request->get('quantity', 1));
         
        if ($request->isXmlHttpRequest()) {
            return new Response('Added to Cart');
        }
        
        return $this->redirectToRoute('vendor_order_sheet', [
            'id' => $product->getVendor()->getId()
        ]);
    }
    
    /**
     * @Route("/cart", name="cart")
     */
    public function cartAction(Request $request) {        
        
        $customerNumber = $request->cookies->get('customerNumber');
        
        $customer = $this->getDoctrine()->getRepository('AppBundle:Customer')->findOneByCustomerNumber($customerNumber);        
        $order = $this->getDoctrine()->getRepository('AppBundle:ShowOrder')->findOneByCustomer($customer);
               
        
        return $this->render('default/cart.html.twig', [
            'order' => $order
        ]);
        
    }
    
    /**
     * @Route("/update-cart", name="update_cart")
     */
    public function updateCartAction(Request $request, EntityManager $em) {
        
        $cartItems = $request->get('quantity');

        $repo = $em->getRepository('AppBundle:ShowOrderItem');
        
        foreach ($cartItems as $key => $value) {
            $showOrder = $repo->find($key);
            $showOrder->setQuantity($value);
            $em->persist($showOrder);
        }
        
        $em->flush();
        
        return $this->redirectToRoute('cart');
        
    }

}
