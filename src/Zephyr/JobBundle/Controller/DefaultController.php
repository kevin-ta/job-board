<?php

namespace Zephyr\JobBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Zephyr\JobBundle\Entity\Job;
use Zephyr\JobBundle\Form\JobType;

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

    public function jobAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $um = $this->get('fos_user.user_manager');
        $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);
        $user = $this->get('security.context')->getToken()->getUser();
        $owner = $job->getOwner();
        $list = $job->getCandidats();

        if($job->getValid() == null || $job->getDone() != null || $job->getExpire() != null)
        {
            return $this->render('ZephyrJobBundle:Default:success.html.twig', array(
                'error' => 'Job invalide',
            ));
        }

        if($request->isMethod('POST'))
        {
            if($user == $owner)
            {
                return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
                    'error' => 'Vous ne pouvez pas postuler à une annonce que vous avez créée.',
                    'job' => $job,
                ));
            }
            
            for($i = 0; $i < count($list); $i++)
            {
                if($user == $list[$i])
                {
                    return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
                        'error' => 'Vous avez déjà postulé à ce job.',
                        'job' => $job,
                    ));
                }
            }

            $job->AddCandidat($user);
            $em->persist($job);
            $em->flush();

            return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
                'success' => 'Votre demande va être étudiée par notre équipe Jobs.',
                'job' => $job,
            ));
        }

        return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
            'job' => $job,
        ));
    }

    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $um = $this->get('fos_user.user_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $job = new Job();
        $form = $this->createForm(new JobType($em), $job);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if(! $form->isValid())
            {
                return $this->render('ZephyrJobBundle:Default:create.html.twig', array(
                    'error' => 'Erreur dans le formulaire.',
                    'job' => $job,
                ));
            }

            $job->setOwner($user);
            $em->persist($job);
            $em->flush();

            return $this->render('ZephyrJobBundle:Default:create.html.twig', array(
                'success' => 'Votre demande va être étudiée par notre équipe Jobs.',
                'job' => $job,
            ));
        }

        return $this->render('ZephyrJobBundle:Default:create.html.twig', array(
            'job' => $job,
            'form' => $form->createView(),
        ));
    }
}
