<?php

namespace App\Services\Woocommerce;

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
                    throw $e;
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
                    throw $e;

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
    public function getOne($target, $id)
    {
        try{

            $response =  $this->client->request(
                   "GET",
                   $this->endpoint.''.$target.'?id='.$id, [
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

        /**
     * @method update
     * @throws Exception $e
     * @return Response
     */
    public function createProductVariations($wcPid, $product, $datas)
    {
    foreach($datas['quantity'] as $quantityKey => $data){
            $response =   $this->client->request(
            "POST",
                $this->endpoint.'products/'.$wcPid.'/variations', [
                // use a different HTTP Basic authentication only for this request
                'auth_basic' => [$this->username, $this->password],
                "body" => [
                    "regular_price" => $product->getSellingPrice(),
                    'stock_quantity' => $datas['quantity'][$quantityKey],
                    'manage_stock' => true,
                    'optional_selected' => true,
                    'on_sale' => true,
                    'selected' => true,
                    "attributes" => [
                        [
                            'id' => 4,
                            "name" => "Couleur",
                            "slug" => "pa_couleur",
                            "option" => $datas['color'][$quantityKey]
                        ],
                        [
                            'id' => 1,
                            "name" => "Taille",
                            "slug" => "pa_taille",
                            "option" => $datas['length'][$quantityKey]

                        ]
                    ],
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
                
                $variations = $this->manager->getRepository(ProductVariation::class)->findProductVariation($content['attributes'][0]['option'],$content['attributes'][1]['option'], $product);

                foreach($variations as $variation){
                    $variation->setVariationId($content['id']);
                    $this->manager->persist($variation);
                    $this->manager->flush();
                }
            }

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
