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
        $not_valid = 0;
    	for ($i = 0; $i < $total; $i++)
    	{
    		if ($jobs[$i]->getValid() != null and $jobs[$i]->getDone() == null and $jobs[$i]->getExpire() == null)
    		{
    			$valid = $valid + 1;
    		}
            if ($jobs[$i]->getValid() == null)
            {
                $not_valid = $not_valid + 1;
            }
    	}

        return $this->render('ZephyrJobBundle:Default:index.html.twig', array(
            'jobs' => $jobs,
            'notvalid' => $not_valid,
            'valid' => $valid,
        ));
    }
}
