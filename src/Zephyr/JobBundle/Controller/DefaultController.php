<?php

namespace Zephyr\JobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
    	$jobs = $em->getRepository('ZephyrJobBundle:Job')->findAll();
    	$total = count($jobs);
    	$valid = 0;
    	for ($i = 0; $i < $total; $i++)
    	{
    		if ($status = $jobs[$i]->getValid() != null)
    		{
    			$valid = $valid + 1;
    		}
    	}

        return $this->render('ZephyrJobBundle:Default:index.html.twig', array(
            'jobs' => $jobs,
            'total' => $total,
            'valid' => $valid,
        ));
    }
}
