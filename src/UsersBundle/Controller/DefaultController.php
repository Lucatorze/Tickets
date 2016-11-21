<?php

namespace UsersBundle\Controller;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/register")
     */
    public function indexAction()
    {
        return $this->render('UsersBundle:Registration:register.html.twig');
    }
}
