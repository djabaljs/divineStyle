<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Entity\User;
use App\Form\ShopType;
use App\Entity\Customer;
use App\Form\CustomerType;
use App\Form\ShopUpdateType;
use App\Form\UserType;
use App\Services\Woocommerce\WoocommerceApiService;
use App\Repository\ShopRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->manager = $entityManager;
    }

    /**
     * @Route("/dashboard", name="admin_dashboard")
     * @param WoocommerceApiService $apiService
     * @throws Exception $e
     * @return Response
     */
    public function dashboard(WoocommerceApiService $apiService): Response
    {
      
        try{

            return $this->render('admin/dashboard.html.twig', [
                'sales' => $apiService->clientRequest('GET', 'reports/sales'),
                'customers' => $apiService->clientRequest('GET', 'customers'),
                'products' => $apiService->clientRequest('GET', 'products'),
                'orders' => $apiService->clientRequest('GET', 'orders'),
            ]);
       
        }catch(\Exception $e){

            $this->addFlash("danger", "Erreur: impossible de se connecter au site distant.");

            return $this->render('admin/dashboard.html.twig', [
                'sales' => [],
                'customers' => [],
                'products' => [],
                'orders' => [],
            ]);
        }

    }

    /**
     * @Route("/products/products", name="admin_products", methods={"GET"})
     * @param WoocommerceApiService $apiService
     * @throws Exception $e
     * @return Response
     */
    public function products(WoocommerceApiService $apiService)
    {
        try{
            return $this->render('admin/products/products/index.html.twig', [
                'products' =>  $apiService->clientRequest('GET', 'products')
            ]);
        }catch(\Exception $e){
            
            return $this->render('admin/products/products/index.html.twig', [
                'products' =>  []
            ]);
        }
     
    }

    /**
     * @Route("/products/create", name="admin_product_create", methods={"GET"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function createProduct(Request $request, WoocommerceApiService $apiService): Response
    {
        $data = [
            'name' => 'Nike',
            'type' => 'simple',
            'regular_price' => '21.99',
            'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
            'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            'categories' => [
                [
                    'id' => 9
                ],
                [
                    'id' => 14
                ]
            ],
            'images' => [
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                ],
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
                ]
            ]
        ];

        $products = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

    /**
     * @Route("/products/show/{id}", name="admin_product_show", methods={"GET"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @param $id
     * @return Response
     */
    public function showProduct(Request $request, WoocommerceApiService $apiService, $id): Response
    {
        try{
            return $this->render('admin/products/products/show.html.twig', [
                'product' => $apiService->clientRequest('GET', 'products/'.$id, null),
            ]);
        }catch(\Exception $e){
            return $this->render('admin/products/products/show.html.twig', [
                'product' => null,
            ]);
        }
        
    }

     /**
     * @Route("/products/update/{io}", name="admin_product_update", methods={"PUT"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function updateProduct(Request $request, WoocommerceApiService $apiService): Response
    {
        $data = [
            'name' => 'Nike',
            'type' => 'simple',
            'regular_price' => '21.99',
            'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
            'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            'categories' => [
                [
                    'id' => 9
                ],
                [
                    'id' => 14
                ]
            ],
            'images' => [
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                ],
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
                ]
            ]
        ];

        $products = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

     /**
     * @Route("/products/delete/{id}", name="admin_product_delete", methods={"DELETE"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function deleteProduct(Request $request, WoocommerceApiService $apiService): Response
    {
        $data = [
            'name' => 'Nike',
            'type' => 'simple',
            'regular_price' => '21.99',
            'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.',
            'short_description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.',
            'categories' => [
                [
                    'id' => 9
                ],
                [
                    'id' => 14
                ]
            ],
            'images' => [
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_front.jpg'
                ],
                [
                    'src' => 'http://demo.woothemes.com/woocommerce/wp-content/uploads/sites/56/2013/06/T_2_back.jpg'
                ]
            ]
        ];

        $products = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

    /**
     * @Route("/products/categories", name="admin_categories", methods={"GET"})
     * @param WoocommerceApiService $apiService
     * @throws Exception $e
     * @return Response
     */
    public function categories(WoocommerceApiService $apiService): Response
    {
        try{
            $categories = $apiService->clientRequest('GET', 'products/categories');
        }catch(\Exception $e){
            $categories = [];
        }

        return $this->render('admin/products/categories/index.html.twig', [
            'categories' => $categories 
        ]);
    }


    /**
     * @Route("/products/categories", name="admin_category_create", methods={"POST"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function createCategories(Request $request, WoocommerceApiService $apiService): Response
    {
        $categories = $apiService->clientRequest('GET', 'products/categories');
        return $this->render('admin/products/categories/create.html.twig', [
            'categories' => $categories 
        ]);
    }

     /**
     * @Route("/products/categories/{id}", name="admin_category_show", methods={"GET"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @param $id
     * @return Response
     */
    public function showCategory(Request $request, WoocommerceApiService $apiService, $id): Response
    {
        $categories = $apiService->clientRequest('GET', 'products/categories/'.$id);
        return $this->render('admin/products/categories/show.html.twig', [
            'categories' => $categories 
        ]);
    }


    /**
     * @Route("/customers", name="admin_customers", methods={"GET"})
     * @param WoocommerceApiService $apiService
     * @param Exception $e
     * @return Response
     */
    public function customers(WoocommerceApiService $apiService)
    {
        try{
            $customers = $apiService->clientRequest('GET', 'customers');
        }catch(\Exception $e){
            $customers = [];
        }
        return $this->render('admin/contacts/customers/index.html.twig',[
            'customers' => $customers
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
     * @param WoocommerceApiService $apiService
     * @param $id
     * @return Response
     */
    public function updateCustomer(Request $request, WoocommerceApiService $apiService, $id)
    {
        $customer = $this->getDoctrine()->getRepository(Customer::class)->find($id);

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
     * @param ShopRepository $shopRepository
     * @return Response
     */
    public function shops(ShopRepository $shopRepository)
    {
        $shops = $shopRepository->findAll();

        return $this->render('admin/shops/index.html.twig',[
            'shops' => $shops
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
                $this->manager->persist($staff);
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
}