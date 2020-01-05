<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\User;
use App\Form\ActivityType;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;
use App\Service\ActivityService;

class AdminActivityController extends AbstractController
{

    /**
     * @Route("/admin/activities", name="admin-activities")
     */
    public function index(ActivityService $activityService)
    {

        //get all activities of the current week
        $date = new \DateTime('2020-1-1');

        //get which week we are in, what day is monday
        $year = $date->format("o");
        $weekNr = $date->format("W");

        //dd($thisMonday, $nextMonday);
        //$thisMonday = new \DateTime(date('Y-m-d', strtotime('this monday')));
        //$nextMonday = new \DateTime(date('Y-m-d', strtotime('next monday')));

        $thisMonday = new \DateTime(date('Y-m-d',strtotime($year.'W'.$weekNr)));
        $thisSunday = new \DateTime( $thisMonday->format('Y-m-d').' this Sunday');

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllActivitiesInWeek($year, $weekNr);


        foreach ($activities as $act) {
            //assign weekday (1-7)
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);
        }



        return $this->render('admin/activity/index.html.twig', [
            'controller_name' => 'AdminActivityController',
            'activities' => $activities,
            'weekNr' => $weekNr,
            'thisMonday' => $thisMonday,
            'thisSunday' => $thisSunday
        ]);

    }

    /**
     * @Route("admin/activities/new", name="admin-activity-new")
     */
    public function registerActivity(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $activity = new Activity();
        $activity->setCreatedAt(new \DateTime('Now'));
        $activity->setStartTime(new \DateTime('Now'));
        $activity->setEndTime(new \DateTime('Now'));


        $form = $this->createForm(ActivityType::class, $activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('admin-activity-detail', ['id' => $activity->getId()]);
        }


        return $this->render('admin/activity/register.html.twig', [
            'controller_name' => 'AdminActivityController',
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/activities/edit/{id}", name="admin-activity-edit")
     */
    public function edit(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activity = $repository->find($id);

        $form = $this->createForm(ActivityType::class, $activity);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $activity = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($activity);
            $entityManager->flush();

            return $this->redirectToRoute('admin-activity-detail', ['id' => $activity->getId()]);
        }

        return $this->render('admin/activity/edit.html.twig', [
            'controller_name' => 'CustomerController',
            'activity' => $activity,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/activities/delete/{id}", name="admin-activity-delete")
     */
    public function delete(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activity = $repository->find($id);

        return $this->render('admin/activity/delete.html.twig', [
            'controller_name' => 'ActivityController',
            'activity' => $activity
        ]);
    }

    /**
     * @Route("/admin/activities/confirmDelete/{id}", name="admin-activity-confirm-delete")
     */
    public function confirmDelete(int $id){

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activity = $repository->find($id);
        $customer = $activity->getCustomer();
        $entityManager->remove($activity);
        $entityManager->flush();

        return $this->redirectToRoute('admin-activities');
    }


    /**
     * @Route("/admin/activities/{id}", name="admin-activity-detail")
     */
    public function detail(int $id, ActivityService $activityService)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activity = $repository->find($id);


        $activity->weekday = $activityService->weekdayFromInt($activity->getStartTime()->format("w"));
        $activity->cost = $activityService->calculateCostPerActivity($activity);
        $activity->transportcost = $activityService->calculateTransportCostsPerActivity($activity);
        $activity->hours = $activity->getStartTime()->diff($activity->getEndTime())->h;

        return $this->render('admin/activity/detail.html.twig', [
            'controller_name' => 'AdminActivityController',
            'activity' => $activity
        ]);
    }

}