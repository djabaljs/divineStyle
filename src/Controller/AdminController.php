<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ApiService;
/**
 * @IsGranted("ROLE_ADMIN")
 * @Route("/user/admin")
 */
class AdminController extends AbstractController
{
    
    /**
     * @Route("/dashboard", name="admin_dashboard")
     * @param ApiService $apiService
     * @return Response
     */
    public function dashboard(ApiService $apiService): Response
    {
      
     $sales = $apiService->clientRequest('GET', 'reports/sales');
     $customers = $apiService->clientRequest('GET', 'customers');
     $products = $apiService->clientRequest('GET', 'products');
     $orders = $apiService->clientRequest('GET', 'orders');
      return $this->render('admin/dashboard.html.twig', [
          'sales' => $sales,
          'customers' => $customers,
          'products' => $products,
          'orders' => $orders
      ]);
    }

    /**
     * @Route("/products/products", name="admin_products", methods={"GET"})
     * @param ApiService $apiService
     * @return Response
     */
    public function products(ApiService $apiService)
    {
        $products = $apiService->clientRequest('GET', 'products');
        // dd($products);
        return $this->render('admin/products/products/index.html.twig', [
            'products' => $products 
        ]);
    }

    /**
     * @Route("/products/create", name="admin_product_create", methods={"GET"})
     * @param Request $request
     * @param ApiService $apiService
     * @return Response
     */
    public function createProduct(Request $request, ApiService $apiService): Response
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

        $response = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

    /**
     * @Route("/products/show/{id}", name="admin_product_show", methods={"GET"})
     * @param Request $request
     * @param ApiService $apiService
     * @param $id
     * @return Response
     */
    public function showProduct(Request $request, ApiService $apiService, $id): Response
    {
        $product = $apiService->clientRequest('GET', 'products/'.$id, null);
        return $this->render('admin/products/products/show.html.twig', [
            'product' => $product 
        ]);
    }

     /**
     * @Route("/products/update/{io}", name="admin_product_update", methods={"PUT"})
     * @param Request $request
     * @param ApiService $apiService
     * @return Response
     */
    public function updateProduct(Request $request, ApiService $apiService): Response
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

        $response = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

     /**
     * @Route("/products/delete/{id}", name="admin_product_delete", methods={"DELETE"})
     * @param Request $request
     * @param ApiService $apiService
     * @return Response
     */
    public function deleteProduct(Request $request, ApiService $apiService): Response
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

        $response = $apiService->clientRequest('POST', 'products',$data);
        return $this->render('admin/products/products.html.twig', [
            'products' => $products 
        ]);
    }

    /**
     * @Route("/products/categories", name="admin_categories", methods={"GET"})
     * @param ApiService $apiService
     * @return Response
     */
    public function categories(ApiService $apiService): Response
    {
        $categories = $apiService->clientRequest('GET', 'products/categories');
        return $this->render('admin/products/categories/index.html.twig', [
            'categories' => $categories 
        ]);
    }


    /**
     * @Route("/products/categories", name="admin_category_create", methods={"POST"})
     * @param Request $request
     * @param ApiService $apiService
     * @return Response
     */
    public function createCategories(Request $request, ApiService $apiService): Response
    {
        $categories = $apiService->clientRequest('GET', 'products/categories');
        return $this->render('admin/products/categories/create.html.twig', [
            'categories' => $categories 
        ]);
    }

     /**
     * @Route("/products/categories/{id}", name="admin_category_show", methods={"GET"})
     * @param Request $request
     * @param ApiService $apiService
     * @param $id
     * @return Response
     */
    public function showCategory(Request $request, ApiService $apiService, $id): Response
    {
        $categories = $apiService->clientRequest('GET', 'products/categories/'.$id);
        return $this->render('admin/products/categories/show.html.twig', [
            'categories' => $categories 
        ]);
    }
}