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

        if($job == null || $job->getValid() == null || $job->getDone() != null || $job->getExpire() != null)
        {
            return $this->redirectToRoute('zephyr_job_homepage');
        }

        $owner = $job->getOwner();
        $list = $job->getCandidats();

        if($request->isMethod('POST'))
        {
            $em = $this->getDoctrine()->getManager();
            $um = $this->get('fos_user.user_manager');
            $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);
            $user = $this->get('security.context')->getToken()->getUser();

            if($job == null || $job->getValid() == null || $job->getDone() != null || $job->getExpire() != null)
            {
                return $this->redirectToRoute('zephyr_job_homepage');
            }

            $owner = $job->getOwner();
            $list = $job->getCandidats();
            $foo = explode('@', $user->getEmail());
            $mail = $foo[1];
            $this->get('request')->request->get('cv');

            if($mail != 'edu.esiee.fr')
            {
                return $this->render('ZephyrJobBundle:Default:job.html.twig', array(
                    'error' => "Seuls les élèves de l'ESIEE (inscrits avec une adresse @edu.esiee.fr) ont le droit de postuler aux jobs.",
                    'job' => $job,
                ));  
            }

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

            $message = \Swift_Message::newInstance()
                    ->setSubject('[Team Jobs] Confirmation')
                    ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                    ->setTo(array($job->getOwner()->getEmail() => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname()))
                    ->setCC(array(
                        //'alizee.perrin@edu.esiee.fr',
                        //'sarah.arnedoslopez@edu.esiee.fr'
                        'kevin.ta@edu.esiee.fr'
                        ))
                    ->setBody(
                        $this->renderView(
                            'ZephyrJobBundle:Email:email.html.twig',
                            array(
                                'name' => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname(),
                                'objet' => "Vous avez récemment postulé à un job, nous allons traiter votre demande sous peu.",
                                'job' => $job->getId(),
                                'lien' => 'https://bde.esiee.fr/job-board/job/'.$job->getId(),
                                'cv' => $request->request->get('cv')
                            )
                        )
                    );
                $this->get('mailer')->send($message);

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
        $foo = explode('@', $user->getEmail());
        $mail = $foo[1];
        $superadmin = $this->get('security.context')->getToken()->getRoles();

        if($mail == 'edu.esiee.fr')
        {
            if($superadmin[0] != 'ROLE_SUPER_ADMIN')
            {
                return $this->redirectToRoute('zephyr_job_homepage', array(
                    'error' => "Seuls les personnes de l'extérieur peuvent poster des jobs.",
                )); 
            }
        }

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

            $checkbox = $request->request->get('checkbox');
            $job->setValid(0);
            $job->setExpire(0);
            $job->setDone(0);

            if($superadmin[0] == 'ROLE_SUPER_ADMIN')
            {
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

            $job->setOwner($user);
            $job->setDate(new \DateTime());
            $em->persist($job);
            $em->flush();

            $message1 = \Swift_Message::newInstance()
                ->setSubject('[Team Jobs] Nouvelle annonce disponible')
                ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setTo(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setCC(array(
                    //'alizee.perrin@edu.esiee.fr',
                    //'sarah.arnedoslopez@edu.esiee.fr'
                    'kevin.ta@edu.esiee.fr'
                ))
                ->setBody(
                    $this->renderView(
                        'ZephyrJobBundle:Email:email.html.twig',
                        array(
                            'name' => 'la Team Jobs',
                            'objet' => "Une annonce vient d'arriver sur la plateforme Job. Vous êtes priés de bien vouloir y jeter un coup d'oeil.",
                            'job' => $job->getId(),
                            'lien' => 'https://bde.esiee.fr/job-board/admin/edit/'.$job->getId()
                        )
                    )
                );
            $this->get('mailer')->send($message1);

            $message2 = \Swift_Message::newInstance()
                ->setSubject('[Team Jobs] Confirmation')
                ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setTo(array($job->getOwner()->getEmail() => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname()))
                ->setCC(array(
                    //'alizee.perrin@edu.esiee.fr',
                    //'sarah.arnedoslopez@edu.esiee.fr'
                    'kevin.ta@edu.esiee.fr'
                ))
                ->setBody(
                    $this->renderView(
                        'ZephyrJobBundle:Email:email.html.twig',
                        array(
                            'name' => $job->getOwner()->getFirstname()." ".$job->getOwner()->getLastname(),
                            'objet' => "Nous avons bien reçu votre annonce, nous allons la traiter sous peu.",
                            'job' => $job->getId(),
                            'lien' => 'https://bde.esiee.fr/job-board/job/'.$job->getId()
                        )
                    )
                );
            $this->get('mailer')->send($message2);

            return $this->redirectToRoute('zephyr_job_homepage', array(
                'id' => $job->getId(),
                'success' => 'Votre demande va être étudiée par notre équipe Jobs.',
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
        $um = $this->get('fos_user.user_manager');
        $job = $em->getRepository('ZephyrJobBundle:Job')->findOneById($id);
        $user = $this->get('security.context')->getToken()->getUser();

        if($job == null || $user != $job->getOwner())
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

            $em->persist($job);
            $em->flush();

            if($job->getValid() == 1)
            {
                $message = \Swift_Message::newInstance()
                ->setSubject('[Team Jobs] Annonce modifiée')
                ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setTo(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setCC(array(
                    //'alizee.perrin@edu.esiee.fr',
                    //'sarah.arnedoslopez@edu.esiee.fr'
                    'kevin.ta@edu.esiee.fr'
                ))
                ->setBody(
                    $this->renderView(
                        'ZephyrJobBundle:Email:email.html.twig',
                        array(
                            'name' => 'la Team Jobs',
                            'objet' => "Une annonce vient d'être modifié sur la plateforme Job.",
                            'job' => $job->getId(),
                            'lien' => 'https://bde.esiee.fr/job-board/admin/edit/'.$job->getId()
                        )
                    )
                );
                $this->get('mailer')->send($message);

                return $this->redirectToRoute('zephyr_job_job', array(
                    'id' => $job->getId(),
                    'success' => 'Votre annonce a été éditée.',
                ));
            }

            $message = \Swift_Message::newInstance()
                ->setSubject('[Team Jobs] Nouvelle annonce disponible')
                ->setFrom(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setTo(array('bde@edu.esiee.fr' => 'BDE ESIEE Paris'))
                ->setCC(array(
                    //'alizee.perrin@edu.esiee.fr',
                    //'sarah.arnedoslopez@edu.esiee.fr'
                    'kevin.ta@edu.esiee.fr'
                ))
                ->setBody(
                    $this->renderView(
                        'ZephyrJobBundle:Email:email.html.twig',
                        array(
                            'name' => 'la Team Jobs',
                            'objet' => "Une annonce vient d'être modifié sur la plateforme Job.",
                            'job' => $job->getId(),
                            'lien' => 'https://bde.esiee.fr/job-board/admin/edit/'.$job->getId()
                        )
                    )
                );
            $this->get('mailer')->send($message);

            return $this->redirectToRoute('zephyr_job_homepage', array(
                'id' => $job->getId(),
                'success' => 'Votre annonce a été éditée.',
            ));
        }

        return $this->render('ZephyrJobBundle:Default:edit.html.twig', array(
            'form' => $form->createView(),
            'job' => $job,
        ));
    }
}
