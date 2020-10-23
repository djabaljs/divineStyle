<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user/super-admin")
 */
class SuperAdminController extends AbstractController
{
    /**
     * @Route("/dashboard", name="super_admin_dashboard")
     */
    public function index()
    {
      return $this->render('admin/dashboard.html.twig');
    }
}