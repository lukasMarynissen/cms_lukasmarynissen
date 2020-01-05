<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Entity\Activity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;


class ActivityController extends AbstractController
{

    /**
     * @Route("/activity/save", name="activity-save")
     */
    public function createActivity()
    {
        $entityManager = $this->getDoctrine()->getManager();

        $activity = new Activity();
        $activity->setUserId(0);
        $activity->setStartTime(new \DateTime('NOW'));
        $activity->setEndTime(new \DateTime('NOW'));
        $activity->setBreakLength(15);
        $activity->setCustomerId(1);
        $activity->setUsedMaterials("screwdriver, pear");
        $activity->setTransportDistance(12);
        $activity->setActivityDescription("Screwed some pears");


        // tell Doctrine you want to (eventually) save the Activity (no queries yet)
        $entityManager->persist($activity);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        return new Response('Saved new activity with id '.$activity->getId());
    }

    /**
     * @Route("/admin", name="admin-index")
     */
    public function adminIndex(){


        return $this->redirect($this->generateUrl('admin-activities'));

    }

    /**
     * @Route("/", name="redirect-to-login")
     */
    public function redirectToLogin(){

        return $this->redirect($this->generateUrl('app_login'));

    }


}
