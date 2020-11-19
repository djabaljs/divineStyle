<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Entity\Order;
use App\Entity\Billing;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Form\BillingType;
use App\Form\CustomerType;
use App\Form\DeliveryType;
use App\Entity\OrderProduct;
use App\Repository\ShopRepository;
use App\Repository\PaymentRepository;
use App\Services\Invoice\InvoiceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\Woocommerce\WoocommerceApiService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_MANAGER")
 * @Route("/user/manager")
 */
class ManagerController extends AbstractController
{
  private $manager;
 
  private $shop;

  private $api;


  public function __construct(EntityManagerInterface $entityManager, Security $security, WoocommerceApiService $apiService)
  {
      $this->manager = $entityManager;
      $this->api = $apiService;
      if(!is_null($security->getUser())){
        $this->shop = $security->getUser()->getShop();
      }
  }

  /**
   * @Route("/dashboard", name="manager_dashboard")
   * @param WoocommerceApiService $apiService
   * @param ShopRepository $shopRepository
   * @return Response
   */
  public function dashboard(): Response
  {
  

        $payments = $this->manager->getRepository(Payment::class)->shopOrdersLastFiveSuccessfully($this->shop);
        
        $deliveriesSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsSuccessfully($this->shop);
        $deliveriesIsNotSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsNotSuccessfully($this->shop);

        
        $deliveries = $this->manager->getRepository(Delivery::class)->shopDeliveries($this->shop);

        $deliveryAmount = 0;
        
        foreach($deliveries as $delivery){
            $deliveryAmount += $delivery->getAmountPaid();
        }

   
        $totalPaid = 0;
        $totalAmount = 0;
        foreach($payments as $payment){
            $totalPaid += $payment->getAmountPaid();
            $totalAmount += $payment->getAmount();
        }

        $shop = $this->manager->getRepository(Shop::class)->find($this->shop);
        return $this->render('manager/dashboard.html.twig', [
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'deliveryIsSuccessfully' => $deliveriesSuccessfully,
            'deliveryIsNotSuccessfully' => $deliveriesIsNotSuccessfully,
            'payments' => $payments,
            'deliveries' => $deliveries,
            'customers' => $shop->getCustomers(),
            'products' => $shop->getProducts()

        ]);
    }
   
   /**
     * @Route("/products/products", name="manager_products", methods={"GET"})
     * @return Response
     */
    public function products()
    {
        return $this->render('manager/products/products/index.html.twig', [
            'products' => $this->manager->getRepository(Product::class)->findBy(['shop' => $this->shop])
        ]);
    }


    /**
     * @Route("/products/show/{slug}", name="manager_products_show", methods={"GET"})
     * @param Product $product
     * @return Response
     */
    public function showProduct(Product $product): Response
    {
        $product = $this->manager->getRepository(Product::class)->find($product);
        return $this->render('manager/products/products/show.html.twig', [
            'product' => $product
        ]);
    }

     /**
     * @Route("/products/categories", name="manager_categories", methods={"GET"})
     * @return Response
     */
    public function categories(): Response
    {
        return $this->render('manager/products/categories/index.html.twig', [
            'categories' => $this->manager->getRepository(Category::class)->getShopProductsQuantity($this->shop)
        ]);
    }

    /**
     * @Route("/products/orders", name="manager_products_orders", methods={"GET"})
     * @method productOrders
     * @return Response
     */
    public function productorders(PaymentRepository $paymentRepository)
    {   

        return $this->render("manager/products/orders/index.html.twig",  [
            'payments' => $paymentRepository->shopOrdersSuccessfully($this->shop)
        ]);
    }

       /**
     * @Route("/products/commands", name="manager_products_commands", methods={"GET"})
     * @method productCommands
     * @return Response
     */
    public function productCommands(PaymentRepository $paymentRepository)
    {   

        return $this->render("manager/products/commands/index.html.twig",  [
            'payments' => $paymentRepository->shopOrdersNotSuccessfully($this->shop)
        ]);
    }

      /**
     * @Route("/products/orders/delete/{id}", name="manager_products_orders_delete", methods={"GET"})
     * @method productOrdersDelete
     * @param Order $order
     * @return Response
     */
    public function productOrdersDelete(Order $order)
    {
        

        $orderProduct = $this->manager->getRepository(OrderProduct::class)->findOneBy(['productOrder' => $order]);

        // if(is_null($orderProduct))
        //     throw $this->createNotFoundException('Cette commande n\'existe pas');

        $this->manager->getConnection()->beginTransaction();
        $this->manager->getConnection()->setAutoCommit(false);

        $order = $this->manager->getRepository(Order::class)->find($order->getId());

        try{
            
        
            // $this->manager->remove($orderProduct);
            $this->manager->remove($order);
            $this->manager->flush();

            $this->manager->commit();

            $this->addFlash("success","Vente supprimée avec succès");

            return $this->redirectToRoute("admin_products_orders");

        }catch(\Exception $e){
            $this->manager->rollback();
            throw $e;
        }
    }


   
     /**
     * @Route("/products/orders/create", name="manager_products_orders_create", methods={"GET", "POST"})
     * @method productOrdersCreate
     * @param Request $request
     * @param SessionInterface $session
     * @param InvoiceService $invoiceService
     * @return Response
     */
    public function productordersCreate(Request $request, SessionInterface $session, InvoiceService $invoiceService): Response
    {
        
        $invoice = $this->manager->getRepository(Invoice::class)->find(26);

        if($request->isXmlHttpRequest()){
           $response = '';
           $code = 0;
           $total = 0;
            if($request->get('action')){

               switch($request->get('action')){
                    case "add":
                        if($request->get('quantity')){
                            $product = $this->manager->getRepository(Product::class)->find($request->get('code'));
                            $itemArray = [
                                $product->getId() =>[
                                    'name'=>$product->getName(), 
                                    'code'=>$product->getId(), 
                                    'quantity'=>$request->get("quantity"), 
                                    'price'=>$product->getSellingPrice()
                                     ]
                                ];
                              
                                if(!empty($session->get("cart_item"))) {
                                    if(in_array($product->getId(), $session->get("cart_item"))) {
                                        foreach($session->get("cart_item") as $k => $v) {
                                                if($product->getId() == $k)
                                                    $session->get("cart_item")[$k]["quantity"] = $request->get("quantity");
                                        }
                                    } else {

                                        $items = $session->get('cart_item', []);
                                        array_push($items, [
                                            'name'=>$product->getName(), 
                                            'code'=>$product->getId(), 
                                            'quantity'=>$request->get("quantity"), 
                                            'price'=>$product->getSellingPrice()
                                        ]);

                                        $session->set('cart_item', $items);

                                        $i = 1;
                                            $response .= '<tr id="element-'.$product->getId().'">';
                                                $response .= '<td>'.$product->getName().'</td>';
                                                $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                                $response .= '<td>'.$request->get('quantity').'</td>';
                                                $response .= '<td><a onClick="cartAction("remove","'.$product->getId().'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                            $response .= '</tr>';
                                        foreach($session->get('cart_item', []) as $v){
                                            $total += $v['price'] * $v['quantity'];
                                        }
                                        
                                    }
                                } else {
                                    $session->set("cart_item", $itemArray);


                                    $i = 1;
                                    $response .= '<tr id="element-'.$product->getId().'">';
                                        $response .= '<td>'.$product->getName().'</td>';
                                        $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                        $response .= '<td>'.$request->get('quantity').'</td>';
                                        $response .= '<td><a onClick="cartAction("remove","'.$product->getId().'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                    $response .= '</tr>';

                                    foreach($session->get('cart_item', []) as $v){
                                        $total += $v['price'] * $v['quantity'];
                                    }
                                }
                        }
                    break;
                    case "remove":
                        if(!empty($session->get("cart_item"))) {
                            foreach($session->get("cart_item") as $k => $v) {
                                    if($request->get("code") == $k){
                                        $code = $request->get("code");
                                        $items = $session->get('cart_item', []);
                                        unset($items[$k]);
                                        $session->set('cart_item', $items);

                                        foreach($session->get('cart_item', []) as $v){
                                            $total += $v['price'] * $v['quantity'];
                                                $response .= '<tr id="element-'.$v['code'].'">';
                                                $response .= '<td>'.$v['name'].'</td>';
                                                $response .= '<td>'.number_format($v['price']).'</td>';
                                                $response .= '<td>'.$v['quantity'].'</td>';
                                                $response .= '<td><a onClick="cartAction("remove","'.$v['code'].'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                                 $response .= '</tr>';
                                        }

                                    }else{
                                        $code = $request->get("code");
                                        $items = $session->get('cart_item', []);
                                        unset($items[$k]);
                                        $session->set('cart_item', $items);

                                        foreach($session->get('cart_item', []) as $v){
                                            $total += $v['price'] * $v['quantity'];

                                            $response .= '<tr id="element-'.$v['code'].'">';
                                            $response .= '<td>'.$v['name'].'</td>';
                                            $response .= '<td>'.number_format($v['price']).'</td>';
                                            $response .= '<td>'.$v['quantity'].'</td>';
                                            $response .= '<td><a onClick="cartAction("remove","'.$v['code'].'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                             $response .= '</tr>';
                                        }

                                    }
                                    if(empty($session->get("cart_item"))){
                                        $items = $session->get('cart_item', []);
                                        unset($items[$k]);
                                        $session->set('cart_item', $items);
                                        $session->set('cart_item', $items);
                                        
                                    }
                            }
                        }
                    break;
                    case "empty":
                        $items = $session->get('cart_item', []);
                        unset($items);
                        $session->set('cart_item', $items);
                    break;		
               }
            }

            return $request->get('action') == 'add' ? new JsonResponse(['status' => 201,'response' => $response, 'total' => number_format($total)]): new JsonResponse(['status' => 200,'response' => $response, 'code' => $code, 'total'=>number_format($total)]);
        }



        $order = new Order();
        $billing = new Billing();
        $invoice = new Invoice();
        $payment = new Payment();

        $today = date("Ymd");
        $rand = strtoupper(substr(uniqid(sha1(time())),0,4));
        $unique = $today . $rand;
        

        $form = $this->createForm(BillingType::class, $billing, ['shop' => $this->shop]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!empty($session->get('cart_item',[]))){

                $this->manager->getConnection()->beginTransaction();
                $this->manager->getConnection()->setAutoCommit(false);

            try{
                $total = 0;

                $order->setNumber('123');
                $order->setShop($this->shop);
                $order->setCustomer($billing->getCustomer());
                $order->setManager($this->getUser());

                foreach($session->get('cart_item', []) as $item){
                    $orderProduct = new OrderProduct();

                    $total += $item['price'] * $item['quantity'];
                    $product = $this->manager->getRepository(Product::class)->find($item['code']);
                    $product->setQuantity($product->getQuantity() > 0 ? $product->getQuantity() - $item['quantity'] : 0);
                    $orderProduct->setProducts($product);
                    $orderProduct->setQuantity($item['quantity']);
                    
                    $order->addOrderProduct($orderProduct);
                }

                $order->setSaleTotal($total);
                $order->setOrderNumber($unique);
                $this->manager->persist($order);

                if($billing->getDeliveryMan()){
                    $delivery = new Delivery();

                    $delivery->setDeliveryMan($billing->getDeliveryMan());
                    $delivery->setAddress($billing->getDeliveryAddress());
                    $delivery->setOrder($order);

                    $this->manager->persist($delivery);
                }

                $rand = strtoupper(substr(uniqid(sha1(time())),0,4));
                $unique = $today . $rand;
                
                $deliveryAmount = 0;
                if(!is_null(($billing->getDeliveryMan()))){
                    
                    $delivery->setOrder($order);
                    $delivery->setDeliveryMan($billing->getDeliveryMan());
                    $delivery->setAddress($billing->getDeliveryAddress());
                    $delivery->setAmountPaid($billing->getDeliveryAmount());
                    $delivery->setStatus(false);
                   
                    if($billing->getChoice() == 0){
                        $delivery->setRecipient($billing->getCustomer());
                        
                        $delivery->setRecipientPhone($billing->getCustomer()->getPhone());
                    }else{
                        $delivery->setRecipient($billing->getRecipient());
                        $delivery->setRecipientPhone($billing->getRecipientPhone());
                    }

                    $this->manager->persist($delivery);

                    $total += $billing->getDeliveryAmount();
                    $deliveryAmount = $billing->getDeliveryAmount();
                }

            
                $invoice->setOrders($order);
                $invoice->setAmount($total);
                $invoice->setInvoiceNumber($unique);

                $this->manager->persist($invoice);

    


                $payment->setInvoice($invoice);
                $payment->setPaymentType($billing->getPaymentType());
                $payment->setAmountPaid($billing->getAmountPaid());
                $payment->setAmount($invoice->getAmount() -($billing->getAmountPaid() + $deliveryAmount));
                $this->manager->persist($payment);

                $quantity = 0;
                foreach($session->get('cart_item',[]) as $item){
                    $productsC = $this->manager->getRepository(Product::class)->findBy(['slug' => $product->getSlug()]);
                    foreach($productsC as $product){
                        $quantity += $product->getQuantity();
                        if($item['code'] == $product->getId()){
                           $product->setQuantity($product->getQuantity());
                           $this->manager->persist($product);
                        }
                    }
                    $productC = clone($product);

                    $productC->setQuantity($quantity);

                    $this->api->putQ('products', $productC);
                }
                
                $this->manager->flush();
                $this->manager->commit();
                $session->remove('cart_item');

                $logo = $request->getUriForPath('/concept/assets/images/logo.jpg');

                if($payment->getAmountPaid() > 0){
                    $invoiceService->generateInvoice($invoice, $logo);
                    $this->addFlash("success", "Vente effectuée avec succès");
                    return $this->redirectToRoute('manager_products_orders_create');
                }else{
                    $this->addFlash("success", "La commande a été enregistré");
                    return $this->redirectToRoute('manager_products_orders_create');

                }

            }catch(\Exception $e){
                $this->manager->rollback();
                throw $e;
            }
                
            }else{
               $this->addFlash("danger","Aucun produit selectionné!");
            }
        }
        
        return $this->render("manager/products/orders/create.html.twig", [
            'products' => $this->manager->getRepository(Product::class)->findBy(['shop' => $this->shop]),
            'form' => $form->createView()
        ]);
    }

      /**
     * @Route("/products/orders/show/{id}", name="manager_products_orders_show", methods={"GET"})
     * @method productOrdersShow
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function productsOrderShow(Order $order)
    {
        $order = $this->manager->getRepository(Order::class)->find($order->getId());

        if(is_null($order))
            throw $this->createNotFoundException('Cette commande n\'existe pas');

            return $this->render("manager/products/orders/show.html.twig", [
                'order' => $order,
                'products' => $this->manager->getRepository(OrderProduct::class)->findBy(['productOrder' => $order])
            ]);
    }

    /**
     * @Route("/products/orders/invoice/show/{id}", name="manager_products_orders_invoice_show", methods={"GET", "POST"})
     * @method productOrdersInvoiceShow
     * @param Invoice $invoice
     * @param InvoiceService $invoiceService
     * @return Response
     */
    public function productsOrdersInvoiceShow(Invoice $invoice, InvoiceService $invoiceService)
    {
        $invoice = $this->manager->getRepository(Invoice::class)->find($invoice->getId());

        if(is_null($invoice))
            throw $this->createNotFoundException('Cette facture n\'existe pas!');

       return $invoiceService->generateInvoice($invoice,'');
    }


      /**
     * @Route("/orders/deliveries", name="manager_orders_deliveries", methods={"GET"})
     * @method ordersDeliveries
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveries()
    {
        return $this->render('manager/products/deliveries/index.html.twig',[
            'deliveries' => $this->manager->getRepository(Delivery::class)->shopDeliveries($this->shop)
        ]);
    }

     /**
     * @Route("/orders/deliveries/create", name="manager_orders_deliveries_create", methods={"GET", "POST"})
     * @method ordersDeliveriesCreate
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesCreate(Request $request)
    {
        $delivery = new Delivery();

        $form = $this->createForm(DeliveryType::class, $delivery, ["shop" => $this->shop]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->manager->persist($delivery);
            $this->manager->flush();

            $this->addFlash("success", "Planification de livraison crée !");
            return  $this->redirectToRoute("manager_orders_deliveries_create");
        }

        return $this->render('manager/products/deliveries/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

         /**
     * @Route("/orders/deliveries/update/{id}", name="manager_orders_deliveries_update", methods={"GET", "POST"})
     * @method ordersDeliveriesUpdate
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesUpdate(Request $request, Delivery $delivery)
    {
        $delivery = $this->manager->getRepository(Delivery::class)->find($delivery);

        if(is_null($delivery)){
            $this->addFlash("danger","Cette livraison n'existe pas!");
            return $this->redirectToRoute('manager_orders_deliveries');
        }


        $form = $this->createForm(DeliveryType::class, $delivery);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->manager->persist($delivery);
            $this->manager->flush();

            $this->addFlash("success", "Planification de livraison modifiée !");
            return  $this->redirectToRoute("manager_orders_deliveries_update", ['id' => $delivery->getId()]);
        }

        return $this->render('manager/products/deliveries/update.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/orders/deliveries/delete/{id}", name="manager_orders_deliveries_delete", methods={"GET"})
     * @method ordersDeliveriesDelete
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesDelete(Delivery $delivery)
    {
        $delivery = $this->manager->getRepository(Delivery::class)->find($delivery);


        if(is_null($delivery)){
            $this->addFlash("danger","Cette livraison n'existe pas!");
            return $this->redirectToRoute('manager_orders_deliveries');
        }

        $this->manager->remove($delivery);
        $this->manager->flush();

        $this->addFlash("success", "Livraison supprimée!");

        return $this->redirectToRoute('manager_orders_deliveries');

    }

    /**
     * @Route("/customers", name="manager_customers", methods={"GET"})
     * @param Exception $e
     * @return Response
     */
    public function customers()
    {
        return $this->render("manager/contacts/customers/index.html.twig", [
            'customers' => $this->manager->getRepository(Customer::class)->findBy(['shops' => $this->shop])
        ]);
    }

        /**
     * @Route("/customers/create", name="manager_customers_create", methods={"POST", "GET"})
     * @param Request
     * @return Response
     */
    public function createCustomer(Request $request)
    {
        $customer = new Customer();

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
             $customer->setShops($this->shop);
            $this->manager->persist($customer);
            $this->manager->flush($customer);

            $this->addFlash("success", "Client enregistré avec succès!");
            
        }

        return $this->render('manager/contacts/customers/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/customers/update/{id}", name="manager_customers_update", methods={"POST", "GET"})
     * @param Request
     * @param Customer $customer
     * @return Response
     */
    public function updateCustomer(Request $request, Customer $customer)
    {
        $customer = $this->getDoctrine()->getRepository(Customer::class)->find($customer);

        if(is_null($customer))
            throw $this->createNotFoundException("Ce client n'existe pas!");

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->manager->persist($customer);
            $this->manager->flush($customer);

            $this->addFlash("success", "Client modifié avec succès!");
            
        }

        return $this->render('manager/contacts/customers/update.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/customers/delete/{id}", name="manager_customers_delete", methods={"GET"})
     * @param Customer $customer
     * @return Response
     */
    public function deleteCustomer(Customer $customer)
    {
        $customer = $this->manager->getRepository(Customer::class)->find($customer);

        if(is_null($customer)){

            $this->addFlash("danger","Ce client n\existe pas!");

            return $this->redirectToRoute('manager_customers');
        }

        $this->manager->remove($customer);
        $this->manager->flush();

        return $this->redirectToRoute('manager_customers');
    }

}