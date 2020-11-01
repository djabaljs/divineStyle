<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Form\OrderType;
use App\Repository\ShopRepository;
use App\Repository\OrderRepository;
use App\Services\Woocommerce\WoocommerceApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("ROLE_MANAGER")
 * @Route("/user/manager")
 */
class ManagerController extends AbstractController
{
  private $manager;
  private $datas = [];

  public function __construct(EntityManagerInterface $entityManager)
  {
      $this->manager = $entityManager;
  }

  /**
   * @Route("/dashboard", name="manager_dashboard")
   * @param WoocommerceApiService $apiService
   * @param ShopRepository $shopRepository
   * @return Response
   */
  public function dashboard(WoocommerceApiService $apiService, ShopRepository $shopRepository): Response
  {

    $customers= [];

    $shop = $shopRepository->findManagerCustomers($this->getUser());
    
    foreach($shop->getCustomers() as $key => $customer){
        $customers[$key] = $customer;
    }
   
    return $this->render('manager/dashboard.html.twig', [
        'sales' => $apiService->clientRequest('GET', 'reports/sales'),
        'customers' => $customers,
        'products' => $apiService->clientRequest('GET', 'products'),
        'orders' => $apiService->clientRequest('GET', 'orders')
    ]);
  }

   /**
     * @Route("/products/products", name="manager_products", methods={"GET"})
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function products(WoocommerceApiService $apiService)
    {
        return $this->render('manager/products/products/index.html.twig', [
            'products' =>  $apiService->clientRequest('GET', 'products')
        ]);
    }


    /**
     * @Route("/products/show/{id}", name="manager_product_show", methods={"GET"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @param $id
     * @return Response
     */
    public function showProduct(Request $request, WoocommerceApiService $apiService, $id): Response
    {
        return $this->render('manager/products/products/show.html.twig', [
            'product' => $apiService->clientRequest('GET', 'products/'.$id, null)
        ]);
    }

     /**
     * @Route("/products/categories", name="manager_categories", methods={"GET"})
     * @param WoocommerceApiService $apiService
     * @return Response
     */
    public function categories(WoocommerceApiService $apiService): Response
    {
        return $this->render('manager/products/categories/index.html.twig', [
            'categories' => $apiService->clientRequest('GET', 'products/categories')
        ]);
    }

    /**
     * @Route("/products/categories/{id}", name="manager_category_show", methods={"GET"})
     * @param Request $request
     * @param WoocommerceApiService $apiService
     * @param $id
     * @return Response
     */
    public function showCategory(Request $request, WoocommerceApiService $apiService, $id): Response
    {
        return $this->render('manager/products/categories/show.html.twig', [
            'categories' => $apiService->clientRequest('GET', 'products/categories/'.$id)
        ]);
    }


   /**
   * @Route("/sales", name="manager_sales")
   * @param OrderRepository $orderRepository
   * @return Response
   */
    public function orders(OrderRepository $orderRepository)
    {
        return $this->render('manager/sales/index.html.twig', [
          'sales' => $orderRepository->findAll()
        ]);
    }

    private function myProductObjects($arrays)
    {
        foreach($arrays as $key => $value){
            $this->datas[$key] = new Product(
                $value['id'], 
                $value['name'], 
                $value['price'], 
                $value['categories'],
                $value['dimensions'], 
                $value['stock_quantity'],
            );
        }

        return $this->datas;
    }

    /**
     * @Route("/sales/create", name="manager_sale_create")
     * @param Request $request
     * @return Response
     */
    public function createOrder(Request $request, WoocommerceApiService $apiService)
    {

        // if($request->getMethod() === 'GET'){
        //     try{
        //         $products = $apiService->clientRequest('GET', 'products');
       
        //         $productsCopy = $products;
        //         $productObjects =  $this->myProductObjects($products);
        //         $products =$this->manager->getRepository(Product::class)->findAll();
                
        //         $inside = [];
        //         $notInside = [];
                
        //         if(!empty($products)){
        //            $es = [];
        //                foreach($productObjects as $key => $value){
        //                    $es[$key] = $value->getWoocommerceId();
        //                }
       
        //                foreach($products as $key => $value){
        //                    if(in_array($value->getWoocommerceId(), $es)){
        //                        $inside[$key] = $value;
        //                    }else{
        //                        $notInside[$key] = $value;
        //                    }
        //                }
        //         }else{
        //            foreach($productObjects as $key => $value){
        //                $inside[$key] = $value;
        //            }
        //         }
       
             
        //         $this->manager->getConnection()->beginTransaction();
       
        //         foreach($notInside as $key => $value){
        //             $this->manager->remove($value);
        //         }
       
        //         foreach($inside as $key => $value){
        //             $this->manager->persist($value);
        //         }
       
        //         $this->manager->flush();
        //         $this->manager->commit();
       
        //      }catch(\Exception $e){
       
        //           $this->addFlash("danger", "Erreur: Impossible de charger les donées");
        //      }
             
        // }
     
      
      $order = new Order();

      $form = $this->createForm(OrderType::class, $order);
      $form->handleRequest($request);
    //   dd($request);

      if($form->isSubmitted() && $form->isValid()){
        dd($order);
      }

      return $this->render('manager/sales/create.html.twig', [
        'form' => $form->createView(),
        'order' => $order
      ]);
    }
}