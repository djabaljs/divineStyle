<?php

namespace App\Controller;

use Mpdf\Mpdf;
use App\Entity\Shop;
use App\Entity\User;
use App\Entity\Color;
use App\Entity\Order;
use App\Entity\Width;
use App\Entity\Height;
use App\Entity\Length;
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
use App\Form\HeightType;
use App\Form\LengthType;
use App\Entity\Attribute;
use App\Form\BillingType;
use App\Form\ProductType;
use App\Form\CategoryType;
use App\Form\CustomerType;
use Cocur\Slugify\Slugify;
use App\Entity\DeliveryMan;
use App\Entity\PaymentType;
use App\Form\AttributeType;
use App\Entity\OrderProduct;
use App\Form\ShopUpdateType;
use App\Form\DeliveryManType;
use App\Form\PaymentTypeType;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProductRepository;
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
    public function dashboard(WoocommerceApiService $apiService): Response
    {
        $payments = $this->manager->getRepository(Payment::class)->findAll();
        $deliveriesSuccessfully = $this->manager->getRepository(Delivery::class)->findBy(['status' => 1]);

        $totalPaid = 0;
        $totalAmount = 0;
        foreach($payments as $payment){
            $totalPaid += $payment->getAmountPaid();
            $totalAmount += $payment->getAmount();
        }
        return $this->render('admin/dashboard.html.twig', [
            'orders' => $this->manager->getRepository(Order::class)->findAll(),
            'customers' => $this->manager->getRepository(Customer::class)->findAll(),
            'products' => $this->manager->getRepository(Product::class)->findAll(),
            "amountPaid" => $totalPaid,
            'amount' => $totalAmount,
            'deliveries' => $deliveriesSuccessfully
        ]);

    }

    /**
     * @Route("/products/products", name="admin_products", methods={"GET"})
     * @method products
     */
    public function products(WoocommerceApiService $apiService)
    {
        return $this->render('admin/products/products/index.html.twig', [
            'products' =>  $this->manager->getRepository(Product::class)->findAll()
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

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

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

            $product->colorArrays = $colorArrays;
            $product->lengthArrays = $lengthArrays;

            $response =  $this->api->post("products", $product);

            $product->setWcProductId($response['id']);
            if($product->getIsVariable()){
                $color->addProduct($product);
                $length->addProduct($product);
            }
       

            $this->manager->persist($product);

            if($product->getIsVariable()){
                $this->manager->persist($color);
                $this->manager->persist($length);
            }
        
            $this->manager->flush();

            $this->addFlash("success", "Produit créé et envoyé dans le magasin ".$product->getShop()." avec succès!");

            return $this->redirectToRoute("admin_products_create");

        }

        return $this->render('admin/products/products/create.html.twig', [
           'form' => $form->createView()
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
        $color = new Color();
        $length = new Length();

        $product = $this->manager->getRepository(Product::class)->findOneBy(['slug' => $product->getSlug()]);
        
        if(is_null($product))
            throw $this->createNotFoundException("Ce produit n'existe pas");
        
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            
            $category = $this->manager->getRepository(Category::class)->find($request->get('category'));


            $slugify = new Slugify();

            $product->setSlug($slugify->slugify($product->getName()));
            $product->setCategory($category);
            
            foreach($product->getColors() as $color){
                dd($color);
            }
         
            $this->api->put('products',$product);

            $this->manager->persist($product);
            $this->manager->flush(); 

            $this->addFlash("success", "Produit modifié avec succès!");

            return $this->redirectToRoute("admin_products_update", [
                "slug" => $product->getSlug(), 
            ]);

        } 

        return $this->render('admin/products/products/update.html.twig', [
            'form' => $form->createView(),
            "update" => $product->getCategory()->getId()
        ]);
    }

     /**
     * @Route("/products/delete/{slug}", name="admin_products_delete", methods={"GET"})
     * @param Request $request
     * @param Product $request
     * @return Response
     */
    public function deleteProduct(Request $request, Product $product): Response
    {
        $product = $this->manager->getRepository(Product::class)->findOneBy(['slug' => $product->getSlug()]);

        $productCopy = $product;
        if(is_null($product))
            throw $this->createNotFoundException("Ce produit n'existe pas!");

        $this->api->delete('products', $product);

        $this->manager->remove($product);
        $this->manager->flush();


        $this->addFlash("success", "Produit supprimé du magasin ".$productCopy->getShop()." !");

        return  $this->redirectToRoute("admin_products");
    }

    /**
     * @Route("/products/categories", name="admin_categories", methods={"GET"})
     * @return Response
     */
    public function categories(): Response
    {
      return  $this->render('admin/products/categories/index.html.twig', [
            'categories' => $this->manager->getRepository(Category::class)->findAll()
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
        
        $this->manager->remove($category);
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
            'customers' => $this->manager->getRepository(Customer::class)->findAll()
        ]);
    }

    /**
     * @Route("/customers/create", name="admin_customer_create", methods={"POST", "GET"})
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
            $this->manger->persist($customer);
            $this->manger->flush($customer);

            $this->addFlash("success", "Client enregistré avec succès!");
            
        }

        return $this->render('admin/contacts/customers/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

     /**
     * @Route("/customers/update", name="admin_customer_create", methods={"PUT", "GET"})
     * @param Request
     * @param Customer $customer
     * @return Response
     */
    public function updateCustomer(Request $request, Customer $customer)
    {
        $customer = $this->getDoctrine()->getRepository(Customer::class)->find($customer->getId());

        if(is_null($customer))
            throw $this->createNotFoundException("Ce client n'existe pas!");

        $form = $this->createForm(CustomerType::class, $customer);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $this->manger->persist($customer);
            $this->manger->flush($customer);

            $this->addFlash("success", "Client modifié avec succès!");
            
        }

        return $this->render('admin/contacts/customers/create.html.twig',[
            'form' => $form->createView()
        ]);
    }




     /**
     * @Route("/shops", name="admin_shops", methods={"GET"})
     * @return Response
     */
    public function shops()
    {
        return $this->render('admin/shops/index.html.twig',[
            'shops' => $this->manager->getRepository(Shop::class)->findAll()
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

        $this->manager->remove($shop);
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

        return $this->render('admin/staffs/index.html.twig',[
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

        return $this->render('admin/staffs/update.html.twig',[
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

        return $this->render('admin/staffs/update.html.twig',[
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
        return $this->render('admin/payment_types/index.html.twig', [
            'paymentTypes' => $this->manager->getRepository(PaymentType::class)->findAll()
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

        $this->manager->remove($paymentType);
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
            'mans' => $this->manager->getRepository(DeliveryMan::class)->findAll()
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

        $this->manager->remove($man);
        $this->manager->flush();

        $this->addFlash("success", "Livreur supprimé avec succès");

        return $this->redirectToRoute("admin_delivery_mans");
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
           
            $options = explode('|',$attribute->getText());
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
            $text .= '|';
        }

        $text = substr($text, 0, -1);
    
        $attribute->text = $text;

        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $options = explode('|',$attribute->getText());
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
            'payments' => $paymentRepository->findBy([], ['createdAt' => 'DESC'])
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
                                            $total += $v['price'];
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

                    $this->manager->persist($delivery);

                    $total += $billing->getDeliveryAmount();
                }

            
                $invoice->setOrders($order);
                $invoice->setAmount($total);
                $invoice->setInvoiceNumber($unique);

                $this->manager->persist($invoice);

    
                foreach($session->get('cart_item',[]) as $item){
                    $product = $this->manager->getRepository(Product::class)->find($item['code']);

                    $this->api->putQ('products', $product);
                }

                $payment->setInvoice($invoice);
                $payment->setPaymentType($billing->getPaymentType());
                $payment->setAmountPaid($billing->getAmountPaid());
                $payment->setAmount($total - $billing->getAmountPaid());
                $this->manager->persist($payment);
      
                $this->manager->flush();
                $this->manager->commit();
                
                $session->clear();

                $logo = $request->getUriForPath('/concept/assets/images/logo.jpg');
                $invoiceService->generateInvoice($invoice, $logo);

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
}