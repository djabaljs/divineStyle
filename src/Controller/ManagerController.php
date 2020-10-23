<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/manager")
 */
class ManagerController extends AbstractController
{
    /**
     * @Route("/dashboard", name="manager_dashboard")
     */
    public function index()
    {
      return $this->render('admin/dashboard.html.twig');
    }
}