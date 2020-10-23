<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/delivery-man")
 */
class DeliveryManController extends AbstractController
{
    /**
     * @Route("/dashboard", name="delivery_man_dashboard")
     */
    public function index()
    {
      return $this->render('admin/dashboard.html.twig');
    }
}