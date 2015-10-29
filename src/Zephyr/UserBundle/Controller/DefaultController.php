<?php

namespace Zephyr\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ZephyrUserBundle:Default:index.html.twig');
    }
}
