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
            if ($jobs[$i]->getValid() == null and $jobs[$i]->getDone() == null and $jobs[$i]->getExpire() == null)
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
        $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);
        $user = $this->get('security.context')->getToken()->getUser();
        $already = null;
        $himself = null;

        if($job == null || $job->getValid() == null || $job->getDone() != null || $job->getExpire() != null)
        {
            return $this->redirectToRoute('zephyr_job_homepage');
        }

        $owner = $job->getOwner();
        $list = $job->getCandidats();

        $owner = $job->getOwner();
        $list = $job->getCandidats();

        if($user == $owner)
        {
            $himself = "Vous ne pouvez pas postuler à une annonce que vous avez créée.";
        }

        for($i = 0; $i < count($list); $i++)
        {
            if($user == $list[$i])
            {
                $already = "Vous avez déjà postulé à ce job.";
            }
        }

        if($request->isMethod('POST'))
        {
            if($himself == null && $already == null)
            {
                if($request->files->get('pdf')->getMimeType()=="application/pdf")
                {
                    $cvname = md5(uniqid()).'.'.$request->files->get('pdf')->guessExtension();
                    $cvdir = $this->container->getParameter('kernel.root_dir').'/../web/uploads/cv';
                    $request->files->get('pdf')->move($cvdir, $cvname);
                    $pdf = 'https://bde.esiee.fr/job-board/uploads/cv/'.$cvname;
                }
                else
                {
                    $pdf = 'Aucun CV PDF';
                }

                $job->AddCandidat($user);
                $em->persist($job);
                $em->flush();

                $message1 = \Swift_Message::newInstance()
                    ->setSubject('[Team Jobs] Une personne a postulé')
                    ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setTo(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setCC(array(
                        'louis.barnabe@edu.esiee.fr' => "Louis BARNABE",
                        'evelyne.davtian@edu.esiee.fr'  => "Evelyne DAVTIAN",
                    ))
                    ->setBody(
                        $this->renderView(
                            'ZephyrJobBundle:Email:email.html.twig',
                            array(
                                'name' => 'la Team Jobs',
                                'objet' => 'Une personne vient de postuler à une annonce. Vous êtes priés de bien vouloir y jeter un oeil.',
                                'job' => $job->getId(),
                                'cv' => $request->request->get('cv'),
                                'pdf' => $pdf,
                                'lien' => 'https://bde.esiee.fr/job-board/edit/'.$job->getId()
                            )
                        )
                    );
                $this->get('mailer')->send($message1);

                $message2 = \Swift_Message::newInstance()
                    ->setSubject('[Team Jobs] Confirmation')
                    ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setTo(array($job->getOwner()->getEmail() => ucfirst($job->getOwner()->getFirstname())." ".strtoupper($job->getOwner()->getLastname())))
                    ->setBody(
                        $this->renderView(
                            'ZephyrJobBundle:Email:email.html.twig',
                            array(
                                'name' => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname(),
                                'objet' => "Votre demande va être étudiée par notre équipe Jobs. Vous recevrez une réponse du BDE sous peu. Vous avez toujours la possibilité de modifier votre annonce via votre panel utilisateur.",
                                'job' => $job->getId(),
                                'cv' => $request->request->get('cv'),
                                'pdf' => $pdf,
                                'lien' => 'https://bde.esiee.fr/job-board/job/'.$job->getId()
                            )
                        )
                    );
                $this->get('mailer')->send($message2);

                return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
                    'success' => 'Votre demande va être étudiée par notre équipe Jobs. Vous allez recevoir une confirmation par mail.',
                    'job' => $job,
                ));
            }
        }

        return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
            'job' => $job,
            'himself' => $himself,
            'already' => $already,
        ));
    }

    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
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
            
            $job->setValid(0);
            $job->setExpire(0);
            $job->setDone(0);

            if($user->hasRole('ROLE_SUPER_ADMIN'))
            {
                if($request->request->get('checkbox') != null)
                {
                    $job->setValid(1);
                }
            }

            $job->setOwner($user);
            $job->setDate(new \DateTime());
            $em->persist($job);
            $em->flush();

            if(!$user->hasRole('ROLE_SUPER_ADMIN'))
            {
                $message1 = \Swift_Message::newInstance()
                    ->setSubject('[Team Jobs] Nouvelle annonce disponible')
                    ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setTo(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setCC(array(
                        'louis.barnabe@edu.esiee.fr' => "Louis BARNABE",
                        'evelyne.davtian@edu.esiee.fr'  => "Evelyne DAVTIAN",
                    ))
                    ->setBody(
                        $this->renderView(
                            'ZephyrJobBundle:Email:create.html.twig',
                            array(
                                'name' => 'la Team Jobs',
                                'objet' => 'Une annonce est arrivée sur la plateforme Job. Vous êtes priés de bien vouloir y jeter un oeil.',
                                'job' => $job->getId(),
                                'lien' => 'https://bde.esiee.fr/job-board/edit/'.$job->getId()
                            )
                        )
                    );
                $this->get('mailer')->send($message1);

                $message2 = \Swift_Message::newInstance()
                    ->setSubject('[Team Jobs] Confirmation')
                    ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setTo(array($job->getOwner()->getEmail() => ucfirst($job->getOwner()->getFirstname())." ".strtoupper($job->getOwner()->getLastname())))
                    ->setBody(
                        $this->renderView(
                            'ZephyrJobBundle:Email:create.html.twig',
                            array(
                                'name' => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname(),
                                'objet' => "Nous avons bien reçu votre annonce, nous allons la traiter sous peu. Une confirmation par mail vous sera renvoyé dès que votre annonce sera validé par le BDE.",
                                'job' => $job->getId(),
                                'lien' => 'https://bde.esiee.fr/job-board/job/'.$job->getId()
                            )
                        )
                    );
                $this->get('mailer')->send($message2);
            }

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
                'success' => 'Votre demande va être étudiée par notre équipe Jobs.',
                'jobs' => $jobs,
                'notvalid' => $not_valid,
                'valid' => $valid,
            ));
        }

        return $this->render('ZephyrJobBundle:Default:create.html.twig', array(
            'job' => $job,
            'form' => $form->createView(),
        ));
    }

    public function editAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);
        $user = $this->get('security.context')->getToken()->getUser();

        if($job == null)
        {
            return $this->redirectToRoute('zephyr_job_homepage');
        }
        else if($user->hasRole('ROLE_SUPER_ADMIN')) 
        {
        }
        else if($user != $job->getOwner())
        {
            return $this->redirectToRoute('zephyr_job_homepage');
        }

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
           
            $job->setValid(0);
            $job->setExpire(0);
            $job->setDone(0);
            
            if($user->hasRole('ROLE_SUPER_ADMIN'))
            {
                $checkbox = $request->request->get('checkbox');
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
            }

            $em->persist($job);
            $em->flush();

            if($user->hasRole('ROLE_SUPER_ADMIN'))
            {
                return $this->redirectToRoute('zephyr_admin');
            }
            else
            {
                return $this->redirectToRoute('fos_user_profile_show');
            }
        }

        return $this->render('ZephyrJobBundle:Default:edit.html.twig', array(
            'form' => $form->createView(),
            'job' => $job,
        ));
    }

    public function faqAction()
    {
        return $this->render('ZephyrJobBundle:Default:faq.html.twig');
    }
}
