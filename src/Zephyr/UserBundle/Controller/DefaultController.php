<?php

namespace Zephyr\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zephyr\JobBundle\Entity\Job;
use Zephyr\JobBundle\Form\JobType;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ZephyrUserBundle:Default:index.html.twig');
    }

    public function adminAction()
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('ZephyrJobBundle:Job')->findAll();

        return $this->render('ZephyrUserBundle:Default:admin.html.twig', array(
            'job' => $job,
        ));
    }
}
