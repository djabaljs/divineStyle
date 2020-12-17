<?php

namespace App\Controller;

use App\Entity\Fund;
use App\Entity\Shop;
use App\Entity\Color;
use App\Entity\Order;
use App\Entity\Length;
use App\Form\FundType;
use App\Entity\Billing;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Versement;
use App\Form\BillingType;
use App\Form\SettingType;
use App\Form\CustomerType;
use App\Form\DeliveryType;
use App\Entity\OrderReturn;
use App\Form\VersementType;
use App\Entity\OrderProduct;
use App\Entity\ProductSearch;
use App\Form\OrderReturnType;
use App\Form\ProductSearchType;
use App\Entity\ProductVariation;
use App\Repository\ShopRepository;
use App\Form\OrderReturnUpdateType;
use App\Repository\PaymentRepository;
use App\Services\Invoice\ReturnInvoice;
use App\Services\Invoice\InvoiceService;
use App\Services\Invoice\PrinterInvoice;
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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
  

        $fivePayments = $this->manager->getRepository(Payment::class)->shopOrdersLastFiveSuccessfully($this->shop);
        $payments = $this->manager->getRepository(Payment::class)->findShopPaymentsByWeek($this->shop);
        $deliveriesSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsSuccessfully($this->shop);
        $deliveriesIsNotSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsNotSuccessfully($this->shop);

        $fundOperations = $this->manager->getRepository(Fund::class)->findBy(['manager' => $this->getUser()]);

        $deliveries = $this->manager->getRepository(Delivery::class)->shopDeliveries($this->shop);
        $deliveryAmount = 0;
        
        foreach($deliveries as $delivery){
            $deliveryAmount += $delivery->getAmountPaid();
        }

        $totalPaid = 0;
        $totalAmount = 0;
        foreach($payments as $key => $payment){
            $totalPaid += $payment->getAmountPaid();
            $totalAmount += $payment->getAmount();
        }


        foreach($fundOperations as $fundOperation){
            
            if($fundOperation->getTransactionType()->getId() == 1){
                $totalPaid += $fundOperation->getAmount();
            }elseif($fundOperation->getTransactionType()->getId() == 2){
                $totalPaid -= $fundOperation->getAmount();
            }
        }

        $shop = $this->manager->getRepository(Shop::class)->find($this->shop);

        $orderReturns = $this->manager->getRepository(OrderReturn::class)->findBy(['manager' => $this->getUser()]);

        $orderReturnAmount = 0;
        foreach($orderReturns as $orderReturn){
            $orderReturnAmount += $orderReturn->getAmount();
        }

        $totalPaid += $deliveryAmount;

        return $this->render('manager/dashboard.html.twig', [
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'deliveryIsSuccessfully' => $deliveriesSuccessfully,
            'deliveryIsNotSuccessfully' => $deliveriesIsNotSuccessfully,
            'fivePayments' => $fivePayments,
            'payments' => $payments,
            'deliveries' => $deliveries,
            'customers' => $shop->getCustomers(),
            'products' => $shop->getProducts(),
            'orderReturnAmount' => $orderReturnAmount

        ]);
    }
   
   /**
     * @Route("/products/products", name="manager_products", methods={"GET", "POST"})
     * @return Response
     */
    public function products(Request $request)
    {
        $products = $this->manager->getRepository(Product::class)->findBy(['shop' => $this->shop, 'deleted' => 0]);
        
        $slugArray = [];
        $productArray = [];
 
        foreach($products as $product){
            if(!in_array($product->getSlug(), $slugArray)){
                $slugArray[] = $product->getSlug();
                $productArray[] = $product;
            }
        }
 
         $productsVariation = [];
         $productsNotVariables = [];
         $productSearch = new ProductSearch();
         $form = $this->createForm(ProductSearchType::class, $productSearch, ['products' => $productArray]);
         $form->handleRequest($request);
 
         $isSearch = false;
         if($form->isSubmitted() && $form->isValid()){
            try{
 
 
             $product = $this->manager->getRepository(Product::class)->find($productSearch->getProduct());
 
             if($product->getIsVariable()){
                   $productsVariation = $this->manager->getRepository(ProductVariation::class)->searchProducts($productSearch);
             }else{
                 $productsNotVariables = $this->manager->getRepository(Product::class)->searchProducstNotVariables($productSearch);
             }
             $isSearch = true;
 
            }catch(\Exception $e){
 
            }
 
         }

        return $this->render('manager/products/products/index.html.twig', [
            'products' => $products,
            'productsVariation' => $productsVariation,
            'isSearch' => $isSearch,
            'productsNotVariables' => $productsNotVariables,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/products/show/{id}", name="manager_products_show", methods={"GET"})
     * @param Product $product
     * @return Response
     */
    public function showProduct(Product $product): Response
    {   
        $product = $this->manager->getRepository(Product::class)->find($product);

        if($product->getIsVariable()){
            $product = $this->manager->getRepository(Product::class)->findProductVariations($product->getId(), $this->shop);
        }


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
            'payments' => $paymentRepository->shopAllPayments($this->shop),
            'deliveries' =>  $this->manager->getRepository(Delivery::class)->shopDeliveries($this->shop)
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
    public function productordersCreate(Request $request, SessionInterface $session, InvoiceService $invoiceService, PrinterInvoice $printer): Response
    {

        if($request->isXmlHttpRequest()){
           $response = '';
           $code = 0;
           $total = 0;
           $color = null;
           $length = null;
           $code = 0;
            if($request->get('action')){
            
               switch($request->get('action')){
                    case "add":
                        if($request->get('quantity')){
                            
                            $product = $this->manager->getRepository(Product::class)->findProductVariations($request->get('code'), $this->shop);
                            
                            $color = '';
                            $length = '';

                            if(is_null($product)){
                                 $product = $this->manager->getRepository(Product::class)->findOneBy(['id' => $request->get('code'), 'shop' => $this->shop]);
                                
                                 $color = '//';
                                 $length = '//';
                            }else{

                                $color = $this->manager->getRepository(Color::class)->find($request->get('color'));
                                $length = $this->manager->getRepository(Length::class)->find($request->get('length'));

                                $color = $color->getName();
                                $length = $length->getName();
                            }

                            $itemArray = [
                                $product->getId() =>[
                                    'name'=>$product->getName(), 
                                    'code'=>$product->getId(), 
                                    'quantity'=>$request->get("quantity"), 
                                    'length' => $length,
                                    'color' => $color,
                                    'price'=> $product->getOnSaleAmount() != null ? $product->getOnSaleAmount() : $product->getSellingPrice()
                                     ]
                                ];
                              $code = $product->getId();
                                if(!empty($session->get("cart_item"))) {
                                    if(in_array($product->getId(), $session->get("cart_item"))) {
                                        foreach($session->get("cart_item") as $k => $v) {
                                            if($product->getId() == $k)
                                                $session->get("cart_item")[$k]["quantity"] = $request->get("quantity");
                                                $session->get("cart_item")[$k]["length"] = $color;
                                                $session->get("cart_item")[$k]["color"] = $length;

                                            }
                                    }else{

                                        $items = $session->get('cart_item', []);
                                        array_push($items, [
                                            'name'=>$product->getName(), 
                                            'code'=>$product->getId(), 
                                            'quantity'=>$request->get("quantity"), 
                                            'length' => $length,
                                            'color' => $color,
                                            'price'=> $product->getOnSaleAmount() != null ? $product->getOnSaleAmount() : $product->getSellingPrice()

                                        ]);

                                        $session->set('cart_item', $items);
                                            $response .= '<tr id="element-'.$product->getId().'">';
                                                $response .= '<td>'.$product->getName().'</td>';
                                                $response .= '<td>'.$length.'</td>';
                                                $response .= '<td>'.$color.'</td>';
                                                if(is_null($product->getOnSaleAmount())){
                                                    $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                                   }else{
                                                    $response .= '<td>'.number_format($product->getOnSaleAmount()).'</td>';
                                                   }
                                                $response .= '<td>'.$request->get('quantity').'</td>';
                                                $response .= '<td><button id="btnRemoveAction-'.$request->get('code').'"  class=" btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-trash"></i></button></td>';
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
                                        $response .= '<td>'.$length.'</td>';
                                        $response .= '<td>'.$color.'</td>';
                                        if(is_null($product->getOnSaleAmount())){
                                            $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                           }else{
                                            $response .= '<td>'.number_format($product->getOnSaleAmount()).'</td>';
                                           }
                                        $response .= '<td>'.$request->get('quantity').'</td>';
                                        $response .= '<td><button id="btnRemoveAction-'.$request->get('code').'"  class=" btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-trash"></i></button></td>';
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
                               $color = $this->manager->getRepository(Color::class)->find($request->get('color'));
                               $length = $this->manager->getRepository(Length::class)->find($request->get('length'));
                                    // if($session->get("cart_item")[$k]['code']){

                                        $product = $this->manager->getRepository(Product::class)->findOneBy(['id' => $request->get('code'),'deleted' => 0]);
                                        $color = $this->manager->getRepository(Color::class)->find($request->get('color'));
                                        $length = $this->manager->getRepository(Length::class)->find( $request->get('length'));
                                        

                                       $element = [];
                                       if(!is_null($product) && !is_null($color) && !is_null($length)){
                                        $element = [
                                            "name" => $product->getName(),
                                            "code" => $product->getId(),
                                            "quantity" => $request->get("quantity"),
                                            "color" => $color->getName(),
                                            "length" => $length->getName(),
                                            "price" => $product->getOnSaleAmount() != null ? $product->getOnSaleAmount():  $product->getSellingPrice()
                                        ];
                                       }

                                      if(!empty($element)){

                                            $items = $session->get('cart_item', []);

                                            $elementKey = array_search($element, $items);
                                            
                                            if(count($items) == 1){
                                                $session->remove('cart_item');

                                                foreach($session->get('cart_item', []) as $v){
                                                    $total += $v['price'] * $v['quantity'];
                                                        $response .= '<tr>';
                                                        $response .= '</tr>';
                                                }
                                            }else{

                                            unset($items[$elementKey]);
                                            $session->set('cart_item', $items);
                                        
                                            foreach($session->get('cart_item', []) as $v){
                                                $total += $v['price'] * $v['quantity'];
                                                    $response .= '<tr id="element-'.$v['code'].'">';
                                                    $response .= '<td>'.$v['name'].'</td>';
                                                    $response .= '<td>'.$length.'</td>';
                                                    $response .= '<td>'.$color.'</td>';
                                                    $response .= '<td>'.number_format($v['price']).'</td>';
                                                    $response .= '<td>'.$v['quantity'].'</td>';
                                                    $response .= '<td><button id="btnRemoveAction-'.$v['code'].'"   class=" btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-trash"></i></button></td>';
                                                    $response .= '</tr>';
                                                }
                                            }
                                      }else{
                                        $session->remove('cart_item');
                                        $response .= '<tr>';
                                        $response .= '</tr>';
                                      }

                                    
                                   

                                    // }else{

                                    //     $code = $request->get("code");
                                        
                                    //     $items = $session->get('cart_item', []);
                                    
                                    //     unset($items[$k]);
                                    //     $session->set('cart_item', $items);

                                    //     foreach($session->get('cart_item', []) as $v){
                                    //         $total += $v['price'] * $v['quantity'];

                                    //         $response .= '<tr id="element-'.$v['code'].'">';
                                    //         $response .= '<td>'.$v['name'].'</td>';
                                    //         $response .= '<td>'.$length.'</td>';
                                    //         $response .= '<td>'.$color.'</td>';
                                    //         $response .= '<td>'.number_format($v['price']).'</td>';
                                    //         $response .= '<td>'.$v['quantity'].'</td>';
                                    //         $response .= '<td><button id="btnRemoveAction-'.$v['code'].'"  class=" btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-trash"></i></button></td>';
                                    //         $response .= '</tr>';
                                    //     }

                                    // }

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

            return $request->get('action') == 'add' ? $this->json(['status' => 201,'response' => $response, 'total' => number_format($total), 'code' => $code]): $this->json(['status' => 200,'response' => $response, 'code' => $code, 'total'=> number_format($total)]);
        }



        $order = new Order();
        $billing = new Billing();
        $invoice = new Invoice();
        $payment = new Payment();
        $customer = new Customer();

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

                if($billing->getCustomerType() == 1){
                    $customer->setFirstname($billing->getCustomerFistname());
                    $customer->setLastname($billing->getCustomerLastname());
                    $customer->setPhone($billing->getCustomerPhone());
                    $customer->setEmail($billing->getCustomerEmail());
                    $customer->setDeleted(false);
                    $customer->setBirthDay($billing->getCustomerBirthDay());
                    $customer->setShops($this->shop);
                }else{

                    $customer = $billing->getCustomer();
                }

                $total = 0;
                $variation = null;

                $order->setNumber('123');
                $order->setShop($this->shop);
                $order->setCustomer($billing->getCustomer());
                $order->setManager($this->getUser());
                $productsx = [];
                foreach($session->get('cart_item', []) as $item){

                    $orderProduct = new OrderProduct();

                    $total += $item['price'] * $item['quantity'];

                    $product = $this->manager->getRepository(Product::class)->find($item['code']);

                    if($product->getIsVariable()){
                        $length = $this->manager->getRepository(Length::class)->findOneBy(['name' => $item['length']]);
                        $color = $this->manager->getRepository(Color::class)->findOneBy(['name' => $item['color']]);
                        $productVariation =  $this->manager->getRepository(ProductVariation::class)->findOneBy(['length' => $length, 'color' => $color, 'shop' => $this->shop]);
                        
                        $productVariation->setQuantity($productVariation->getQuantity() - $item['quantity']);
                                            
                        $product = $productVariation->getProduct();

                        $this->manager->persist($productVariation);
                    }

                    
                    $product->setQuantity($product->getQuantity() > 0 ? $product->getQuantity() - $item['quantity'] : 0);
                    $orderProduct->setProducts($product);
                    $orderProduct->setQuantity($item['quantity']);
                    $order->addOrderProduct($orderProduct);
                    
                    $productsx[] = $product;
                }

         
                
                $order->setSaleTotal($total);
                $order->setOrderNumber($unique);

                $customer->addOrder($order);

                $this->manager->persist($order);
                $this->manager->persist($customer);

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
                    $delivery->setDeleted(false);
                    $delivery->setPaymentType($billing->getPaymentType());
                    
                   
                    if($billing->getChoice() == 0){
                        $delivery->setRecipient($customer);
                        
                        $delivery->setRecipientPhone($customer->getPhone());
                    }else{
                        $delivery->setRecipient($billing->getRecipient());
                        $delivery->setRecipientPhone($billing->getRecipientPhone());
                    }

                    $this->manager->persist($delivery);

                    // $total += $billing->getDeliveryAmount();
                    $deliveryAmount = $billing->getDeliveryAmount();
                }

                if(!is_null($billing->getDiscount())){
                    $total = $total - $billing->getDiscount();
                }

            
                $invoice->setOrders($order);
                $invoice->setAmount($total);
                $invoice->setDiscount($billing->getDiscount() != null ? $billing->getDiscount() : 0);
                $invoice->setInvoiceNumber($unique);
              
                $this->manager->persist($invoice);


                $payment->setInvoice($invoice);
                $payment->setPaymentType($billing->getPaymentType());
                $payment->setAmountPaid($total);
                $payment->setStatus(true);
                if(is_null($billing->getDiscount())){
                  $payment->setAmount(0);
                }else{
                    $payment->setAmount(0);
                }

                $this->manager->persist($payment);


                foreach($productsx as $product){

                    $productVariation = $this->manager->getRepository(ProductVariation::class)->findOneBy(['product' => $product]);

                    if(!is_null($productVariation)){
                         $productVariation->setQuantity($product->getQuantity() < 0 ? 0 : $product->getQuantity());

                         $this->manager->persist($productVariation);
                    }
                    $this->manager->persist($product);
                }

                $this->manager->flush();
                $this->manager->commit();

                                
                $sameProducts = [];
                $sameProductsName = [];
                $products = [];

                foreach($session->get('cart_item',[]) as $item){
                    
                    $products = $this->manager->getRepository(Product::class)->findBy(['name' => $item['name']]);
                    foreach($products as $product){

                        if(!in_array($product->getSlug(), $sameProductsName)){
    
                            $sameProductsName[] = $product->getSlug();
    
                            $sameProducts[$product->getSlug()] = $product;
    
                        }else{
                           
                            $productx =  $sameProducts[$product->getSlug()];
    
                            $productx->setQuantity($productx->getQuantity()  + $product->getQuantity());

                            $sameProducts[$product->getSlug()] = $productx;
                        }
                    }
                }


                foreach($sameProducts as $product){
                    if($product->getIsVariable()){
                         
                        $productVariations = $this->manager->getRepository(ProductVariation::class)->findAllProductBySlug($product);
    
                        foreach($productVariations as $variation){
                          $productsVariations['color'][] = $variation->getColor()->getName();
                          $productsVariations['length'][] = $variation->getLength()->getName(); 
                          $productsVariations['variationId'][] = $variation->getVariationId(); 
                          $productsVariations['quantity'][] = $variation->getQuantity(); 
                          $productsVariations['shop'][] = $variation->getShop()->getName(); 
    
                        }
                       $this->api->updateProductVariations($product->getWcProductId(), $product, $productsVariations);
                    }

                    $this->api->putQ('products', $product);
                }



                $session->remove('cart_item');

                $logo = $request->getUriForPath('/concept/assets/images/logo.jpg');

                if($payment->getAmountPaid() > 0){
                    $printer->generateInvoice($invoice, $logo);
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

        $products = $this->manager->getRepository(Product::class)->findBy(['shop' => $this->shop, 'deleted' => 0]);

        $productNames = [];
        $productArray = [];


        $colors = [];
        $lengths = [];

        foreach($products as $product){
            foreach($product->getProductVariations() as $variation){
                if(!in_array($variation->getLength(), $lengths) && !in_array($variation->getColor(), $colors)){
                    $lengths[] = $variation->getLength();
                    $colors[] = $variation->getColor();
                }
            }
        }

        foreach($products as $product){
            if(!in_array($product->getSlug(), $productNames)){
              
                 $product->colorArrays = $colors;
                 $product->lengthArrays = $lengths;

                 $product->setColors($colors);
                 $productNames[] = $product->getSlug();
                 $productArray[$product->getSlug()] = $product;

            }else{
                $productx = $productArray[$product->getSlug()];
                
                $productx->setQuantity($productx->getQuantity() + $product->getQuantity());

                $productArray[$product->getSlug()] = $productx;
            }
        }

        
        return $this->render("manager/products/orders/create.html.twig", [
            'products' => $productArray,
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
    public function productsOrdersInvoiceShow(Invoice $invoice, InvoiceService $invoiceService, PrinterInvoice $printer)
    {
        $invoice = $this->manager->getRepository(Invoice::class)->find($invoice->getId());

        if(is_null($invoice))
            throw $this->createNotFoundException('Cette facture n\'existe pas!');

    //    return $invoiceService->generateInvoice($invoice,'');
    return $printer->generateInvoice($invoice,'');

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

            $delivery->setDeleted(false);
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


        $form = $this->createForm(DeliveryType::class, $delivery, ['shop' => $this->shop]);
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
        $delivery->setDeleted(true);
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
            'customers' => $this->manager->getRepository(Customer::class)->findBy(['shops' => $this->shop, 'deleted' => 0],['createdAt' => 'DESC'])
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

      /**
     * @Route("/reports", name="manager_reports", methods={"GET", "POST"})
     * @method report
     * @param Request $request
     * @return Response
     */
    public function reports(Request $request)
    {
        // // $chart = new Chart();
        // $orderSearch = new OrderSearch();
        // $results = [];

        // $form = $this->createForm(OrderSearchShopType::class, $orderSearch);
        // $form->handleRequest($request);

        // if($form->isSubmitted() && $form->isValid()){
        //     $results = $this->manager->getRepository(Payment::class)->searchPayments($this->shop,$orderSearch->getStart(),$orderSearch->getEnd(),$orderSearch->getPaymentType());
        // }

        return $this->render('manager/reports/index.html.twig', [
            'results' => $this->manager->getRepository(Payment::class)->findShopPaymentsByWeek($this->shop),
            // 'form' => $form->createView(),
            'deliveries' =>  $this->manager->getRepository(Delivery::class)->shopDeliveries($this->shop),
            'operations' => $this->manager->getRepository(Fund::class)->findManagerFundByWeek($this->getUser())
        ]);
    }


    /**
     * @Route("/customer/{id}/orders", name="manager_customer_orders", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function customersOrders(Customer $customer)
    {
         
        return $this->render('manager/contacts/customers/orders.html.twig', [
            'customer' => $this->manager->getRepository(Customer::class)->findOneBy(['id' => $customer, 'deleted' => 0], ['createdAt' => 'DESC']),
            'payments' => $this->manager->getRepository(Payment::class)->customerOrders($customer)
        ]);
    }


     /**
     * @Route("/versements", name="manager_versements", methods={"GET"})
     * @return Response
     */
    public function versements()
    {
        return $this->render('manager/operations/versements/index.html.twig', [
            'versements' => $this->manager->getRepository(Versement::class)->findManagerVersementsByWeek($this->getUser()),
        ]);
    }

     /**
     * @Route("/versements/create", name="manager_versements_create", methods={"GET", "POST"})
     * @return Response
     */
    public function versementsCreate(Request $request)
    {
        $versement = new Versement();

        $form = $this->createForm(VersementType::class, $versement);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $versement->setManager($this->getUser());

            $this->manager->persist($versement);
            $this->manager->flush();

            $this->addFlash("success", "Le versement a été effectué!");

            return $this->redirectToRoute('manager_versements');
        }

        return $this->render('manager/operations/versements/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }



     /**
     * @Route("/fund/operations", name="manager_fund_operations", methods={"GET"})
     * @return Response
     */
    public function fundOperations()
    {
        return $this->render('manager/operations/fund/index.html.twig', [
            'operations' => $this->manager->getRepository(Fund::class)->findBy(['manager' => $this->getUser()]),
        ]);
    }

     /**
     * @Route("/fund/operations/create", name="manager_fund_operations_create", methods={"GET", "POST"})
     * @return Response
     */
    public function fundOperationsCreate(Request $request)
    {
        $operation = new Fund();

        $form = $this->createForm(FundType::class, $operation);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $operation->setManager($this->getUser());

            $this->manager->persist($operation);
            $this->manager->flush();

            $this->addFlash("success", "Opération enregistrée !");

            return $this->redirectToRoute('manager_fund_operations_create');
        }

        return $this->render('manager/operations/fund/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

      /**
     * @Route("/fund/operations/{id}/update", name="manager_fund_operations_update", methods={"GET", "POST"})
     * @return Response
     */
    public function fundOperationsUpdate(Request $request, Fund $operation)
    {

        if(is_null($operation)){
            $this->addFlash("danger", "Cette operation n'existe pas");
        }

        $form = $this->createForm(FundType::class, $operation);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $operation->setManager($this->getUser());

            $this->manager->persist($operation);
            $this->manager->flush();

            $this->addFlash("success", "Opération modifiée !");

            return $this->redirectToRoute('manager_fund_operations_update', ['id' => $operation->getId()]);
        }

        return $this->render('manager/operations/fund/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/fund/operations/{id}/delete", name="manager_fund_operations_delete", methods={"GET"})
     * @return Response
    */
    public function fundOperationsDelete(Fund $operation)
    {
        if(is_null($operation)){
            $this->addFlash("danger", "Cette operation n'existe pas");
        }

        $this->manager->remove($operation);
        $this->manager->flush();

        $this->addFlash('success', 'Opération supprimée!');

        return $this->redirectToRoute("manager_fund_operations");
    }



     /**
     * @Route("/cancel/order/{id}", name="manager_cancel_order", methods={"GET"})
     * @return Response
    */
    public function cancelOrder(Order $order)
    {
       $orderProducts = $order->getOrderProducts();
       $products = $this->manager->getRepository(Product::class)->findAll();
       $invoice = $this->manager->getRepository(Invoice::class)->findOneBy(['orders' => $order]);
       $payment = $this->manager->getRepository(Payment::class)->findOneBy(['invoice' => $invoice]);
       $productsx = [];

       $this->manager->getConnection()->beginTransaction();
       $this->manager->getConnection()->setAutoCommit(false);

       try{
            foreach($orderProducts as $oP){
                foreach($products as $product){
                    if($oP->getProductOrder()->getShop() === $product->getShop()){
                        if($product->getId() === $oP->getProducts()->getId()){
                            $product->setQuantity($product->getQuantity() + $oP->getQuantity());
                            $productsx[] = $product;
                        }
                    }
                }
            }
           
            $payment->setStatus(false);
            $this->manager->persist($payment);


            foreach($productsx as $productx){
                $this->manager->persist($productx);
            }
            $this->manager->flush();
            $this->manager->commit();

            
            $productArray = [];
            $slugArray = [];

            foreach($productsx as $productx){
                $products = $this->manager->getRepository(Product::class)->findBy(['slug' => $productx->getSlug()]);

                foreach($products as $product){
                    if(!in_array($product->getSlug(), $slugArray)){
                        $slugArray[] = $product->getSlug();
                        $productArray[$product->getSlug()] = $product;
    
                    }else{
                        $productx = $productArray[$product->getSlug()];
                        $productx->setQuantity($productx->getQuantity() + $product->getQuantity());
                        $productArray[$product->getSlug()] = $productx;
                    }
                }
            }
          
         
            foreach($productArray as $product){
                $this->api->putQ('products', $product);
                // dd($productArray);
            }


            $this->addFlash("success","Vente annulée!");
            
       }catch(\Exception $e){
            throw $e;
       }
      

       return $this->redirectToRoute('manager_products_orders');
    }

    /**
     * @Route("/order/return", name="manager_orders_return", methods={"GET"})
     * @return Response
    */
    public function ordersReturn()
    {
        return $this->render('manager/products/orders/return/index.html.twig', [
            'orders' =>  $this->manager->getRepository(OrderReturn::class)->findBy(['deleted' => 0],['createdAt' => 'DESC'])
        ]);
    }

    /**
     * @Route("/order/return/create", name="manager_orders_return_create", methods={"GET", "POST"})
     * @return Response
    */
    public function ordersReturnCreate(Request $request)
    {
        $order = new OrderReturn();

        $form = $this->createForm(OrderReturnType::class, $order,  ["shop" => $this->shop]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $order->setManager($this->getUser());

            $order->setAmount($order->getFirstOrder()->getAmountPaid() - $order->getLastOrder()->getAmountPaid());
            if($order->getAmount() <= 0){
              $this->addFlash("warning", "Le total de l'ancienne vente doit est inferieur au total de la nouvelle vente!");
              return $this->redirectToRoute('manager_orders_return_create');
            }
            $order->setDeleted(false);
            $this->manager->persist($order);
            $this->manager->flush();

            $this->addFlash("success", "Retour de marchandise crée!");

            return $this->redirectToRoute("manager_orders_return_create");
        }

        return $this->render('manager/products/orders/return/create.html.twig', [
            'form' => $form->createView()  
        ]);
    }

    /**
     * @Route("/order/return/{id}/update", name="manager_orders_return_update", methods={"GET","POST"})
     * @return Response
    */
    public function ordersReturnUpdate(Request $request, OrderReturn $order)
    {
        if(is_null($order)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas!");
        }

        $form = $this->createForm(OrderReturnUpdateType::class, $order, ['shop' => $this->shop]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $order->setAmount($order->getFirstOrder()->getAmountPaid() - $order->getLastOrder()->getAmountPaid());
            if($order->getAmount() <= 0){
              $this->addFlash("warning", "Le total de l'ancienne vente doit est inferieur au total de la nouvelle vente!");
              return $this->redirectToRoute('manager_orders_return_update', ['id' => $order->getId()]);
            }

            $this->manager->persist($order);
            $this->manager->flush();

            $this->addFlash("success", "Retour de marchandise modifié!");

            return $this->redirectToRoute("manager_orders_return_update", ['id' => $order->getId()]);
        }

        return $this->render('manager/products/orders/return/update.html.twig', [
            'form' => $form->createView()  
        ]);
    }

     /**
     * @Route("/order/return/{id}/delete", name="manager_orders_return_delete", methods={"GET"})
     * @return Response
    */
    public function ordersReturnDelete(OrderReturn $order)
    {
        if(is_null($order)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas!");

            return $this->redirectToRoute("manager_orders_return");

        }
        $order->setDeleted(true);
        $this->manager->persist($order);
        $this->manager->flush();

        $this->addFlash("success", "Retour de marchandise supprimé");

        return $this->redirectToRoute("manager_orders_return");
    }

    /**
     * @Route("/order/return/{id}/success", name="manager_orders_return_success", methods={"GET"})
     * @return Response
    */
    public function ordersReturnSuccess(OrderReturn $orderReturn)
    {
        if(is_null($orderReturn)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas!");

            return $this->redirectToRoute("manager_orders_return");
        }

        $orderReturn->setAmount(0);

        $this->manager->persist($orderReturn);

        $this->manager->flush();

        $this->addFlash("success", "Retour de marchandise réglé");

        return $this->redirectToRoute("manager_orders_return");
    }

    /**
     * @Route("/order/return/invoice/{id}", name="manager_orders_return_invoice", methods={"GET"})
     * @return Response
    */
    public function orderReturnInvoice(OrderReturn $order, ReturnInvoice $returnInvoice)
    {
        if(is_null($order)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas");
            return $this->redirectToRoute("manager_order_return");
        }

        // dd($order);
        $firstInvoice = $order->getFirstOrder()->getInvoice();
        $lastInvoice = $order->getLastOrder()->getInvoice();

        if(!is_null($firstInvoice) && !is_null($lastInvoice)){
            $returnInvoice->generateInvoice($firstInvoice, $lastInvoice);
        }

        return $this->render('manager/products/orders/return/index.html.twig', [
            'orders' =>  $this->manager->getRepository(OrderReturn::class)->findBy([],['createdAt' => 'DESC'])
        ]);
    }


    /**
     * @Route("/setting", name="manager_setting", methods={"GET","POST"})
     * @return Response
    */
    public function setting(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $manager = $this->getUser();

        $form = $this->createForm(SettingType::class, $manager);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           

            $isPasswordValid = $encoder->isPasswordValid($manager, $manager->getOldPassword());
          
           if($isPasswordValid){
            
            $passHass = $encoder->encodePassword($manager, $manager->getNewPassword());

            $manager->setPassword($passHass);
            
            $this->manager->persist($manager);
            $this->manager->flush();

            $this->addFlash("success", "Paramètres modifiés");

            return $this->redirectToRoute("manager_setting");
           }else{
               $this->addFlash("danger", "Mot de passe incorrecte.");
           }
             
           
        }

        return $this->render('manager/setting/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/product/variation/quantity", name="manager_product_variation_quantity", methods={"GET","POST"})
     * @return Response
    */
    public function productVariationQuantity(Request $request)
    {
        $colorKey = $request->get('color');
        $lengthKey = $request->get('length');

        $quantity = 0;
        try{

        $color = $this->manager->getRepository(Color::class)->find($colorKey);
        $length = $this->manager->getRepository(Length::class)->find($lengthKey);

        $variation = $this->manager->getRepository(ProductVariation::class)->findOneBy(['color' => $color, 'length' => $length, 'shop' => $this->shop]);
        
        $quantity = $variation->getQuantity();
        }catch(\Exception $e){
        $quantity =  1;
        }
        return $this->json($quantity);
    }

    /**
     * @Route("/search-product", name="manager_search_product", methods={"POST", "GET"})
     * @return Response
    */
    public function searchProduct(Request $request, SessionInterface $session)
    {
        $searchData = $request->get('searchData');

        $products = $this->manager->getRepository(Product::class)->findProductByNameLike($searchData, $this->shop);
        
        $productNames = [];
        $productArray = [];


        $colors = [];
        $lengths = [];

        foreach($products as $product){
            foreach($product->getProductVariations() as $variation){
                if(!in_array($variation->getLength(), $lengths) && !in_array($variation->getColor(), $colors)){
                    $lengths[] = $variation->getLength();
                    $colors[] = $variation->getColor();
                }
            }
        }

        foreach($products as $product){
            if(!in_array($product->getSlug(), $productNames)){
              
                 $product->colorArrays = $colors;
                 $product->lengthArrays = $lengths;

                 $product->setColors($colors);
                 $productNames[] = $product->getSlug();
                 $productArray[$product->getSlug()] = $product;

            }else{
                $productx = $productArray[$product->getSlug()];
                
                $productx->setQuantity($productx->getQuantity() + $product->getQuantity());

                $productArray[$product->getSlug()] = $productx;
            }
        }

        $html = '';
       foreach($productArray as $key => $product){
        $in_session = 0;
        $id = 0;
        if(!empty($session->get('cart_item', []))){
            $session_code_array = $session->get('cart_item',[])[$key];
            $id = $productArray[$key] - 1;

            if(in_array($id, $session_code_array)){
                $in_session = 1;
            }
        }

        $html .='
        <div class="col-lg-3">
            <div class="card">
                <img class="card-img" src="https://s3.eu-central-1.amazonaws.com/bootstrapbaymisc/blog/24_days_bootstrap/vans.png" alt="Vans">
                <div class="card-body">
                <h4 class="card-title">'.$product->getName().'</h4>
                <h6 class="card-subtitle mb-2 text-muted">Catégorie: '.$product->getCategory().'</h6>
                <h6 class="card-subtitle mb-2 text-muted">En stock: '.$product->getQuantity().'</h6>
                <h6 class="card-subtitle mb-2 text-muted">Tailles: </h6>
                <h6 class="card-subtitle mb-2 text-muted">Couleurs: </h6>
                <p class="card-text">
                <div class="buy d-flex justify-content-between align-items-center">
                    <div class="price text-success"><h5 class="mt-4">'.$product->getSellingPrice().' CFA</div>
                    <button class="btn btn-danger btn-sm btnAdded"id="added_'.$product->getId().'"  $in_session != 1 ?? style="display:none">Ajouté</button>
                </div>
                </div>
            </div>
        </div>
        ';
       }

       return $this->json($html);
    }

}