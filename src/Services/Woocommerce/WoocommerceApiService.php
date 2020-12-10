<?php

namespace App\Services\Woocommerce;

use App\Entity\Product;
use App\Entity\ProductVariation;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ProductVariationRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class WoocommerceApiService  extends AbstractController
{
      //PRODUCTS REQUESTS INSIDE README
     
      /**
       * @var $client
       */
      protected $client;
         
      /**
       * @var $endpoint
       */
      protected $endpoint;
   
      /**
       * @var $username
       */
      protected $username;
        
      /**
       * @var $password
       */
      protected $password;

        /**
       * @var $password
       */
      protected $productVariationRepository;
    
            /**
       * @var $password
       */
      protected $manager;
    
    
     /**
      * @method construct
      */
      public function __construct(HttpClientInterface $client, ContainerInterface $container, ProductVariationRepository $productVariationRepository, EntityManagerInterface $manager)
      {
          $this->client = $client;
          $this->endpoint = $container->getParameter('TEST_API_ENDPOINT');
          $this->username = $container->getParameter('TEST_USERNAME');
          $this->password = $container->getParameter('TEST_PASSWORD');
          $this->productVariationRepository = $productVariationRepository;
          $this->manager = $manager;
      } 

      /**
       * @method post
       * @throws Exception $e
       * @return Response
       */
      public function post($target, $data)
      {

        
        switch($target){
            case "products":
                try{
                    if(!$data->getIsVariable()){

                        $response =  $this->client->request(
                            "POST",
                            $this->endpoint.''.$target, [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->username, $this->password],
                            "body" => [
                                "name" => $data->getName(),
                                "slug" => $data->getSlug(),
                                "price" => $data->getBuyingPrice(),
                                "regular_price" => $data->getSellingPrice(),
                                "sale_price" => $data->getSellingPrice(),
                                "manage_stock" => true,
                                "stock_quantity" => $data->getQuantity(),
                                "status" => "pending",
                                "type" => "simple",
                                "categories" => [
                                    [
                                        "id" => $data->getCategory()->getWcCategoryId()
                                    ]
                                ]
                            ]
                        ]);
                    }else{
                        $response1 =  $this->client->request(
                            "POST",
                            $this->endpoint.''.$target, [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->username, $this->password],
                            "body" => [
                                "name" => $data->getName(),
                                "slug" => $data->getSlug(),
                                "regular_price" => $data->getSellingPrice(),
                                "manage_stock" => true,
                                "stock_quantity" => $data->getQuantity(),
                                "status" => "pending",
                                "type" => "variable",
                                "categories" => [
                                        [
                                            "id" => $data->getCategory()->getWcCategoryId()
                                        ]
                                ],
                                "attributes" => [
                                    [
                                        "id" => 4,
                                        "variation" => true,
                                        "visible"   => true,
                                        "options" => $data->colorArrays
                                    ],
                                    [
                                        "id" => 1,
                                        "variation" => true,
                                        "visible"   => true,
                                        "options" => $data->lengthArrays
                                    ]
                                ],
                            ]
                        ]);

                        $statusCode = $response1->getStatusCode();
                        // $statusCode = 200
                        
                        if($statusCode === 201){
    
                            $contentType = $response1->getHeaders()['content-type'][0];
                            // $contentType = 'application/json'
                            // $content = '{"id":521583, "name":"symfony-docs", ...}'
                            $content1 = $response1->toArray();
                            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                            
                            return $content1;
                        }             
                    } 

                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200

                    if($statusCode === 201){

                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        // $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        $this->addFlash("success", "Produit crée sur le site de vente.");

                        return $content;

                    }else{
                        $this->addFlash("danger", "Le produit n'a pas été crée sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    // throw $e;
                    $this->addFlash("danger","Erreur: Une eurreur s'est produite lors de la création du produit!");
                    // throw $e;
                }
            break;
            case "categories":
                try{

                    $response =  $this->client->request(
                        "POST",
                        $this->endpoint.'products/'.$target, [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->username, $this->password],
                         "body" => [
                            "name" => $data->getName(),
                            "slug" => $data->getSlug(),
                            "description" => $data->getDescription()
                        ]
                    ]);
        
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200
                   
                    if($statusCode === 201){

                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        $this->addFlash("success", "Catégorie crée sur le site de vente.");

                        return $content;

                    }else{
                        $this->addFlash("danger", "La Catégorie n'a pas été crée sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    // throw $e;

                    $this->addFlash("danger","Erreur: Une eurreur s'est produite lors de la création de la catégorie!");

                    // throw $e;
                }
            break;
        case "attributes":
            try{

                $response =  $this->client->request(
                    "POST",
                    $this->endpoint.'products/'.$target, [
                     // use a different HTTP Basic authentication only for this request
                     'auth_basic' => [$this->username, $this->password],
                     "body" => [
                        "name" => $data->getName(),
                        "slug" => $data->getSlug(),
                        "options" => $data->getDescription()
                    ]
                ]);
    
                $statusCode = $response->getStatusCode();
                // $statusCode = 200
               
                if($statusCode === 201){

                    $contentType = $response->getHeaders()['content-type'][0];
                    // $contentType = 'application/json'
                    $content = $response->getContent();
                    // $content = '{"id":521583, "name":"symfony-docs", ...}'
                    $content = $response->toArray();
                    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                    $this->addFlash("success", "Catégorie crée sur le site de vente.");

                    return $content;

                }else{
                    $this->addFlash("danger", "La Catégorie n'a pas été crée sur le site de vente.");
                }
              
            }catch(\Exception $e){
                $this->addFlash("danger",$e->getMessage());

            }
        break;
                
        }

        
      }
    
     /**
     * @method getAll
     * @throws Exception $e
     * @return Response
     */
    public function getAll($target)
    {
        try{

            $response =  $this->client->request(
                   "GET",
                   $this->endpoint.''.$target, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password]
               ]);

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            if($statusCode === 200){

                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $content = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                
                return $content;
            }
             
           }catch(\Exception $e){
            $this->addFlash("danger",$e->getMessage());

           }
    }

    /**
     * @method getOne
     * @throws Exception $e
     * @return Response
     */
    public function getOne($target, $data)
    {
        try{

            $response =  $this->client->request(
                   "GET",
                   $this->endpoint.''.$target.'/'.$data->getWcProductId(), [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password],
               ]);

            $statusCode = $response->getStatusCode();
            // $statusCode = 200
            if($statusCode === 200){

                $contentType = $response->getHeaders()['content-type'][0];
                // $contentType = 'application/json'
                $content = $response->getContent();
                // $content = '{"id":521583, "name":"symfony-docs", ...}'
                $content = $response->toArray();
                // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                
                return $content;
            }
             
           }catch(\Exception $e){
              $this->addFlash("danger",$e->getMessage());
           }
    }

    /**
     * @method update
     * @throws Exception $e
     * @return Response
     */
    public function put($target, $data)
    {
        switch($target){
            case "products":

                try{
                  
                    $response =  $this->client->request(
                        "PUT",
                        $this->endpoint.''.$target.'/'.$data->getWcProductId(), [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->username, $this->password],
                         "body" => [
                            "name" => $data->getName(),
                            "slug" => $data->getSlug(),
                            "sale_price" => $data->getSellingPrice(),
                            "regular_price" => $data->getSellingPrice(),
                            "stock_quantity" => $data->getQuantity(),
                        ]
                    ]);
                    
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200
                   
                    if($statusCode === 200){

                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        $this->addFlash("success", "Produit modifié sur le site de vente.");
                        
                        return $content;

                    }else{
                        $this->addFlash("danger", "Le produit n'a pas été modifié sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    $this->addFlash("danger",$e->getMessage());

                }
            break;
            case "categories":
                try{

                    $response =  $this->client->request(
                        "PUT",
                        $this->endpoint.'products/'.$target.'/'.$data->getWcCategoryId(), [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->username, $this->password],
                         "body" => [
                            "name" => $data->getName(),
                            "slug" => $data->getSlug(),
                            "description" => $data->getDescription()
                        ]
                    ]);
        
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200
                   
                    if($statusCode === 200){

                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        $this->addFlash("success", "Catégorie modifiée sur le site de vente.");

                        return $content;

                    }else{
                        $this->addFlash("danger", "La Catégorie n'a pas été modifiée sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                      $this->addFlash("danger",$e->getMessage());
                    
                }
            break;
                
        }

    }

    public function updateOneProductQuantity($wcPid, $quantity)
    {
        try{

            $this->client->request(
                "PUT",
                    $this->endpoint.'products/'.$wcPid, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password],
                    "body" => [
                        'stock_quantity' => $quantity,
                    ]
                ]);
        }catch(\Exception $e){
            throw $e;
        }
    }

        /**
     * @method update
     * @throws Exception $e
     * @return Response
     */
    public function getVariation($wcPid, $variationId)
    {
               
        try{
         
            $response = $this->client->request(
                    "POST",
                    $this->endpoint.'products/'.$wcPid.'/variations/'.$variationId, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password],
                ]);

                $statusCode = $response->getStatusCode();
                // $statusCode = 200
                if($statusCode === 200){

                    $contentType = $response->getHeaders()['content-type'][0];
                    // $contentType = 'application/json'
                    // $content = $response->getContent();
                    // $content = '{"id":521583, "name":"symfony-docs", ...}'
                    $content = $response->toArray();
                    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                    
                    return $content;
                }

        }catch(\Exception $e){
            throw $e;
        }

    }
    public function createProductVariations($wcPid, $product, $datas)
    {
               
        $array = [];
        $element = [];
        $variationArray = [];
        $shopVariation = [];
        try{
            $i = 0;
            foreach($datas['quantity'] as $quantityKey => $data){

                $element = [
                    'color' => $datas['color'][$quantityKey],
                    'length' => $datas['length'][$quantityKey],
                ];

                if(!in_array($element, $array)){
                    $array[$i] = $element;
                    $variationArray[$i] = $element;
                    $variationArray[$i]['quantity'] = $datas['quantity'][$quantityKey];
                    $variationArray[$i]['shop'] = $datas['shop'][$quantityKey];

                }else{
                    $key = array_search($element,$array);
                    $el = $variationArray[$key];
                    $quantity = $el['quantity'];
                    $quantity += $datas['quantity'][$quantityKey];
                    $variationArray[$key]['quantity'] = $quantity;
                }      

                $shopVariation[$i]['shop']= $datas['shop'][$quantityKey];
                $shopVariation[$i]['color'] = $datas['color'][$quantityKey];
                $shopVariation[$i]['length'] = $datas['length'][$quantityKey];
                $shopVariation[$i]['quantity'] = $datas['quantity'][$quantityKey];


                $i++;
            }


            $shopVariationId = [];
            $variationExist = false;
            foreach($variationArray as $key => $variation){

                    $variations = $this->manager->getRepository(ProductVariation::class)->findProductVariation($shopVariation[$key]['color'],$shopVariation[$key]['length'], $product, $shopVariation[$key]['shop']);
                    
                    foreach($variations as $v){
                        if($v->getVariationId()){
                            $variationExist = true;
                        }
                    }

                 
                    if(!$variationExist){
                        $response =   $this->client->request(
                            "POST",
                            $this->endpoint.'products/'.$wcPid.'/variations', [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->username, $this->password],
                            "body" => [
                                "regular_price" => $product->getSellingPrice(),
                                'stock_quantity' => $variation['quantity'],
                                'manage_stock' => true,
                                'optional_selected' => true,
                                'on_sale' => true,
                                'selected' => true,
                                "attributes" => [
                                    [
                                        'id' => 4,
                                        "name" => "Couleur",
                                        "slug" => "pa_couleur",
                                        "option" => $variation['color']
                                    ],
                                    [
                                        'id' => 1,
                                        "name" => "Taille",
                                        "slug" => "pa_taille",
                                        "option" => $variation['length']
    
                                    ]
                                ],
                            ]
                        ]);
                            
                        $statusCode = $response->getStatusCode();
                        // $statusCode = 200
                        if($statusCode === 201){
    
                            $contentType = $response->getHeaders()['content-type'][0];
                            // $contentType = 'application/json'
                            // $content = $response->getContent();
                            // $content = '{"id":521583, "name":"symfony-docs", ...}'
                            $content = $response->toArray();
                            // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                            $shopVariationId[$key]['shop'][$key] = $shopVariation[$key]['shop'];
                            $shopVariationId[$key]['color'][$key] = $shopVariation[$key]['color'];
                            $shopVariationId[$key]['length'][$key] = $shopVariation[$key]['length'];
                            $shopVariationId[$key]['quantity'][$key] = $shopVariation[$key]['quantity'];
                            $shopVariationId[$key]['variationId'][$key] = $content['id'];
    
                        }
                    }else{

                        $vClone = null;
                        $vExist = false;

                        foreach($variations as $v){
                            if($v->getVariationId()){
                                $vClone = clone($v);
                                $vExist = true;
                            }
                        }

                        if($vExist){
                            $newVariation = $this->manager->getRepository(ProductVariation::class)->find($vClone);
                          
                            $newVariation->setQuantity($newVariation->getQuantity() +  $variation['quantity']);

                            $product = $this->manager->getRepository(Product::class)->find($variation->getProduct());
                            
                            $product->setQuantity($newVariation->getQuantity());

                            $this->manager->persist($newVariation);
                            $this->manager->persist($product);

                            $this->updateOneVariationQuantity($product->getWcProductId(), $newVariation->getVariationId(),$newVariation->getQuantity());

                        }

                    }

          
            }

            // if(!$variationExist){
            //     foreach($shopVariation as $shopVKey => $shopV){
            //         foreach($shopVariationId as $key => $variationId){
                      
            //               if($variationId['shop'][$key] == $shopV['shop'] && $variationId['color'][$key] == $shopV['color'] && $variationId['length'][$key] == $shopV['length']){
            //                 $variations = $this->manager->getRepository(ProductVariation::class)->findProductVariation($shopV['color'],$shopV['length'], $product, $shopV['shop']);
                           
            //                 $vId = intval($variationId['variationId'][$key]);
            //                 $quantity = intval($variationId['quantity'][$key]);
    
            //                 $variationExist = false;
            //                 $variationObj = null;
            //                 $oldQuantity = 0;
            //                 foreach($variations as $variation){
    
            //                     if($variation->getVariationId() != null){
            //                         $variationExist = true;
            //                         $oldQuantity += $variation->getQuantity();
            //                         $variationObj = clone($variation);
            //                     }
    
            //                     if($variationExist && is_null($variation->getVariationId())){
                                  
            //                       $variationObj->setQuantity($oldQuantity + $quantity);
            //                       $product = $variationObj->getProduct();
    
            //                       $product->setQuantity($oldQuantity + $quantity);
                                
            //                       $this->manager->persist($variationObj);
            //                       $this->manager->persist($product);
            //                     }elseif(is_null($variation->getVariationId())){
            //                         $variation->setQuantity($quantity);
            //                         $product = $variation->getProduct();
      
            //                         $product->setQuantity($product->getQuantity() + $quantity);
            //                         $variation->setVariationId($vId);
    
            //                         $this->manager->persist($variation);
            //                         $this->manager->persist($product);
            //                     }
    
            //                 }
    
            //             }
            //         }
            //     }
                      
            // }
     

        }catch(\Exception $e){
            throw $e;
            $this->manager->rollback();
        }

    }

    public function updateOneVariationQuantity($wcPid,$variationId, $quantity)
    {
        try{

          $response =  $this->client->request(
                "PUT",
                    $this->endpoint.'products/'.$wcPid.'/variations/'.$variationId, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password],
                    "body" => [
                        'stock_quantity' => $quantity,
                    ]
                ]);

            $statusCode = $response->getStatusCode();
                // $statusCode = 200

                if($statusCode === 200){

                    $contentType = $response->getHeaders()['content-type'][0];
                    // $contentType = 'application/json'
                    $content = $response->getContent();
                    // $content = '{"id":521583, "name":"symfony-docs", ...}'
                    $content = $response->toArray();
                    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                    $this->addFlash("success", "La quantité du produit a été modifié sur le site de vente.");
                    
                    return $content;

                }
        }catch(\Exception $e){
            throw $e;
        }
    }

    public function updateProductVariations($wcPid,$product,$datas)
    {
        $array = [];
        $element = [];
        $variationArray = [];

        try{
            $i = 0;
            foreach($datas['quantity'] as $quantityKey => $data){
               
                $element = [
                    'color' => $datas['color'][$quantityKey],
                    'length' => $datas['length'][$quantityKey],
                ];

            
                if(!in_array($element, $array)){
                    $array[$i] = $element;
                    $variationArray[$i] = $element;
                    $variationArray[$i]['quantity'] = $datas['quantity'][$quantityKey];
                    $variationArray[$i]['variationId'] = $datas['variationId'];
                    
                }else{

                    $key = array_search($element,$array);
                    $el = $variationArray[$key];
                    $quantity = $el['quantity'];
                    $quantity += $datas['quantity'][$quantityKey];
                    $variationArray[$key]['quantity'] = $quantity;
                }      
                
                $i++;
            }

            foreach($variationArray as $key => $variation){
      
                $this->client->request(
                    "PUT",
                        $this->endpoint.'products/'.$wcPid.'/variations/'.$variation['variationId'][$key], [
                        // use a different HTTP Basic authentication only for this request
                        'auth_basic' => [$this->username, $this->password],
                        "body" => [
                            "regular_price" => $product->getSellingPrice(),
                            'manage_stock' => true,
                            'stock_quantity' => $variation['quantity'],
                            "attributes" => [
                                [
                                    'id' => 4,
                                    "option" => $variation['color']
                                ],
                                [
                                    'id' => 1,
                                    "option" => $variation['length']
        
                                ]
                            ],
                        ]
                    ]);
            }
        }catch(\Exception $e){
            throw $e;
        }

    }

    public function deleteProductVariations($wcPid, $variationId)
    {
        try{
            $response =   $this->client->request(
                "DELETE",
                    $this->endpoint.'products/'.$wcPid.'/variations/'.$variationId.'?force=true', [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->username, $this->password],
                ]);
    
                $statusCode = $response->getStatusCode();
                // $statusCode = 200
                if($statusCode === 200){
    
                    $contentType = $response->getHeaders()['content-type'][0];
                    // $content = '{"id":521583, "name":"symfony-docs", ...}'
                    $content = $response->toArray();
                    // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]

                   $this->addFlash('sucees', 'Variation supprimé sur le site de vente');

                }
    
        }catch(\Exception $e){
        }

    }

     /**
     * @method updateQ
     * @throws Exception $e
     * @return Response
     */
    public function putQ($target, $data)
    {
        switch($target){
            case "products":
                
                try{
                    $response =  $this->client->request(
                        "PUT",
                        $this->endpoint.''.$target.'/'.$data->getWcProductId(), [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->username, $this->password],
                         "body" => [
                            "stock_quantity" => $data->getQuantity(),
                        ]
                    ]);
                    
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200

                    if($statusCode === 200){

                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        $this->addFlash("success", "La quantité du produit a été modifié sur le site de vente.");
                        
                        return $content;

                    }else{
                        $this->addFlash("danger", "La quantité du produit n'a pas été modifié sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    $this->addFlash("danger",$e->getMessage());
                }
            break;
                
        }

    }

    /**
     * @method delete
     * @throws Exception $e
     * @return Response
     */
    public function delete($target, $data)
    {
        switch($target){
            case "products":
                try{

                    $response =  $this->client->request(
                           "DELETE",
                           $this->endpoint.''.$target.'/'.$data->getWcProductId(), [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->username, $this->password],
                       ]);
        
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200
                    if($statusCode === 200){
        
                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        
                       $this->addFlash("success", "Produit supprimé du site de vente!");

                       return $content;

                    }else{
                       $this->addFlash("danger", "Le produit n'a pas été supprimé du site de vente!");
                    }
                     
                   }catch(\Exception $e){
                    $this->addFlash("danger",$e->getMessage());

                   }
                break;
            case "categories":
                try{

                    $response =  $this->client->request(
                           "DELETE",
                           $this->endpoint.'products/'.$target.'/'.$data->getWcCategoryId(), [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->username, $this->password]
                       ]);
        
                    $statusCode = $response->getStatusCode();
                    // $statusCode = 200
                    if($statusCode === 200){
        
                        $contentType = $response->getHeaders()['content-type'][0];
                        // $contentType = 'application/json'
                        $content = $response->getContent();
                        // $content = '{"id":521583, "name":"symfony-docs", ...}'
                        $content = $response->toArray();
                        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                        
                       $this->addFlash("success", "Produit supprimé du site de vente!");

                       return $content;

                    }else{
                       $this->addFlash("danger", "Le produit n'a pas été supprimé du site de vente!");
                    }
                     
                   }catch(\Exception $e){
                    $this->addFlash("danger",$e->getMessage());

                   }
                break;

        }
        
    }
}
