<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\ActivityType;
use App\Service\ActivityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class ApiController extends AbstractController
{
    public function index()
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    /**
     * @Route("/api/auth/register", name="api-auth-register")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function register(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );
        $validator = Validation::createValidator();
        $constraint = new Assert\Collection(array(
            // the keys correspond to the keys in the input array
            'name' => new Assert\Length(array('min' => 1)),
            'firstname' => new Assert\Length(array('min' => 1)),
            'subcontractor' => new Assert\NotNull(),
            'password' => new Assert\Length(array('min' => 1)),
            'email' => new Assert\Email(),
        ));
        $violations = $validator->validate($data, $constraint);
        if ($violations->count() > 0) {
            return new JsonResponse(["error" => (string)$violations], 500);
        }
        $name = $data['name'];
        $firstname = $data['firstname'];
        $subcontractor = $data['subcontractor'];
        $password = $data['password'];
        $email = $data['email'];
        $user = new User();
        $user
            ->setName($name)
            ->setFirstName($firstname)
            ->setPassword($password)
            ->setEmail($email)
            ->setSubcontractor($subcontractor)
            ->setRoles(['ROLE_WORKER'])

        ;
        try {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
        return new JsonResponse(["success" => $user->getUsername(). " has been registered!"], 200);
    }


    /**
     * @Route("/api/auth/login", name="api-auth-login")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function login(Request $request){

    }

    /**
     * @Route("/api/me", name="api-me")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getMe(Request $request){
        $userObject = new class{};
        $userObject->id = $this->getUser()->getId();
        $userObject->name = $this->getUser()->getName();
        $userObject->firstname = $this->getUser()->getFirstName();


        return new JsonResponse( $userObject, 200);
    }

    /**
     * @Route("/api/activities/new", name="api-activities-new")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function registerActivity(Request $request)
    {
        $data = json_decode(
            $request->getContent(),
            true
        );

        $activity = new Activity();
        $activity->setCreatedAt(new \DateTime('Now'));
        $activity->setStartTime(new \DateTime($data["date"]." ".$data["start_time"]));
        $activity->setEndTime(new \DateTime($data["date"]." ".$data["start_time"]));
        $activity->setActivityDescription($data["description"]);
        $activity->setUsedMaterials($data["used_materials"]);
        $activity->setBreakLength($data["break_time"]);
        $activity->setTransportDistance($data["transport_distance"]);
        $activity->setUser($this->getUser());

        $repository = $this->getDoctrine()->getRepository(User::class);
        $customer = $repository->find($data["customer"]);
        $activity->setCustomer($customer);

        //if valid:
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($activity);
        $entityManager->flush();


        return new JsonResponse($activity->getId(), 200);
    }





    /**
     * @Route("/api/activities/{id}", name="api-activity-detail")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getActivity(int $id, Request $request, ActivityService $activityService){

        $repository = $this->getDoctrine()->getRepository(Activity::class);
        $activity = $repository->find($id);

        if(isset($activity)){
            /*
            $activity->weekday = $activityService->weekdayFromInt($activity->getStartTime()->format("w"));
            $activity->cost = $activityService->calculateCostPerActivity($activity);
            $activity->transportcost = $activityService->calculateTransportCostsPerActivity($activity);
            */
            $activity->hours = $activity->getStartTime()->diff($activity->getEndTime())->h;

            $serializedActivity = $this->serializeActivity($activity);


            return new JsonResponse( $serializedActivity, 200);
        }else{
            return new JsonResponse(null, 200);
        }

    }

    /**
     * @Route("/api/activities/user", name="api-activities-user")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function recentActivities(Request $request){

        $repository = $this->getDoctrine()->getRepository(User::class);
        $worker = $repository->find($this->getUser()->getId());

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findRecentActivitiesByWorker($worker);

        $serializedActivities = [];

        foreach ($activities as $activity) {
            $activity->hours = $activity->getStartTime()->diff($activity->getEndTime())->h;
           array_push($serializedActivities, $this->serializeActivity($activity));
        }

        return new JsonResponse($serializedActivities, 200);
    }


    /**
     * @Route("/api/activities/user/week", name="api-activities-user-week")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activitiesPerWeek(Request $request, ActivityService $activityService){
        //get all user activities of the current week

        $data = json_decode(
            $request->getContent(),
            true
        );

        $date = new \DateTime();
        if(isset($data["date"])) {
            $date = new \DateTime($data["date"]);
        }

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
            ->findAllUserActivitiesInWeek($year, $weekNr, $this->getUser());


        $formattedActivities = $this->formatActivities($activities);
        $totalHours = $this->getTotalHours($activities);

        $response = [];
        $response["activities"] = $formattedActivities;
        $response["weekNr"] = $weekNr;
        $response["year"] = $year;
        $response["thisMonday"] = $thisMonday->format("d-m-Y");
        $response["thisSunday"] = $thisSunday->format("d-m-Y");
        $response["totalHours"] = $totalHours;

        return new JsonResponse($response, 200);
    }


    /**
     * @Route("/api/activities/user/month", name="api-activities-user-month")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activitiesPerMonth(Request $request, ActivityService $activityService){
        //get all user activities of the current month

        $data = json_decode(
            $request->getContent(),
            true
        );

        $date = new \DateTime();
        if(isset($data["date"])) {
            $date = new \DateTime($data["date"]);
        }

        //get which month we are in
        $year = $date->format("o");
        $month = $date->format("n");

        $firstDay = date('Y-m-01', strtotime($date->format("Y-m-d")));
        $lastDay = date('Y-m-t', strtotime($date->format("Y-m-d")));

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllUserActivitiesInPeriod($firstDay, $lastDay, $this->getUser());


        $formattedActivities = $this->formatActivities($activities);
        $totalHours = $this->getTotalHours($activities);
        $monthTranslated = $this->translateMonth($month);

        $response = [];
        $response["activities"] = $formattedActivities;
        $response["month"] = $month;
        $response["year"] = $year;
        $response["monthTranslated"] = $monthTranslated;
        $response["totalHours"] = $totalHours;

        return new JsonResponse($response, 200);
    }


    /**
     * @Route("/api/activities/user/year", name="api-activities-user-year")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function activitiesPerYear(Request $request, ActivityService $activityService){
        //get all user activities of the current month

        $data = json_decode(
            $request->getContent(),
            true
        );

        $date = new \DateTime();
        if(isset($data["date"])) {
            $date = new \DateTime($data["date"]);
        }

        //get which year we are in
        $year = $date->format("o");

        $firstDay = date('Y-01-01', strtotime($date->format("Y-m-d")));
        $lastDay = date('Y-12-31', strtotime($date->format("Y-m-d")));

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllUserActivitiesInPeriod($firstDay, $lastDay, $this->getUser());


        $formattedActivities = $this->formatActivities($activities);
        $totalHours = $this->getTotalHours($activities);

        $response = [];
        $response["activities"] = $formattedActivities;
        $response["year"] = $year;
        $response["totalHours"] = $totalHours;

        return new JsonResponse($response, 200);
    }


    /**
     * @Route("/api/customers", name="api-customers")
     * @param Request $request
     * @param UserManagerInterface $userManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function getCustomers(Request $request, ActivityService $activityService){
        $repository = $this->getDoctrine()->getRepository(User::class);
        $customers = $repository->findByRole("ROLE_CUSTOMER");

        $serializedCustomers = [];
        foreach ($customers as $cust) {
            array_push($serializedCustomers, $this->serializeCustomer($cust));
        }

        return new JsonResponse($serializedCustomers, 200);
    }



    private function formatActivities($activities) {
        $serializedActivities = [];

        $activityService = new ActivityService();

        foreach ($activities as $act) {
            //assign weekday (1-7)
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);
            $act->hours = $act->getStartTime()->diff($act->getEndTime())->h;
            array_push($serializedActivities, $this->serializeActivity($act));
        }

        return $serializedActivities;
    }

    private function getTotalHours($activities) {
        $totalHours = 0;
        foreach ($activities as $act) {
            $totalHours += $act->hours;
        }

        return $totalHours;
    }


    private function serializeActivity(Activity $activity)
    {
        return array(
            'id' => $activity->getId(),
            'customer' => $this->serializeCustomer($activity->getCustomer()),
            'start_time' => $activity->getStartTime()->format("d/m/Y H:i"),
            'end_time' => $activity->getEndTime()->format("d/m/Y H:i"),
            'description' => $activity->getActivityDescription(),
            'used_materials' => $activity->getUsedMaterials(),
            'hours' => $activity->hours,
        );
    }

    private function serializeCustomer(User $customer)
    {
        return array(
            'id' => $customer->getId(),
            'companyName' => $customer->getCompanyName(),
        );
    }

    private function translateMonth($month){
        switch($month) {
            case 1:
                return "Januari";
                break;
            case 2:
                return "Februari";
                break;
            case 3:
                return "Maart";
                break;
            case 4:
                return "April";
                break;
            case 5:
                return "Mei";
                break;
            case 6:
                return "Juni";
                break;
            case 7:
                return "Juli";
                break;
            case 8:
                return "Augustus";
                break;
            case 9:
                return "September";
                break;
            case 10:
                return "Oktober";
                break;
            case 11:
                return "November";
                break;
            case 12:
                return "December";
                break;
        }
    }
}


