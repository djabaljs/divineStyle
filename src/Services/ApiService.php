<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class ApiService  extends AbstractController
{
      //PRODUCTS REQUESTS INSIDE README

      private $client;

      public function __construct(HttpClientInterface $client)
      {
          $this->client = $client;
      }
  
      public function clientRequest($method, $target, $contentType = null, $body = null)
      {
          if(!is_null($body)){
              $response = $this->client->request(
                  $method,
                  'https://test.btest-ci.com/wp/wp-json/wc/v3/'.$target, [
                   // use a different HTTP Basic authentication only for this request
                   'auth_basic' => [$this->getParameter('WOOCOMMERCE_AUTH_USERNAME'), $this->getParameter('WOOCOMMERCE_AUTH_PASSWORD')],
                   'body' => $body
              ]);
  
          }else{
              $response = $this->client->request(
                  $method,
                  'https://test.btest-ci.com/wp/wp-json/wc/v3/'.$target, [
                   // use a different HTTP Basic authentication only for this request
                   'auth_basic' => [$this->getParameter('WOOCOMMERCE_AUTH_USERNAME'), $this->getParameter('WOOCOMMERCE_AUTH_PASSWORD')],
                  ]);
          }
         
  
          switch ($method) {
              case 'GET':
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
  
                  break;
              case 'POST':
                      $statusCode = $response->getStatusCode();
                      // $statusCode = 200
                      // if($statusCode === 200){
      
                          // $contentType = $response->getHeaders()['content-type'][0];
                          // // $contentType = 'application/json'
                          $content = $response->getContent();
                          // // $content = '{"id":521583, "name":"symfony-docs", ...}'
                          // $content = $response->toArray();
                          // // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
                  
                          return $content;
                  
                      // }
      
                      break;
              default:
                  # code...
                  break;
          }
    
      }
  
}