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

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);

        if($job == null)
        {
            return $this->redirectToRoute('zephyr_admin');
        }

        $candidats = $job->getCandidats();

        $form = $this->createForm(new JobType($em), $job);

        if($request->isMethod('POST'))
        {
            $form->handleRequest($request);

            if(! $form->isValid())
            {
                return $this->render('ZephyrJobBundle:Default:edit.html.twig', array(
                    'error' => 'Erreur dans le formulaire.',
                    'job' => $job,
                ));
            }

            $checkbox = $request->request->get('checkbox');
            $job->setValid(0);
            $job->setExpire(0);
            $job->setDone(0);

            if($checkbox != null)
            {
                foreach($checkbox as $value)
                {
                    if($value == 'valid')
                    {
                        $job->setValid(1);
                    }
                    elseif($value == 'expire')
                    {
                        $job->setExpire(1);
                    }
                    elseif($value == 'done')
                    {
                        $job->setDone(1);
                    }
                }
            }

            $em->persist($job);
            $em->flush();

            $job = $em->getRepository('ZephyrJobBundle:Job')->findAll();

            return $this->render('ZephyrUserBundle:Default:admin.html.twig', array(
                'success' => 'Votre annonce a été éditée.',
                'job' => $job,
            ));
        }

        return $this->render('ZephyrJobBundle:Default:edit.html.twig', array(
            'form' => $form->createView(),
            'job' => $job,
        ));
    }
}
