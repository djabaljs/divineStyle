<?php

namespace App\Services\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService{

    protected $session;
    protected $productRepository;

    /**
     * constructor
     * @param SessionInterface $session
     * @param ProductRepository $productRepository
     */
    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    /**
     * add method
     * @param $id
     * @return void
     */
    public function add($item){

        $items = $this->session->get('cart_item', []);
        
        if(!empty($items[$item['code']])){
            $items[] = $item;
        }else{
            $items[0] = $item;
        }

        $this->session->set('cart_item', $items);

    }

    /**
     * remove method
     * @param $id
     * @return void
     */
    public function remove($id){

        $items = $this->session->get('cart_item', []);

        if(!empty($items[$id]))
        {
            unset($items[$id]);

            $this->session->set('panier', $items);
        }
    }

    /**
     * getFullCart method
     * @return Array 
     */
    public function getFullCart(): array {

        $panierWithData = [];

        foreach($this->session->get('panier', []) as $id => $quantity){

            $panierWithData[] = [
                'product' => $this->productRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $panierWithData;
    }

    /**
     * getTotal method
     * @return Float
     */
    public function getTotal(): float {
        
        $total = 0;

        foreach($this->getFullCart() as $item){
            $productItem = $item['product']->getPrice() * $item['quantity'];

            $total += $productItem; 
        }

        return $total;

    }
}