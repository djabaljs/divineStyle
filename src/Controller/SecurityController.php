<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {

            
            $user = $this->getUser();
    
         
        if (in_array('ROLE_ADMIN', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())){
            return $this->redirectToRoute('admin_dashboard');
        } elseif (in_array('ROLE_MANAGER', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())) {
            if(!is_null($user->getShop())){
                return $this->redirectToRoute('manager_dashboard');
            }else{
                $this->addFlash("danger", "Vous n'ếtes pas associé à un magasin de vente!");
                return $this->redirectToRoute('app_logout');
            }
        } elseif (in_array('ROLE_DELIVERY_MAN', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())) {
            return $this->redirectToRoute('delivery_man_dashboard');
        } elseif (in_array('ROLE_SUPER_ADMIN', $user->getRoles()) && in_array('ROLE_USER', $user->getRoles())) {
            return $this->redirectToRoute('super_admin_dashboard');
        }
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}