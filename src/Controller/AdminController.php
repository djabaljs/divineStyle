<?php

namespace App\Controller;

use App\Entity\Fund;
use App\Entity\Shop;
use App\Entity\User;
use App\Entity\Color;
use App\Entity\Order;
use App\Entity\Width;
use App\Entity\Height;
use App\Entity\Length;
use App\Form\FundType;
use App\Form\ShopType;
use App\Form\UserType;
use App\Entity\Billing;
use App\Entity\Invoice;
use App\Entity\Payment;
use App\Entity\Product;
use App\Form\ColorType;
use App\Form\WidthType;
use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Delivery;
use App\Entity\Provider;
use App\Form\HeightType;
use App\Form\LengthType;
use App\Entity\Attribute;
use App\Entity\Versement;
use App\Form\BillingType;
use App\Form\ProductType;
use App\Form\SettingType;
use App\Form\CategoryType;
use App\Form\CustomerType;
use App\Form\DeliveryType;
use App\Form\ProviderType;
use Cocur\Slugify\Slugify;
use App\Entity\DeliveryMan;
use App\Entity\OrderReturn;
use App\Entity\OrderSearch;
use App\Entity\PaymentType;
use App\Form\AttributeType;
use App\Form\VersementType;
use App\Entity\OrderProduct;
use App\Form\ShopUpdateType;
use App\Entity\Replenishment;
use App\Form\DeliveryManType;
use App\Form\OrderReturnType;
use App\Form\PaymentTypeType;
use App\Entity\ProviderProduct;
use App\Form\AdminDeliveryType;
use App\Form\AdministratorType;
use App\Form\ProductUpdateType;
use App\Form\ReplenishmentType;
use App\Repository\UserRepository;
use App\Form\OrderSearchByShopType;
use App\Form\SimpleAdminProductType;
use App\Repository\PaymentRepository;
use App\Repository\ProductRepository;
use App\Repository\DeliveryRepository;
use App\Services\Invoice\ReturnInvoice;
use App\Services\Invoice\InvoiceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Services\Woocommerce\WoocommerceApiService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/user/admin")
 */
class AdminController extends AbstractController
{
    private $manager;
    private $api;

    public function __construct(EntityManagerInterface $entityManager, WoocommerceApiService $apiService)
    {
        $this->manager = $entityManager;
        $this->api = $apiService;
    }

    /**
     * @Route("/dashboard", name="admin_dashboard")
     * @param WoocommerceApiService $apiService
     * @throws Exception $e
     * @return Response
     */
    public function dashboard(PaymentRepository $paymentRepository, DeliveryRepository $deliveryRepository): Response
    {


        $payments = $this->manager->getRepository(Payment::class)->findBy(['status' => 1]);
        $deliveriesSuccessfully = $this->manager->getRepository(Delivery::class)->findBy(['status' => 1]);
        $deliveries = $this->manager->getRepository(Delivery::class)->findAll();
        $fundOperations = $this->manager->getRepository(Fund::class)->findAll();
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

        $orderReturns =  $this->manager->getRepository(OrderReturn::class)->findAll();
        
        $orderReturnAmount = 0;

        foreach($orderReturns as $orderReturn){
            $orderReturnAmount += $orderReturn->getAmount();
        }

        return $this->render('admin/dashboard.html.twig', [
            'orders' => $this->manager->getRepository(Order::class)->findLastFiveProducts(),
            'customers' => $this->manager->getRepository(Customer::class)->findAll(),
            'products' => $this->manager->getRepository(Product::class)->findProducts(),
            "amountPaid" => $totalPaid + $deliveryAmount,
            'amount' => $totalAmount,
            'deliveries' => $deliveriesSuccessfully,
            'payments' => $paymentRepository->ordersLastFiveSuccessfully(),
            'deliverySuccessFully' =>  $deliveryRepository->isSuccessFully(),
            'deliveryIsNotSuccessFully' =>  $deliveryRepository->isNotSuccessFully(),
            'orderReturnAmount' => $orderReturnAmount
        ]);

    }

    /**
     * @Route("/products/products", name="admin_products", methods={"GET"})
     * @method products
     */
    public function products(WoocommerceApiService $apiService)
    {
        $products = $this->manager->getRepository(Product::class)->findBy(['deleted' => 0],['createdAt' => 'DESC']);

        
        $sameProductName = [];
        $sameProductArray = [];
        $sameProductQuantity = [];

        foreach($products as $key => $product){
           
            if(!in_array($product->getName(), $sameProductName)){

                $sameProductName[] = $product->getName();
                $sameProductArray[$product->getId()] = $product;

                if(isset($sameProductQuantity[$product->getSlug()])){
                    $sameProductQuantity[$product->getSlug()] += $product->getQuantity();
                }else{
                    $sameProductQuantity[$product->getSlug()] = $product->getQuantity();

                }

            }else{
                if(isset($sameProductQuantity[$product->getSlug()])){
                    $sameProductQuantity[$product->getSlug()] += $product->getQuantity();
                }else{
                    $sameProductQuantity[$product->getSlug()] = $product->getQuantity();

                }
            }

        }

        $products = null;

        foreach($sameProductArray as $product){
           
            if($sameProductQuantity[$product->getSlug()] >= 0){
                $product->setQuantity($sameProductQuantity[$product->getSlug()]);
                $products[] = $product;
            }

        }

        return $this->render('admin/products/products/index.html.twig', [
            'products' =>  $products
        ]);
    }


    /**
     * @Route("/products/create", name="admin_products_create", methods={"POST", "GET"})
     * @param Request $request
     * @return Response
     */
    public function createProduct(Request $request): Response
    {

        $product = new Product();
        $color = new Color();
        $length = new Length();
        
        if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())){
            $form = $this->createForm(ProductType::class, $product);
        }elseif(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
            $form = $this->createForm(SimpleAdminProductType::class, $product);
        }
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
         
            $this->manager->getConnection()->beginTransaction();
            $this->manager->getConnection()->setAutoCommit(false);

            try{
                if($request->get('shopQuantity')){

                        $product->setRegister($this->getUser());
                        $slugify = new Slugify();
                        $product->setSlug($slugify->slugify($product->getName()));
                        
                        $colorArrays = [];
                        $lengthArrays = [];

                        foreach($product->getColors() as  $color){
                            $colorArrays[] = $color->getName();
                        }
                        foreach($product->getLengths() as $key => $length){
                            $lengthArrays[$key] = $length->getName();
                        }

                        if(is_null($product->getOnSaleAmount()) || $product->getOnSaleAmount() == 0.0){
                            $product->setOnSaleAmount(null);
                        }
                        $product->colorArrays = $colorArrays;
                        $product->lengthArrays = $lengthArrays;

                        $totalQuantity = 0;
                        $products = [];
                        foreach($request->get('shopQuantity') as $key => $quantities){
                            foreach($quantities as $quantity){
                                if($key != 0 && $quantity != 0){
                                    $totalQuantity += intval($quantity);
                                    $productC = clone($product);
                                    $shop = $this->manager->getRepository(Shop::class)->find($key);
                                    $productC->setQuantity($quantity);
                                    $productC->setDeleted(false);
                                    $productC->setShop($shop);
                                    $products[] = $productC;
                                }
                            }
                        }

                        $product->setQuantity($totalQuantity);
                
                        $response =  $this->api->post("products", $product);
                        

                        $isVariable = false;
                        if($product->getIsVariable()){

                            $color->addProduct($product);
                            $length->addProduct($product);
                            
                            $this->manager->persist($color);
                            $this->manager->persist($length);
                            $isVariable = true;
                        }

                        
                        try{
                            foreach($products as $product){
                                if($isVariable){
                                  $product->setWcProductId($response['id'] - 1);
                                }else{
                                  $product->setWcProductId($response['id']);
                                }
                                $this->manager->persist($product);
                            }
                        }catch(\Exception $e){
                            foreach($products as $product){
                                if($isVariable){
                                  $product->setWcProductId(null);
                                }else{
                                  $product->setWcProductId(null);
                                }
                                $this->manager->persist($product);
                            }
                        }

                        $this->manager->flush();
                        $this->manager->commit();

                        $this->addFlash("success", "Produit créé et envoyé dans le magasin ".$product->getShop()." avec succès!");

                        return $this->redirectToRoute("admin_products_create");
                }
            }catch(\Exception $e){
                throw $e;
            }
            
        }

        return $this->render('admin/products/products/create.html.twig', [
           'form' => $form->createView(),
           'shops' => $this->manager->getRepository(Shop::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/show/{slug}", name="admin_products_show", methods={"GET"})
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function showProduct(Request $request, Product $product): Response
    {
        $product = $this->manager->getRepository(Product::class)->findOneBy(['slug' => $product->getSlug()]);
        
        if(is_null($product)){
            throw $this->createNotFoundException("Ce produit n'existe pas!");
        }

        return $this->render('admin/products/products/show.html.twig', [
            'product' => $product,
        ]);
    }

     /**
     * @Route("/products/update/{slug}", name="admin_products_update", methods={"POST", "GET"})
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateProduct(Request $request, Product $product): Response
    {   


        if(is_null($product)){
            throw $this->createNotFoundException("Ce produit n'existe pas");
        }
            
        if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles())){
            $form = $this->createForm(ProductType::class, $product);
        }elseif(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
            $form = $this->createForm(SimpleAdminProductType::class, $product);
        }
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
          
            
            if($request->get('shopQuantity')){
               
                $totalQuantity = 0;
                foreach($request->get('shopQuantity') as $key => $quantities){
                        foreach($quantities as $quantity){

                        $totalQuantity += $quantity;

                        if($key != 0){

                            $shop = $this->manager->getRepository(Shop::class)->find($key);

                            $shopProducts = [];

                            foreach($shop->getProducts() as $shopProduct){
                                $shopProducts[] = $shopProduct;
                            }

                            if(!empty($shopProducts)){

                                foreach($shop->getProducts() as $productC){
                        
                                    if($product->getShop() == $productC->getShop()){
    
                                        $slugify = new Slugify();
                        
                                        $productC->setSlug($slugify->slugify($product->getName()));
                                        $productC->setQuantity($quantity);
                                        $productC->setName($product->getName());
                                        $productC->setProvider($product->getProvider());
                                        $productC->setMinimumStock($product->getMinimumStock());
                                        $productC->setSellingPrice($product->getSellingPrice());
                                        $productC->setBuyingPrice($product->getBuyingPrice());
    
                                        if(is_null($product->getOnSaleAmount()) || $product->getOnSaleAmount() == 0.0){
                                            $productC->setOnSaleAmount(null);
                                        }else{
                                            $productC->setOnSaleAmount($product->getOnSaleAmount());
                                        }
    
                                        $this->manager->persist($productC);
                                        $this->manager->flush(); 
    
                                    }
                                    else{
    
                                            $products = $this->manager->getRepository(Product::class)->findAll();
    
                                            foreach($products as $productx){
    
                                            if($productx->getWcProductId() == $product->getWcProductId()){
                                                $slugify = new Slugify();
                                                $productx->setName($product->getName());
                                                $productx->setSlug($slugify->slugify($product->getName()));
                                                $productx->setQuantity($quantity);
                                                $productx->setProvider($product->getProvider());
                                                $productx->setMinimumStock($product->getMinimumStock());
                                                $productx->setSellingPrice($product->getSellingPrice());
                                                $productx->setBuyingPrice($product->getBuyingPrice());
    
                                                if(is_null($product->getOnSaleAmount())){
                                                    $productx->setOnSaleAmount(null);
                                                }else{
                                                    $productx->setOnSaleAmount($product->getOnSaleAmount());
                                                }
    
                                                $this->manager->persist($productx);
                                                $this->manager->flush(); 
                                            }
                                            
                                            }
    
                                    }
                                }
                            }else{

                                $nProduct = new  Product();
                                $nProduct = clone($product);
                                $nProduct->setQuantity($quantity);
                                $shop->addProduct($nProduct);
                                $this->manager->persist($shop);
                                $this->manager->flush();
                            }
                        }
                    }

                }
                
                $product->setQuantity($totalQuantity);
       
                $this->api->put('products',$product);

                $this->addFlash("success", "Produit modifié avec succès!");

                return $this->redirectToRoute("admin_products_update", [
                    "slug" => $product->getSlug(), 
                ]);
            }

        } 

        return $this->render('admin/products/products/update.html.twig', [
            'form' => $form->createView(),
            "product" => $product,
            'shops' => $this->manager->getRepository(Shop::class)->findAll(),
            'update' => $product->getCategory()->getId()

        ]);
    }

     /**
     * @Route("/products/delete/{slug}", name="admin_products_delete", methods={"GET"})
     * @param Request $request
     * @param Product $request
     * @return Response
     */
    public function deleteProduct(Product $product): Response
    {
        $products = $this->manager->getRepository(Product::class)->findBy(['slug' => $product->getSlug()]);

        if(is_null($product)){
            throw $this->createNotFoundException("Ce produit n'existe pas!");
        }

        $this->manager->getConnection()->beginTransaction();
        $this->manager->getConnection()->setAutoCommit(false);
        try{

            foreach($products as $product){
                $product->setDeleted(true);
                $this->manager->flush($product);
            }

            $this->manager->flush();
            $this->manager->commit();

            $this->addFlash("success", "Produit supprimé du magasin ".$product->getShop()." !");
        }catch(\Exception $e){
           throw $e;
        }
        $this->api->delete('products', $product);

        return  $this->redirectToRoute("admin_products");
    }

    /**
     * @Route("/products/categories", name="admin_categories", methods={"GET"})
     * @return Response
     */
    public function categories(): Response
    {
      return  $this->render('admin/products/categories/index.html.twig', [
            'categories' => $this->manager->getRepository(Category::class)->findBy(['deleted' => 0],['createdAt', 'DESC'])
        ]);
    }


    /**
     * @Route("/products/categories/create", name="admin_category_create", methods={"POST", "GET"})
     * @param Request $request
     * @return Response
     */
    public function createCategories(Request $request): Response
    {
        $category = new Category();
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $slugify = new Slugify();
            $category->setSlug($slugify->slugify($category->getName()));
            $category->setRegister($this->getUser());
            $response = $this->api->post("categories", $category);
         
            try{
                $category->setWcCategoryId($response['id']);
                $this->manager->persist($category);
                $this->manager->flush();

                $this->addFlash("success", "Catégorie enregistrée avec succeès!");
                
            }catch(\Exception $e){
                $this->manager->persist($category);
                $this->manager->flush();

                $this->addFlash("success", "Catégorie enregistrée avec succeès!");

            }
          

        }

        return $this->render('admin/products/categories/create.html.twig', [
            'form' => $form->createView() 
        ]);
    }

     /**
     * @Route("/products/categories/{slug}", name="admin_category_show", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function showCategory(Product $product): Response
    {
        dd($product);
        return $this->render('admin/products/categories/show.html.twig', [
        ]);
    }

    /**
     * @Route("/products/categories/update/{slug}", name="admin_category_update", methods={"GET", "POST"})
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function updateCategory(Request $request, Category $category): Response
    {
        $category = $this->manager->getRepository(Category::class)->find($category->getId());

        if(is_null($category))
            throw $this->createNotFoundException("Cette catégorie de produit n'existe pas!");
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $slugify = new Slugify();
            $category->setSlug($slugify->slugify($category->getName()));
            $this->manager->persist($category);
            $this->manager->flush();

            $this->api->put("categories", $category);

            $this->addFlash("success", "Catégorie modifiée avec succès!");

            return $this->redirectToRoute("admin_category_update", ['slug' => $category->getSlug()]);
        }

        return $this->render('admin/products/categories/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/products/categories/delete/{slug}", name="admin_category_delete", methods={"GET"})
     * @param Category $categoryCopy
     * @throws CreateNotFoundException
     * @return Response
     */
    public function deleteCategory(Category $category): Response
    {
        $category = $this->manager->getRepository(Category::class)->findOneBy(['slug' => $category->getSlug()]);


        if(is_null($category))
            throw $this->createNotFoundException("Cette catégorie n'existe pas!");
        
        $this->api->delete("categories", $category);
        
        $category->setDeleted(true);
        $this->manager->persist($category);
        $this->manager->flush();


        $this->addFlash("success","Catégorie supprimée avec succès!");

        return $this->redirectToRoute("admin_categories");
    }


    /**
     * @Route("/customers", name="admin_customers", methods={"GET"})
     * @param Exception $e
     * @return Response
     */
    public function customers()
    {
        return $this->render("admin/contacts/customers/index.html.twig", [
            'customers' => $this->manager->getRepository(Customer::class)->findBy(['deleted' => 0],['createdAt' => 'DESC'])
        ]);
    }

    /**
     * @Route("/customers/create", name="admin_customers_create", methods={"POST", "GET"})
     * @param Request
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function createCustomer(Request $request, WoocommerceApiService $apiService)
    {
        $customer = new Customer();

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->manager->persist($customer);
            $this->manager->flush($customer);

            $this->addFlash("success", "Client enregistré avec succès!");
            
        }

        return $this->render('admin/contacts/customers/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/customers/update/{id}", name="admin_customers_update", methods={"POST", "GET"})
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

        return $this->render('admin/contacts/customers/update.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/customers/delete/{id}", name="admin_customers_delete", methods={"GET"})
     * @param Customer $customer
     * @return Response
     */
    public function deleteCustomer(Customer $customer)
    {
        $customer = $this->manager->getRepository(Customer::class)->find($customer);

        if(is_null($customer)){

            $this->addFlash("danger","Ce client n\existe pas!");

            return $this->redirectToRoute('admin_customers');
        }

        $customer->setDeleted(true);
        $this->manager->persist($customer);
        $this->manager->flush();

        $this->addFlash("success", "Client supprimé!");
        return $this->redirectToRoute('admin_customers');
    }


    /**
     * @Route("/providers", name="admin_providers", methods={"GET"})
     * @param Exception $e
     * @return Response
     */
    public function providers()
    {
        return $this->render("admin/contacts/providers/index.html.twig", [
            'providers' => $this->manager->getRepository(Provider::class)->findBy(['deleted' => 0], ['createdAt' => 'DESC'])
        ]);
    }

    /**
     * @Route("/providers/create", name="admin_providers_create", methods={"POST", "GET"})
     * @param Request
     * @return Response
     */
    public function createProvider(Request $request)
    {
        $provider = new Provider();

        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {   
            $provider->setDeleted(false);
            $this->manager->persist($provider);
            $this->manager->flush($provider);

            $this->addFlash("success", "Fournisseur enregistré avec succès!");
            
        }

        return $this->render('admin/contacts/providers/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/providers/update/{id}", name="admin_providers_update", methods={"POST", "GET"})
     * @param Request
     * @param Provider $provider
     * @return Response
     */
    public function updateProvider(Request $request, Provider $provider)
    {
        $provider = $this->getDoctrine()->getRepository(Provider::class)->find($provider);

        if(is_null($provider))
            throw $this->createNotFoundException("Ce fournisseur  n'existe pas!");

        $form = $this->createForm(ProviderType::class, $provider);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->manager->persist($provider);
            $this->manager->flush($provider);

            $this->addFlash("success", "Fournisseur modifié avec succès!");
            
        }

        return $this->render('admin/contacts/providers/update.html.twig',[
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/providers/delete/{id}", name="admin_providers_delete", methods={"GET"})
     * @param Provider $provider
     * @return Response
     */
    public function deleteProviders(Provider $provider)
    {
        $provider = $this->manager->getRepository(Provider::class)->find($provider);

        if(is_null($provider)){

            $this->addFlash("danger","Ce fournisseur n\existe pas!");

            return $this->redirectToRoute('admin_providers');
        }

        $provider->setDeleted(true);
        $this->manager->persist($provider);
        $this->manager->flush();

        $this->addFlash("success", "Fournisseur supprimé avec succès!");

        return $this->redirectToRoute('admin_providers');
    }


    /**
     * @Route("/providers/{id}/products", name="admin_providers_products", methods={"GET"})
     * @param Exception $e
     * @return Response
     */
    public function providersProducts(Provider $provider)
    {
        return $this->render("admin/contacts/providers/products.html.twig", [
            'products' => $this->manager->getRepository(Product::class)->findBy(['provider' => $provider]),
            'provider' => $provider
        ]);
    }

     /**
     * @Route("/shops", name="admin_shops", methods={"GET"})
     * @return Response
     */
    public function shops()
    {
        return $this->render('admin/shops/index.html.twig',[
            'shops' => $this->manager->getRepository(Shop::class)->findBy(['deleted' => 0],['createdAt' => 'DESC'])
        ]);
    }

    /**
     * @Route("/shops/create", name="admin_shop_create", methods={"POST", "GET"})
     * @param Request
     * @throws Exception
     * @return Response
     */
    public function createShop(Request $request)
    {
        $shop = new Shop();
        $staff = new User();
        
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->manager->getConnection()->beginTransaction();

            $staffs = $form->get('staffs')->getData();
            
            foreach($staffs as $staff){
                $shop->addStaff($staff);
                $staff->setShop($shop);
            }

            try{
                
                $this->manager->persist($shop);
                // $this->manager->persist($staff);
                $this->manager->flush();
                $this->manager->commit();

                $this->addFlash("success", "Magasin crée avec succès!");

            }catch(\Exception $e)
            {
                $this->manager->rollback();

                throw $e;
            }
            
        }

        return $this->render('admin/shops/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/shops/update/{id}", name="admin_shop_update", methods={"POST", "GET"})
     * @param Request
     * @param Shop $shop
     * @throws Exception
     * @return Response
     */
    public function updateShop(Request $request, Shop $shop)
    {
        // dd($shop);
        $datas = [];

        $shop = $this->manager->getRepository(Shop::class)->find($shop->getId());
        $form = $this->createForm(ShopUpdateType::class, $shop);
        $form->handleRequest($request);
        
        if($request->get('data')){
            $datas = explode(',',$request->get('data')[0]);
            
         }
         


        if($form->isSubmitted() && $form->isValid())
        {
            $this->manager->getConnection()->beginTransaction();

            $staffs = $form->get('staffs')->getData();
        
            try{

                foreach($datas as $data){
                    $staff = $this->manager->getRepository(User::class)->find($data);

                if(!is_null($staff)){
                    if(!is_null($staff->getShop())){
                        $shop->addStaff($staff);
                        $staff->setShop($shop);
                        $this->manager->persist($shop);
                        $this->manager->persist($staff);
                    }
                  }
                }

                foreach($staffs as $staff){
                   

                    if(!is_null($staff->getShop())){

                        $shop->removeStaff($staff);
                        $staff->setShop(null);
                        $this->manager->persist($shop);
                        $this->manager->persist($staff);
                        
                    }else{

                        $shop->addStaff($staff);
                        $staff->setShop($shop);
                        $this->manager->persist($shop);
                        $this->manager->persist($staff);
                    }
    
                }

                $this->manager->flush();
                $this->manager->commit();

                $this->addFlash("success", "Magasin modifié avec succès!");

            }catch(\Exception $e)
            {
                $this->manager->rollback();

                throw $e;
            }
            
        }

        return $this->render('admin/shops/update.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/shops/remove/{id}", name="admin_shop_remove", methods={"GET"})
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function removeShop(Request $request, Shop $shop)
    {
        $shop = $this->manager->getRepository(Shop::class)->find($shop);

        if (!$shop) {
            $this->addFlash("danger", "Ce magasin n'existe pas");
            $this->redirectToRoute('admin_shops');
        }

        $shop->setDeleted(true);
        $this->manager->persist($shop);
        $this->manager->flush();

        $this->addFlash("success", "Magasin supprimé");

        return $this->redirectToRoute('admin_shops');
    }



     /**
     * @Route("/staffs", name="admin_staffs", methods={"GET"})
     * @param UserRepository $userRespository
     * @return Response
     */

    public function staffs(UserRepository $userRepository)
    {
        $staffs = $userRepository->findStaffs();

        return $this->render('admin/contacts/staffs/index.html.twig',[
            'staffs' => $staffs
        ]);
    }


     /**
     * @Route("/staffs/create", name="admin_staff_create", methods={"GET", "POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */

    public function createStaff(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $staff = new User();

        $form = $this->createForm(UserType::class, $staff);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $passwordHash = $passwordEncoder->encodePassword($staff, '123456');
            $staff->setPassword($passwordHash);
            $staff->setRoles(["ROLE_STAFF"]);
            $this->manager->persist($staff);
            $this->manager->flush();

            $this->addFlash("success", "Personnel enregistré!");

            $this->redirectToRoute('admin_staffs');
        }

        return $this->render('admin/contacts/staffs/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/staffs/update/{id}", name="admin_staff_update", methods={"GET", "POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param User $staff
     * @return Response
     */

    public function updateStaff(Request $request, UserPasswordEncoderInterface $passwordEncoder, User $staff)
    {
        $form = $this->createForm(UserType::class, $staff);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $passwordHash = $passwordEncoder->encodePassword($staff, '123456');
            $staff->setPassword($passwordHash);
            $this->manager->persist($staff);
            $this->manager->flush();

            $this->addFlash("success", "Personnel modifié!");

        }

        return $this->render('admin/contacts/staffs/update.html.twig',[
            'form' => $form->createView()
        ]);
    }

      /**
     * @Route("/staffs/remove/{id}", name="admin_staff_remove", methods={"GET"})
     * @param Request $request
     * @param Shop $shop
     * @return Response
     */
    public function removeStaff(Request $request, User $staff)
    {
        $staff = $this->manager->getRepository(User::class)->find($staff);

        if (!$staff) {
            $this->addFlash("danger", "Personnel n'existe pas");
            $this->redirectToRoute('admin_shops');
        }

        $this->manager->remove($staff);
        $this->manager->flush();

        $this->addFlash("success", "Personnel supprimé");

        return $this->redirectToRoute('admin_staffs');
    }

    /**
     * @Route("/products/options/colors", name="admin_products_colors", methods={"GET"})
     * @method productsColors
     * @return Response
     */
    public function productsColors()
    {
        return $this->render("admin/options/colors/index.html.twig", [
            'colors' => $this->manager->getRepository(Color::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/options/colors/create", name="admin_products_colors_create", methods={"GET", "POST"})
     * @method productsColorsCreate
     * @param Request $request
     * @return Response
     */
    public function productsColorsCreate(Request $request)
    {
        $color = new Color();

        $form = $this->createForm(ColorType::class, $color);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $color->setRegister($this->getUser());
            $this->manager->persist($color);
            $this->manager->flush();

            $this->addFlash("success", "Coleur de produit crée avec succès!");
            return $this->redirectToRoute("admin_products_colors_create");
        }
        return $this->render("admin/options/colors/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/colors/update/{id}", name="admin_products_colors_update", methods={"GET", "POST"})
     * @method productsColorsCreate
     * @param Request $request
     * @param Color $color
     * @return Response
     */
    public function productsColorsUpdate(Request $request, Color $color)
    {
        $color = $this->manager->getRepository(Color::class)->find($color->getId());

        $form = $this->createForm(ColorType::class, $color);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($color);
            $this->manager->flush();

            $this->addFlash("success", "Coleur de produit modifiée avec succès!");
            return $this->redirectToRoute("admin_products_colors_update", ['id' => $color->getId()]);
        }
        return $this->render("admin/options/colors/update.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/colors/delete/{id}", name="admin_products_colors_delete", methods={"GET"})
     * @method productsColorsDelete
     * @param Color $color
     * @return Response
     */
    public function productsColorsDelete(Color $color): Response
    {
        $color = $this->manager->getRepository(Color::class)->find($color->getId());

        if(is_null($color))
            throw $this->createNotFoundException("Cette coleur de produit n'existe pas!");
        
        $this->manager->remove($color);
        $this->manager->flush();

        $this->addFlash("success", "Couleur de produit supprimée avec succès!");

        return $this->redirectToRoute("admin_products_colors");
       
    }




    /**
     * @Route("/products/options/lengths", name="admin_products_lengths", methods={"GET"})
     * @method productsLenghts
     * @return Response
     */
    public function productsLenghts()
    {
        return $this->render("admin/options/lengths/index.html.twig", [
            'lengths' => $this->manager->getRepository(Length::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/options/lengths/create", name="admin_products_lengths_create", methods={"GET", "POST"})
     * @method productsLenghtsCreate
     * @param Request $request
     * @return Response
     */
    public function productsLenghtsCreate(Request $request)
    {
        $length = new Length();

        $form = $this->createForm(LengthType::class, $length);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $length->setRegister($this->getUser());
            $this->manager->persist($length);
            $this->manager->flush();

            $this->addFlash("success", "Taille de produit crée avec succès!");
            return $this->redirectToRoute("admin_products_lengths_create");
        }
        return $this->render("admin/options/lengths/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/lengths/update/{id}", name="admin_products_lengths_update", methods={"GET", "POST"})
     * @method productsLenghtsUpdate
     * @param Request $request
     * @param Length $length
     * @return Response
     */
    public function productsLenghtsUpdate(Request $request, Length $length)
    {
        $length = $this->manager->getRepository(Length::class)->find($length->getId());

        $form = $this->createForm(LengthType::class, $length);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($length);
            $this->manager->flush();

            $this->addFlash("success", "Taille de produit modifiée avec succès!");
            return $this->redirectToRoute("admin_products_lengths_update", ['id' => $length->getId()]);
        }
        return $this->render("admin/options/lengths/update.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/lengths/delete/{id}", name="admin_products_lengths_delete", methods={"GET"})
     * @method productsLengthsDelete
     * @param Length $length
     * @return Response
     */
    public function productsLengthsDelete(Length $length): Response
    {
        $length = $this->manager->getRepository(Length::class)->find($length->getId());

        if(is_null($length))
            throw $this->createNotFoundException("Cette taille de produit n'existe pas!");
        
        $this->manager->remove($length);
        $this->manager->flush();

        $this->addFlash("success", "Taille de produit supprimée avec succès!");

        return $this->redirectToRoute("admin_products_lengths");
       
    }


           
    /**
     * @Route("/payment-types", name="admin_payment_types", methods={"GET"})
     * @method paymentTypes
     * @return Response
     */
    public function paymentTypes()
    {
        return $this->render('admin/options/payment_types/index.html.twig', [
            'paymentTypes' => $this->manager->getRepository(PaymentType::class)->findBy(['deleted' => 0],['createdAt' => 'DESC'])
        ]);
    }

       
    /**
     * @Route("/payment-types/create", name="admin_payment_types_create", methods={"GET", "POST"})
     * @method paymentTypesCreate
     * @return Response
     */
    public function paymentTypesCreate(Request $request)
    {
        $paymentType = new PaymentType();

        $form = $this->createForm(PaymentTypeType::class, $paymentType);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($paymentType);
            $this->manager->flush();

            $this->addFlash("success", "Méthode de paiement crée avec succès:!");

            return $this->redirectToRoute('admin_payment_types_create');
        }
        return $this->render('admin/options/payment_types/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

        /**
     * @Route("/payment-types/update/{id}", name="admin_payment_types_update", methods={"GET", "POST"})
     * @method paymentTypesUpdate
     * @return Response
     */
    public function paymentTypesUpdate(Request $request, PaymentType $paymentType)
    {
       $paymentType = $this->manager->getRepository(PaymentType::class)->find($paymentType->getId());

       if(is_null($paymentType))
            throw $this->createNotFoundException('Ce type de paiement n\'existe pas!');

        $form = $this->createForm(PaymentTypeType::class, $paymentType);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($paymentType);
            $this->manager->flush();

            $this->addFlash("success", "Méthode de paiement modifiée avec succès!");

        }
        return $this->render('admin/options/payment_types/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

       /**
     * @Route("/payment-types/delete/{id}", name="admin_payment_types_delete", methods={"GET"})
     * @method paymentTypesDelete
     * @return Response
     */
    public function paymentTypesDelete(Request $request, PaymentType $paymentType)
    {
        $paymentType = $this->manager->getRepository(PaymentType::class)->find($paymentType->getId());

        if(is_null($paymentType))
             throw $this->createNotFoundException('Ce type de paiement n\'existe pas!');

        $paymentType->setDeleted(true);
        $this->manager->flush($paymentType);
        $this->manager->flush();

        $this->addFlash("success", "Méthode de paiement supprimé avec succès");

        return $this->redirectToRoute("admin_payment_types");
    }



               
    /**
     * @Route("/delivery-mans", name="admin_delivery_mans", methods={"GET"})
     * @method deliveryMans
     * @return Response
     */
    public function deliveryMans()
    {
        return $this->render('admin/contacts/delivery_mans/index.html.twig', [
            'mans' => $this->manager->getRepository(DeliveryMan::class)->findBy(['deleted' => 0], ['id' => 'DESC'])
        ]);
    }

       
    /**
     * @Route("/delivery-mans/create", name="admin_delivery_mans_create", methods={"GET", "POST"})
     * @method paymentTypesCreate
     * @return Response
     */
    public function deliveryMansCreate(Request $request)
    {
        $man = new DeliveryMan();

        $form = $this->createForm(DeliveryManType::class, $man);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($man);
            $this->manager->flush();

            $this->addFlash("success", "Livreur  crée avec succès!");

            return $this->redirectToRoute('admin_delivery_mans_create');
        }
        return $this->render('admin/contacts/delivery_mans/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

        /**
     * @Route("/delivery_mans/update/{id}", name="admin_delivery_mans_update", methods={"GET", "POST"})
     * @method deliveryMansUpdate
     * @return Response
     */
    public function deliveryMansUpdate(Request $request, DeliveryMan $man)
    {
       $man = $this->manager->getRepository(DeliveryMan::class)->find($man->getId());

       if(is_null($man))
            throw $this->createNotFoundException('Ce livreur n\'existe pas!');

        $form = $this->createForm(DeliveryManType::class, $man);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($man);
            $this->manager->flush();

            $this->addFlash("success", "Livreur modifié avec succès!");

        }
        return $this->render('admin/contacts/delivery_mans/update.html.twig', [
            'form' => $form->createView()
        ]);
    }

       /**
     * @Route("/delivery-mans/delete/{id}", name="admin_delivery_mans_delete", methods={"GET"})
     * @method deliveryManDelete
     * @return Response
     */
    public function deliveryManDelete(Request $request, DeliveryMan $man)
    {
        $man = $this->manager->getRepository(DeliveryMan::class)->find($man->getId());

        if(is_null($man))
             throw $this->createNotFoundException('Ce livreur  n\'existe pas!');

        $man->setDeleted(true);
        $this->manager->flsuh($man);
        $this->manager->flush();

        $this->addFlash("success", "Livreur supprimé avec succès");

        return $this->redirectToRoute("admin_delivery_mans");
    }

    /**
     * @Route("/delivery-mans/{id}/deliveries", name="admin_delivery_mans_deliveries", methods={"GET"})
     * @method deliveryMansDeliveries
     * @return Response
     */
    public function deliveryMansDeliveries(DeliveryMan $deliveryMan)
    {
        return $this->render('admin/contacts/delivery_mans/orders.html.twig', [
            'deliveries' => $this->manager->getRepository(Delivery::class)->findBy(['deliveryMan' => $deliveryMan, 'deleted' => 0],['createdAt' => 'DESC']),
            'mans' => $deliveryMan
        ]);
    }


    /**
     * @Route("/products/options/widths", name="admin_products_widths", methods={"GET"})
     * @method productsWidths
     * @return Response
     */
    public function productsWidths()
    {
        return $this->render("admin/options/widths/index.html.twig", [
            'widths' => $this->manager->getRepository(Width::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/options/widths/create", name="admin_products_widths_create", methods={"GET", "POST"})
     * @method productsWidthsCreate
     * @param Request $request
     * @return Response
     */
    public function productsWidthsCreate(Request $request)
    {
        $width = new Width();

        $form = $this->createForm(WidthType::class, $width);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $width->setRegister($this->getUser());
            $this->manager->persist($width);
            $this->manager->flush();

            $this->addFlash("success", "Largeur de produit crée avec succès!");
            return $this->redirectToRoute("admin_products_widths_create");
        }
        return $this->render("admin/options/widths/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/widths/update/{id}", name="admin_products_widths_update", methods={"GET", "POST"})
     * @method productsLenghtsUpdate
     * @param Request $request
     * @param Width $width
     * @return Response
     */
    public function productsWidthsUpdate(Request $request, Width $width)
    {
        $width = $this->manager->getRepository(Width::class)->find($width->getId());

        $form = $this->createForm(WidthType::class, $width);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($width);
            $this->manager->flush();

            $this->addFlash("success", "Largeur de produit modifiée avec succès!");
            return $this->redirectToRoute("admin_products_widths_update", ['id' => $width->getId()]);
        }
        return $this->render("admin/options/widths/update.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/widths/delete/{id}", name="admin_products_widths_delete", methods={"GET"})
     * @method productsLengthsDelete
     * @param Width $width
     * @return Response
     */
    public function productsWidthsDelete(Width $width): Response
    {
        $width = $this->manager->getRepository(Width::class)->find($width->getId());

        if(is_null($width))
            throw $this->createNotFoundException("Cette largeur de produit n'existe pas!");
        
        $this->manager->remove($width);
        $this->manager->flush();

        $this->addFlash("success", "Largeur de produit supprimée avec succès!");

        return $this->redirectToRoute("admin_products_widths");
       
    }



    /**
     * @Route("/products/options/heights", name="admin_products_heights", methods={"GET"})
     * @method productsHeights
     * @return Response
     */
    public function productsHeights()
    {
        return $this->render("admin/options/heights/index.html.twig", [
            'heights' => $this->manager->getRepository(Height::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/options/heights/create", name="admin_products_heights_create", methods={"GET", "POST"})
     * @method productsHeightsCreate
     * @param Request $request
     * @return Response
     */
    public function productsHeightsCreate(Request $request)
    {
        $height = new Height();

        $form = $this->createForm(HeightType::class, $height);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $height->setRegister($this->getUser());
            $this->manager->persist($height);
            $this->manager->flush();

            $this->addFlash("success", "Hauteur de produit crée avec succès!");
            return $this->redirectToRoute("admin_products_heights_create");
        }
        return $this->render("admin/options/heights/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/heights/update/{id}", name="admin_products_heights_update", methods={"GET", "POST"})
     * @method productsHeightsUpdate
     * @param Request $request
     * @param Height $height
     * @return Response
     */
    public function productsHeightsUpdate(Request $request, Height $height)
    {
        $height = $this->manager->getRepository(Height::class)->find($height->getId());

        $form = $this->createForm(HeightType::class, $height);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $this->manager->persist($height);
            $this->manager->flush();

            $this->addFlash("success", "Largeur de produit modifiée avec succès!");
            return $this->redirectToRoute("admin_products_heights_update", ['id' => $height->getId()]);
        }
        return $this->render("admin/options/heights/update.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/options/heights/delete/{id}", name="admin_products_heights_delete", methods={"GET"})
     * @method productsHeightssDelete
     * @param Height $height
     * @return Response
     */
    public function productsHeightsDelete(Height $height): Response
    {
        $height = $this->manager->getRepository(Width::class)->find($height->getId());

        if(is_null($height))
            throw $this->createNotFoundException("Cette Hauteur de produit n'existe pas!");
        
        $this->manager->remove($height);
        $this->manager->flush();

        $this->addFlash("success", "Hauteur de produit supprimée avec succès!");

        return $this->redirectToRoute("admin_products_heights");
       
    }

      /**
     * @Route("/products/attributes", name="admin_products_attributes", methods={"GET"})
     * @method productsAttributes
     * @return Response
     */
    public function productsAttributes()
    {
        return $this->render("admin/products/attributes/index.html.twig", [
            'attributes' => $this->manager->getRepository(Attribute::class)->findAll()
        ]);
    }

    /**
     * @Route("/products/attributes/create", name="admin_products_attributes_create", methods={"GET", "POST"})
     * @method productsAttributesCreate
     * @param Request $request
     * @return Response
     */
    public function productsAttributesCreate(Request $request)
    {
        $attribute = new Attribute();

        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $attribute->setRegister($this->getUser());
           
            $options = explode(',',$attribute->getText());
            $attribute->setOptions($options);
            
            $slugify = new Slugify([ 'separator' => '_' ]);
            $attribute->setSlug($slugify->slugify('pa'.$attribute->getName()));
            $this->manager->persist($attribute);
            $this->manager->flush();

            $this->addFlash("success", "Attribut de produit crée avec succès!");
            return $this->redirectToRoute("admin_products_attributes_create");
        }
        return $this->render("admin/products/attributes/create.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/attributes/update/{id}", name="admin_products_attributes_update", methods={"GET", "POST"})
     * @method productsAttributesUpdate
     * @param Request $request
     * @param Attribute $attribute
     * @return Response
     */
    public function productsAttributesUpdate(Request $request, Attribute $attribute)
    {
        $attribute = $this->manager->getRepository(Attribute::class)->find($attribute->getId());

        $text = '';

        for($i = 0; $i <= count($attribute->getOptions()) - 1; $i++){
            $text .= $attribute->getOptions()[$i];
            $text .= ',';
        }

        $text = substr($text, 0, -1);
    
        $attribute->text = $text;

        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $options = explode(',',$attribute->getText());
            $attribute->setOptions($options);
             
            $slugify = new Slugify([ 'separator' => '_' ]);
            $attribute->setSlug($slugify->slugify('pa '.$attribute->getName()));
            $this->manager->persist($attribute);
            $this->manager->flush();

            $this->addFlash("success", "Attribut de produit modifiée avec succès!");
            return $this->redirectToRoute("admin_products_attributes_update", ['id' => $attribute->getId()]);
        }
        return $this->render("admin/products/attributes/update.html.twig", [
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/products/attributes/delete/{id}", name="admin_products_attributes_delete", methods={"GET"})
     * @method productsAttributesDelete
     * @param Attribute $attribute
     * @return Response
     */
    public function productsAttributesDelete(Attribute $attribute): Response
    {
        $attribute = $this->manager->getRepository(Width::class)->find($attribute->getId());

        if(is_null($attribute))
            throw $this->createNotFoundException("Cet attribut de produit n'existe pas!");
        
        $this->manager->remove($attribute);
        $this->manager->flush();

        $this->addFlash("success", "Attribut de produit supprimé avec succès!");

        return $this->redirectToRoute("admin_products_attributes");
       
    }


    /**
     * @Route("/products/orders", name="admin_products_orders", methods={"GET"})
     * @method productOrders
     * @return Response
     */
    public function productorders(PaymentRepository $paymentRepository)
    {   

        return $this->render("admin/products/orders/index.html.twig",  [
            'payments' => $paymentRepository->ordersSuccessfully()
        ]);
    }

      /**
     * @Route("/products/commands", name="admin_products_commands", methods={"GET"})
     * @method productCommands
     * @return Response
     */
    public function productCommands(PaymentRepository $paymentRepository)
    {   

        return $this->render("admin/products/commands/index.html.twig",  [
            'payments' => $paymentRepository->ordersNotSuccessfully()
        ]);
    }


     /**
     * @Route("/products/orders/create", name="admin_products_orders_create", methods={"GET", "POST"})
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
                                    'price'=> $product->getOnSaleAmount() != null ? $product->getOnSaleAmount() : $product->getSellingPrice()
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
                                            'price'=> $product->getOnSaleAmount() != null ? $product->getOnSaleAmount() : $product->getSellingPrice()
                                        ]);

                                        $session->set('cart_item', $items);

                                        $i = 1;
                                            $response .= '<tr id="element-'.$product->getId().'">';
                                                $response .= '<td>'.$product->getName().'</td>';
                                                if(is_null($product->getOnSaleAmount())){
                                                 $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                                }else{
                                                 $response .= '<td>'.number_format($product->getOnSaleAmount()).'</td>';
                                                }
                                                $response .= '<td>'.$request->get('quantity').'</td>';
                                                $response .= '<td><a onClick="cartAction("remove","'.$product->getId().'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                            $response .= '</tr>';
                                        foreach($session->get('cart_item', []) as $v){
                                            $total += $v['price'];
                                        }
                                        
                                    }
                                } else {
                                    $session->set("cart_item", $itemArray);


                                    $i = 1;
                                    $response .= '<tr id="element-'.$product->getId().'">';
                                        $response .= '<td>'.$product->getName().'</td>';
                                        if(is_null($product->getOnSaleAmount())){
                                            $response .= '<td>'.number_format($product->getSellingPrice()).'</td>';
                                           }else{
                                            $response .= '<td>'.number_format($product->getOnSaleAmount()).'</td>';
                                           }
                                        $response .= '<td>'.$request->get('quantity').'</td>';
                                        $response .= '<td><a onClick="cartAction("remove","'.$product->getId().'")" class="btnRemoveAction btn btn-danger btn-sm" style="color:#fff;"><i class="fas fa-remove"></i></a></td>';
                                    $response .= '</tr>';

                                    foreach($session->get('cart_item', []) as $v){
                                        $total += $v['price'];
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
                                            $total += $v['price'];
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
                                            $total += $v['price'];

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
        

        $form = $this->createForm(BillingType::class, $billing);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if(!empty($session->get('cart_item',[]))){

                $this->manager->getConnection()->beginTransaction();
                $this->manager->getConnection()->setAutoCommit(false);

            try{
                $total = 0;
                $shop = $this->manager->getRepository(Shop::class)->find(25);

                $order->setNumber('123');
                $order->setShop($shop);
                $order->setCustomer($billing->getCustomer());
                $order->setManager($shop->getManager());

                foreach($session->get('cart_item', []) as $item){
                    $orderProduct = new OrderProduct();

                    $total += $item['price'];
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
                }

            
                $invoice->setOrders($order);
                $invoice->setAmount($total);
                $invoice->setInvoiceNumber($unique);

                $this->manager->persist($invoice);

    


                $payment->setInvoice($invoice);
                $payment->setPaymentType($billing->getPaymentType());
                $payment->setAmountPaid($billing->getAmountPaid());
                $payment->setAmount($total - $billing->getAmountPaid());
                $this->manager->persist($payment);
      

                $this->manager->flush();
                $this->manager->commit();
                

                foreach($session->get('cart_item',[]) as $item){
                    $product = $this->manager->getRepository(Product::class)->find($item['code']);

                    $this->api->putQ('products', $product);
                }

                $session->clear();

                $logo = $request->getUriForPath('/concept/assets/images/logo.jpg');

                if($payment->getAmountPaid() > 0){
                    $invoiceService->generateInvoice($invoice, $logo);
                    $this->addFlash("success", "Vente effectuée avec succès");
                    return $this->redirectToRoute('admin_products_orders_create');
                }else{
                    $this->addFlash("success", "La commande a été enregistré");
                    return $this->redirectToRoute('admin_products_orders_create');

                }

            }catch(\Exception $e){
                $this->manager->rollback();
                throw $e;
            }
                
            }else{
               $this->addFlash("danger","Aucun produit selectionné!");
            }
        }
        
        return $this->render("admin/products/orders/create.html.twig", [
            'products' => $this->manager->getRepository(Product::class)->findAll(),
            'form' => $form->createView()
        ]);
    }

      /**
     * @Route("/products/orders/show/{id}", name="admin_products_orders_show", methods={"GET"})
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

            return $this->render("admin/products/orders/show.html.twig", [
                'order' => $order,
                'products' => $this->manager->getRepository(OrderProduct::class)->findBy(['productOrder' => $order])
            ]);
    }

    /**
     * @Route("/products/orders/invoice/show/{id}", name="admin_products_orders_invoice_show", methods={"GET", "POST"})
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
     * @Route("/products/orders/delete/{id}", name="admin_products_orders_delete", methods={"GET"})
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
     * @Route("/products/replenishment/", name="admin_products_replenishment", methods={"GET","POST"})
     * @method productReplenishment
     * @param Request $request
     * @return Response
     */
    public function productsReplenishment(Request $request)
    {
       $replenishment = new Replenishment();
       $providerProduct = new ProviderProduct();

       $products = $this->manager->getRepository(Product::class)->findAll();

       $slugArray = [];
       $productArray = [];

       foreach($products as $product){
           if(!in_array($product->getSlug(), $slugArray)){
               $slugArray[] = $product->getSlug();
               $productArray[] = $product;
           }
       }
       
       $form = $this->createForm(ReplenishmentType::class, $replenishment, ['products' => $productArray]);
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()){
            $this->manager->getConnection()->beginTransaction();
            $this->manager->getConnection()->setAutoCommit(false);
            
            if($request->get('shopQuantity')){
                
                try{

                $providerQuantities = [];
                $providerQuantity = 0;
                $totalQuantity = 0;
                $provider = null;
                $product = null;
                $shop = $request->get('shopQuantity');

                foreach($shop as $key => $quantities){

                    foreach($quantities as $quantity){
                        
                        $providerQuantities[$key] = intval($quantity);
                    }
                    if($key != 0 && !empty($providerQuantities)){

                        $products = $this->manager->getRepository(Product::class)->findBy(["slug" => $replenishment->getProduct()->getSlug()]);
                        $provider = $this->manager->getRepository(Provider::class)->find($replenishment->getProvider()->getId());
                     
                        foreach($products as $product){

                          if($key == $product->getShop()->getId()){
                          $totalQuantity += $product->getQuantity();
                          $product->setQuantity($providerQuantities[$key] + $product->getQuantity());
                          $this->manager->persist($product);
                          
                          }
                        }
                     }

                     $totalQuantity += $providerQuantities[$key];
                     $providerQuantity += $providerQuantities[$key];
                }

                
                $providerProduct->setProduct($product);
                $providerProduct->setProvider($provider);
                $providerProduct->setQuantity($providerQuantity);
                $this->manager->persist($providerProduct);
                $this->manager->flush();
                $this->manager->commit();

                
                $product->setQuantity($totalQuantity);
                
                $this->api->putQ('products', $product);
                
                $this->addFlash('success', 'Réapprovisionnement effectué avec succès !');
                

        
                return $this->redirectToRoute('admin_products_replenishment');
             
                }catch(\Exception $e){
                    $this->manager->rollback();
                    $this->addFlash("danger", "Le réapprovisionnement n'a pas été effectué!");
                    $this->addFlash("danger", $e->getMessage());

                }
            }
       }

       return $this->render('admin/products/replenishments/index.html.twig',[
           'form' => $form->createView(),
           'shops' => $this->manager->getRepository(Shop::class)->findAll()
       ]);
    }

    /**
     * @Route("/orders/deliveries", name="admin_orders_deliveries", methods={"GET"})
     * @method ordersDeliveries
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveries()
    {
        return $this->render('admin/products/deliveries/index.html.twig',[
            'deliveries' => $this->manager->getRepository(Delivery::class)->findBy([], ['createdAt' => 'DESC'])
        ]);
    }

     /**
     * @Route("/orders/deliveries/create", name="admin_orders_deliveries_create", methods={"GET", "POST"})
     * @method ordersDeliveriesCreate
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesCreate(Request $request)
    {
        $delivery = new Delivery();

        $form = $this->createForm(AdminDeliveryType::class, $delivery);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->manager->persist($delivery);
            $this->manager->flush();

            $this->addFlash("success", "Planification de livraison crée !");
            return  $this->redirectToRoute("admin_orders_deliveries_create");
        }

        return $this->render('admin/products/deliveries/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

         /**
     * @Route("/orders/deliveries/update/{id}", name="admin_orders_deliveries_update", methods={"GET", "POST"})
     * @method ordersDeliveriesUpdate
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesUpdate(Request $request, Delivery $delivery)
    {
       
        if(is_null($delivery)){
            $this->addFlash("danger","Cette livraison n'existe pas!");
            return $this->redirectToRoute('admin_orders_deliveries');
        }


        $form = $this->createForm(AdminDeliveryType::class, $delivery);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->manager->persist($delivery);
            $this->manager->flush();

            $this->addFlash("success", "Planification de livraison modifiée !");
            return  $this->redirectToRoute("admin_orders_deliveries_update", ['id' => $delivery->getId()]);
        }

        return $this->render('admin/products/deliveries/update.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/orders/deliveries/delete/{id}", name="admin_orders_deliveries_delete", methods={"GET"})
     * @method ordersDeliveriesDelete
     * @param Request $request
     * @return Response
     */
    public function ordersDeliveriesDelete(Delivery $delivery)
    {
        $delivery = $this->manager->getRepository(Delivery::class)->find($delivery);


        if(is_null($delivery)){
            $this->addFlash("danger","Cette livraison n'existe pas!");
            return $this->redirectToRoute('admin_orders_deliveries');
        }

        $this->manager->remove($delivery);
        $this->manager->flush();

        $this->addFlash("success", "Livraison supprimée!");

        return $this->redirectToRoute('admin_orders_deliveries');

    }

      /**
     * @Route("/reports", name="admin_reports", methods={"GET", "POST"})
     * @method report
     * @param Request $request
     * @return Response
     */
    public function reports(Request $request)
    {
        // $chart = new Chart();
        $orderSearch = new OrderSearch();
        $results = [];

        $form = $this->createForm(OrderSearchByShopType::class, $orderSearch);
        $form->handleRequest($request);
                
        if(in_array('ROLE_ADMIN', $this->getUser()->getRoles())){
            $results = $this->manager->getRepository(Payment::class)->findPaymentsByWeek();
        }

        if($form->isSubmitted() && $form->isValid()){
            $results = $this->manager->getRepository(Payment::class)->searchPayments($orderSearch->getShop(), $orderSearch->getStart(),$orderSearch->getEnd(),$orderSearch->getPaymentType());
        }


        return $this->render('admin/reports/index.html.twig', [
            'shops' => $this->manager->getRepository(Shop::class)->findBy([],['createdAt' => 'DESC']),
            'i' => 1,
            'categories' => $this->manager->getRepository(Category::class)->findAll(),
            'form' => $form->createView(),
            'payments' => $this->manager->getRepository(Payment::class)->findBy(['status' => 1]),
            'results' => $results
        ]);
    }

      /**
     * @Route("/reports/shop/{id}", name="admin_reports_shop", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function reportsShop(Shop $shop)
    {
       

        $payments = $this->manager->getRepository(Payment::class)->shopPayments($shop);
        
        $deliveriesSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsSuccessfully($shop);
        $deliveriesIsNotSuccessfully = $this->manager->getRepository(Delivery::class)->shopOrderIsNotSuccessfully($shop);

        $fundOperations = $this->manager->getRepository(Fund::class)->findBy(['manager' => $shop->getManager()]);
    
        $deliveries = $this->manager->getRepository(Delivery::class)->shopDeliveries($shop);

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

        $totalPaid += $deliveryAmount;

        $shop = $this->manager->getRepository(Shop::class)->find($shop);

        $orderReturns = $this->manager->getRepository(OrderReturn::class)->findBy(['manager' => $shop->getManager()]);
        
        $orderReturnAmount = 0;
        foreach($orderReturns as $orderReturn){
            $orderReturnAmount += $orderReturn->getAmount();
        }

        return $this->render('admin/reports/shop.html.twig', [
            'shop' => $shop,
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'deliveryIsSuccessfully' => $deliveriesSuccessfully,
            'deliveryIsNotSuccessfully' => $deliveriesIsNotSuccessfully,
            'payments' => $payments,
            'deliveries' => $deliveries,
            'orderReturnAmount' => $orderReturnAmount

        ]);
    }

     /**
     * @Route("/reports/shop/{id}/orders", name="admin_reports_shop_orders", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function shopOrders(Shop $shop)
    {
        return $this->render("admin/reports/shop/orders.html.twig", [
            'payments' => $this->manager->getRepository(Payment::class)->shopOrders($shop),
            'shop' => $shop
        ]);
    }

    /**
     * @Route("/reports/shop/{id}/customers", name="admin_reports_shop_customers", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function shopCustomers(Shop $shop)
    {
        return $this->render("admin/reports/shop/customers.html.twig", [
            'customers' => $this->manager->getRepository(Customer::class)->findBy(['shops' => $shop, 'deleted' => 0]),
            'shop' => $shop
        ]);
    }

    /**
     * @Route("/reports/shop/{id}/products", name="admin_reports_shop_products", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function shopProducts(Shop $shop)
    {
        return $this->render("admin/reports/shop/products.html.twig", [
            'products' => $this->manager->getRepository(Product::class)->findBy(['shop' => $shop]),
            'shop' => $shop
        ]);
    }

     /**
     * @Route("/reports/shop/{id}/orders/return", name="admin_reports_shop_orders_return", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function shopOrdersReturn(Shop $shop)
    {
        return $this->render("admin/reports/shop/orders_return.html.twig", [
            'orders' => $this->manager->getRepository(OrderReturn::class)->findBy(['manager' => $shop->getManager()]),
            'shop' => $shop
        ]);
    }

        /**
     * @Route("/order/return/{id}/update", name="admin_orders_return_update", methods={"GET","POST"})
     * @return Response
    */
    public function ordersReturnUpdate(Request $request, OrderReturn $order)
    {
        if(is_null($order)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas!");
        }

        $form = $this->createForm(OrderReturnType::class, $order, ['shop' => $this->shop]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $order->setAmount($order->getFirstOrder()->getAmountPaid() - $order->getLastOrder()->getAmountPaid());
            if($order->getAmount() <= 0){
              $this->addFlash("warning", "Le total de l'ancienne vente doit est inferieur au total de la nouvelle vente!");
              return $this->redirectToRoute('manager_orders_return_create');
            }

            $this->manager->persist($order);
            $this->manager->flush();

            $this->addFlash("success", "Retour de marchandise modifié!");

            return $this->redirectToRoute("manager_orders_return_update", ['id' => $order->getId()]);
        }

        return $this->render('admin/reports/shop/order_return_update.html.twig', [
            'form' => $form->createView()  
        ]);
    }

        /**
     * @Route("/order/return/invoice/{id}", name="admin_orders_return_invoice", methods={"GET"})
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

        return $this->render('admin/products/orders/return/index.html.twig', [
            'orders' =>  $this->manager->getRepository(OrderReturn::class)->findBy([],['createdAt' => 'DESC'])
        ]);
    }



     /**
     * @Route("/order/return/{id}/delete", name="admin_orders_return_delete", methods={"GET"})
     * @return Response
    */
    public function ordersReturnDelete(OrderReturn $order)
    {
        if(is_null($order)){
            $this->addFlash("danger", "Ce retour de marchandise n'existe pas!");
        }

        $this->manager->remove($order);
        $this->manager->flush();

        $this->addFlash("success", "Retour de marchandise supprimé");

        return $this->redirectToRoute("manager_order_return");
    }

     /**
     * @Route("/reports/shop/{id}/deliveries", name="admin_reports_shop_deliveries", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function shopDeliveries(Shop $shop)
    {
        return $this->render("admin/reports/shop/deliveries.html.twig", [
            'deliveries' => $this->manager->getRepository(Delivery::class)->shopDeliveries($shop),
            'shop' => $shop
        ]);
    }

       /**
     * @Route("/update/software", name="update_software", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function updateSoftWare(Request $request)
    {
       $wProducts =  $this->api->getAll('products');
       $products = $this->manager->getRepository(Product::class)->findAll();
        
       $this->manager->getConnection()->beginTransaction();
       $this->manager->getConnection()->setAutoCommit(false);

       try{
            foreach($products as $product){
           
                foreach($wProducts as $wProduct){
                 
                    if($wProduct['id'] == $product->getWcProductId()){
                        
                        $product->setImageUrls($wProduct['images']);
                        
                        $this->manager->persist($product);
                        
                    }
                }
               
            }

            $this->manager->flush();
            $this->manager->commit();

            $this->addFlash("success", "Mise à jour terminée!");

          return   $this->redirectToRoute("admin_dashboard");

       }catch(\Exception $e){


        $this->addFlash("danger", "Une erreur est survenue lors de la mise à jour!");

        return $this->redirectToRoute("admin_dashboard");

       }

       
       return $this->redirectToRoute("admin_dashboard");

    }

    /**
     * @Route("/wc/orders", name="admin_wc_orders", methods={"GET"})
     * @method reportShop
     * @param Shop $shop
     * @return Response
     */
    public function wcOrders()
    {
        $array = [];
        
        try{
             $wcOrders = $this->api->getAll('orders');
             foreach($wcOrders as $wcOrder){
                if($wcOrder['status'] == "processing" || $wcOrder['satus'] == "completed"){
                    $array[] = $wcOrder;
                }
            }
    
        }catch(\Exception $e){
            throw $e;
        }

        
        return $this->render('admin/products/wc/orders/index.html.twig', [
            'orders' => $array
        ]);
    }

    /**
     * @Route("/customer/{id}/orders", name="admin_customer_orders", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function customersOrders(Customer $customer)
    {
         
        return $this->render('admin/contacts/customers/orders.html.twig', [
            'customer' => $this->manager->getRepository(Customer::class)->find($customer),
            'payments' => $this->manager->getRepository(Payment::class)->customerOrders($customer)
        ]);
    }


               
    /**
     * @Route("/administrators", name="admin_administrators", methods={"GET"})
     * @method administrators
     * @return Response
     */
    public function administrators()
    {
        return $this->render('admin/contacts/administrators/index.html.twig', [
            'administrators' => $this->manager->getRepository(User::class)->findAdministrators()
        ]);
    }

  
     /**
     * @Route("/administrators/create", name="admin_administrators_create", methods={"GET", "POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */

    public function administratorsCreate(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $admin = new User();

        $form = $this->createForm(AdministratorType::class, $admin);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $admin->setDeleted(false);
            $passwordHash = $passwordEncoder->encodePassword($admin, '123456');
            $admin->setPassword($passwordHash);
            $admin->setRoles(["ROLE_ADMIN"]);
            $this->manager->persist($admin);
            $this->manager->flush();

            $this->addFlash("success", "Administrateur crée!");

            $this->redirectToRoute('admin_administrators_create');
        }

        return $this->render('admin/contacts/administrators/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/administrators/update/{id}", name="admin_administrators_update", methods={"GET", "POST"})
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param User $staff
     * @return Response
     */

    public function administratorsUpdate(Request $request, UserPasswordEncoderInterface $passwordEncoder, User $admin)
    {
        $form = $this->createForm(AdministratorType::class, $admin);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $passwordHash = $passwordEncoder->encodePassword($admin, '123456');
            $admin->setPassword($passwordHash);
            $this->manager->persist($admin);
            $this->manager->flush();

            $this->addFlash("success", "Administrateur modifié!");


        }

        return $this->render('admin/contacts/administrators/update.html.twig',[
            'form' => $form->createView()
        ]);
    }
       /**
     * @Route("/payment-types/delete/{id}", name="admin_administrators_delete", methods={"GET"})
     * @method paymentTypesDelete
     * @return Response
     */
    public function administratorsRemove(Request $request, User $user)
    {

        if(is_null($user))
             throw $this->createNotFoundException('Cet administrateur n\'existe pas!');

        $this->manager->remove($user);
        $this->manager->flush();

        $this->addFlash("success", "Administrateur supprimé avec succès");

        return $this->redirectToRoute("admin_administrators");
    }


      /**
     * @Route("/versements", name="admin_versements", methods={"GET"})
     * @return Response
     */
    public function versements()
    {
        return $this->render('admin/operations/versements/index.html.twig', [
            'versements' => $this->manager->getRepository(Versement::class)->findAll(),
        ]);
    }

     /**
     * @Route("/versements/{id}/update", name="admin_versements_update", methods={"GET", "POST"})
     * @return Response
     */
    public function versementsUpdate(Request $request, Versement $versement)
    {

        $form = $this->createForm(VersementType::class, $versement);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $this->manager->persist($versement);
            $this->manager->flush();

            $this->addFlash("success", "Le versement a été modifié!");

            return $this->redirectToRoute('admin_versements');
        }

        return $this->render('admin/operations/versements/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/versements/{id}/delete", name="admin_versements_delete", methods={"GET"})
     * @return Response
    */
    public function versementsDelete(Versement $versement)
    {
        if(is_null($versement)){
            $this->addFlash("danger", "Ce versement n'existe pas");
        }

        $this->manager->remove($versement);
        $this->manager->flush();

        $this->addFlash('success', 'Versement supprimé!');

        return $this->redirectToRoute("admin_versements");
    }

         /**
     * @Route("/fund/operations", name="admin_fund_operations", methods={"GET"})
     * @return Response
     */
    public function fundOperations()
    {
        return $this->render('admin/operations/fund/index.html.twig', [
            'operations' => $this->manager->getRepository(Fund::class)->findBy([],['createdAt' => 'DESC']),
        ]);
    }

      /**
     * @Route("/fund/operations/{id}/update", name="admin_fund_operations_update", methods={"GET", "POST"})
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

            $this->manager->persist($operation);
            $this->manager->flush();

            $this->addFlash("success", "Opération modifiée !");

            return $this->redirectToRoute('admin_fund_operations_update', ['id' => $operation->getId()]);
        }

        return $this->render('admin/operations/fund/update.html.twig', [
            'form' => $form->createView(),
        ]);
    }

     /**
     * @Route("/fund/operations/{id}/delete", name="admin_fund_operations_delete", methods={"GET"})
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

        return $this->redirectToRoute("admin_fund_operations");
    }


     /**
     * @Route("/cancel/order/{id}", name="admin_cancel_order", methods={"GET"})
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
            }


            $this->addFlash("success","Vente annulée!");
            
       }catch(\Exception $e){
            throw $e;
       }
      

       return $this->redirectToRoute('admin_products_orders');
    }

      /**
     * @Route("/setting", name="admin_setting", methods={"GET","POST"})
     * @return Response
    */
    public function setting(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(SettingType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
           

            $isPasswordValid = $encoder->isPasswordValid($user, $user->getOldPassword());
          
           if($isPasswordValid){
            
            $passHass = $encoder->encodePassword($user, $user->getNewPassword());

            $manager->setPassword($passHass);
            
            $this->manager->persist($user);
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


    
}