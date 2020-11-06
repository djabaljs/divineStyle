<?php

namespace App\Services\Woocommerce;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class WoocommerceApiService  extends AbstractController
{
      //PRODUCTS REQUESTS INSIDE README

      protected $client;

      public function __construct(HttpClientInterface $client)
      {
          $this->client = $client;
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
                    $response =  $this->client->request(
                        "POST",
                        $this->getParameter('TEST_API_ENDPOINT').''.$target, [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
                         "body" => [
                            "name" => $data->getName(),
                            "slug" => $data->getSlug(),
                            "price" => $data->getBuyingPrice(),
                            "regular_price" => $data->getSellingPrice(),
                            "sale_price" => $data->getSellingPrice(),
                            "manage_stock" => true,
                            "stock_quantity" => $data->getQuantity(),
                            "status" => "pending",
                            "categories" => [
                               [
                                   "id" => $data->getCategory()->getWcCategoryId()
                               ]
                            ]
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
                        $this->addFlash("success", "Produit crée sur le site de vente.");
                        
                        return $content;

                    }else{
                        $this->addFlash("danger", "Le produit n'a pas été crée sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    throw $e;
                }
            break;
            case "categories":
                try{

                    $response =  $this->client->request(
                        "POST",
                        $this->getParameter('TEST_API_ENDPOINT').'products/'.$target, [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
                   $this->getParameter('TEST_API_ENDPOINT').''.$target, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
               throw $e;
           }
    }

    /**
     * @method getOne
     * @throws Exception $e
     * @return Response
     */
    public function getOne($target, $slug)
    {
        try{

            $response =  $this->client->request(
                   "GET",
                   $this->getParameter('TEST_API_ENDPOINT').''.$target.'?slug='.$slug, [
                    // use a different HTTP Basic authentication only for this request
                    'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
               throw $e;
           }
    }

    /**
     * @method update
     * @throws Exception $e
     * @return Response
     */
    public function put($target, $id, $data)
    {
        switch($target){
            case "products":
                try{

                    $response =  $this->client->request(
                        "PUT",
                        $this->getParameter('TEST_API_ENDPOINT').''.$target.'/'.$id, [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
                         "body" => [
                            "name" => $data->getName(),
                            "slug" => $data->getSlug(),
                            "price" => $data->getBuyingPrice(),
                            "regular_price" => $data->getSellingPrice(),
                            "sale_price" => $data->getSellingPrice(),
                            "manage_stock" => true,
                            "stock_quantity" => $data->getQuantity(),
                            "status" => "pending", 
                            "categories" => [
                                'id' => $data->getCategory()->getWcCategoryId()
                            ]
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
                        $this->addFlash("success", "Produit crée sur le site de vente.");
                        
                        return $content;

                    }else{
                        $this->addFlash("danger", "Le produit n'a pas été crée sur le site de vente.");
                    }
                  
                }catch(\Exception $e){
                    throw $e;
                }
            break;
            case "categories":
                try{

                    $response =  $this->client->request(
                        "PUT",
                        $this->getParameter('TEST_API_ENDPOINT').'products/'.$target.'/'.$id, [
                         // use a different HTTP Basic authentication only for this request
                         'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
                    throw $e;
                }
            break;
                
        }

    }

    /**
     * @method delete
     * @throws Exception $e
     * @return Response
     */
    public function delete($target, $id)
    {
        switch($target){
            case "products":
                try{

                    $response =  $this->client->request(
                           "DELETE",
                           $this->getParameter('TEST_API_ENDPOINT').''.$target.'/'.$id, [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
                       throw $e;
                   }
                break;
            case "categories":
                try{

                    $response =  $this->client->request(
                           "DELETE",
                           $this->getParameter('TEST_API_ENDPOINT').'products/'.$target.'/'.$id, [
                            // use a different HTTP Basic authentication only for this request
                            'auth_basic' => [$this->getParameter('TEST_USERNAME'), $this->getParameter('TEST_PASSWORD')],
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
                       throw $e;
                   }
                break;

        }
        
    }
}
