<?php

namespace Zephyr\JobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ZephyrJobBundle:Default:index.html.twig');
    }
}
