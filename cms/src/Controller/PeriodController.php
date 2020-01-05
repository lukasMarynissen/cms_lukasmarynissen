<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Comment;
use App\Entity\Period;
use App\Form\CommentType;
use App\Form\PeriodType;
use Doctrine\DBAL\Types\ArrayType;
//use Dompdf\Dompdf;
//use Dompdf\Options;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Rate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use App\Service\ActivityService;

// Include PhpSpreadsheet required namespaces
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PeriodController extends AbstractController
{

    /*
     * ADMIN
     */

    /**
     * @Route("/admin/periods", name="admin-periods")
     */
    public function index()
    {

        $periods = $this->getDoctrine()
            ->getRepository(Period::class)
            ->findRecentPeriods();

        return $this->render('admin/period/index.html.twig', [
            'controller_name' => 'PeriodController',
            'periods' => $periods,
        ]);

    }

    /**
     * @Route("admin/periods/new", name="admin-period-new")
     */
    public function registerPeriod(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $period = new Period();
        $period->setConfirmed(false);
        $period->setPublished(false);
        $period->setCreatedAt(new \DateTime('Now'));
        $form = $this->createForm(PeriodType::class, $period);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $period = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($period);
            $entityManager->flush();

            return $this->redirectToRoute('admin-period-detail', ['id' => $period->getId()]);
        }

        return $this->render('admin/period/register.html.twig', [
            'controller_name' => 'PeriodController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/periods/edit/{id}", name="admin-period-edit")
     */
    public function edit(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        $form = $this->createForm(PeriodType::class, $period);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $period = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($period);
            $entityManager->flush();

            return $this->redirectToRoute('admin-period-detail', ['id' => $period->getId()]);
        }

        return $this->render('admin/period/edit.html.twig', [
            'controller_name' => 'CustomerController',
            'period' => $period,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/periods/publish/{id}", name="admin-period-publish")
     */
    public function publish(int $id, Request $request, \Swift_Mailer $mailer)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        //send mail if the period is about to be published
        if($period->getPublished() == false){
            $message = (new \Swift_Message('Nieuwe periode beschikbaar'))
                ->setFrom('artetech@gmail.com')
                ->setTo($_ENV['TEST_MAIL_RECIPIENT'])
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'email/period.html.twig',
                        ['name' => $period->getCustomer()->getName(),
                            'firstname' => $period->getCustomer()->getFirstName(),
                            'id' => $period->getId()]
                    ),
                    'text/html'
                );

            $mailer->send($message);
        }

        $period->setPublished(!$period->getPublished());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($period);
        $entityManager->flush();

        return $this->redirectToRoute('admin-period-detail', ['id' => $period->getId()]);
    }

    /**
     * @Route("/admin/periods/delete/{id}", name="admin-period-delete")
     */
    public function delete(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        return $this->render('admin/period/delete.html.twig', [
            'controller_name' => 'CustomerController',
            'period' => $period
        ]);
    }

    /**
     * @Route("/admin/periods/confirmDelete/{id}", name="admin-period-confirm-delete")
     */
    public function confirmDelete(int $id){

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);
        $customer = $period->getCustomer();
        $entityManager->remove($period);
        $entityManager->flush();

        return $this->redirectToRoute('admin-customer-detail', ['id' => $customer->getId()]);
    }


    /**
     * @Route("/admin/periods/xls/{id}", name="admin-period-detail-xls")
     */
    public function getxls(int $id, ActivityService $activityService){

       // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        //get all activities of the current week

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllActivitiesInPeriodByCustomer($period->getStartTime(), $period->getEndTime(), $period->getCustomer()->getId());


        $totalHourlyCost = 0;
        $totalTransportCost = 0;
        foreach ($activities as $act) {
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);
            $act->totalcost = $act->cost + $act->transportcost;
            $act->hours = $act->getStartTime()->diff($act->getEndTime())->h;
            //add up all costs
            $totalHourlyCost += $act->cost;
            $totalTransportCost += $act->transportcost;
        }
        $totalCost = $totalHourlyCost + $totalTransportCost;


        $activitiesArray = [];
        $activitiesArray[0] = ["Dag", "Van", "Tot", "Medewerker","Uren", "Uurkost", "Transportkost","Totale kost" ];
        $counter = 1;
        foreach ($activities as $act) {
            $activitiesArray[$counter]= [$act->getStartTime()->format("d/m/Y"), $act->getStartTime()->format("H:i:s"),  $act->getEndTime()->format("H:i:s"),  $act->getUser()->getName()." ".$act->getUser()->getFirstName(), $act->hours, "€".$act->cost, "€".$act->transportcost, "€".$act->totalcost];
            $counter++;
        }

        $spreadsheet = new Spreadsheet();

        //set header to bold
        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('B8:I8')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('B5')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('D5')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('F5')->applyFromArray($headerStyleArray);


        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Periodeverslag");

        $sheet->setCellValue('A1', $period->getCustomer()->getCompanyName());
        $sheet->setCellValue('A2', "van ".$act->getStartTime()->format("d/m/Y")." tot ".$act->getEndTime()->format("d/m/Y"));
        $sheet->setCellValue('B5', "Totale uurkost");
        $sheet->setCellValue('B6', "€".$totalHourlyCost);
        $sheet->setCellValue('D5', "Totale transportkost");
        $sheet->setCellValue('D6', "€".$totalTransportCost);
        $sheet->setCellValue('F5', "Totale kost");
        $sheet->setCellValue('F6', "€".$totalCost);
        $sheet->fromArray(
                $activitiesArray,  // The data to set
                NULL,        // Array values with this value will not be set
                'B8'         // Top left coordinate of the worksheet range where
            //    we want to set these values (default is A1)
            );

        $writer = new Xlsx($spreadsheet);
        $fileName = 'periodeverslag.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }


    /**
     * @Route("/admin/periods/{id}", name="admin-period-detail")
     * @throws \Exception
     */
    public function detail(int $id, ActivityService $activityService, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        //get all activities of the current week

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllActivitiesInPeriodByCustomer($period->getStartTime(), $period->getEndTime(), $period->getCustomer()->getId());

        $totalHourlyCost = 0;
        $totalTransportCost = 0;
        foreach ($activities as $act) {
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);

            //add up all costs
            $totalHourlyCost += $act->cost;
            $totalTransportCost += $act->transportcost;
        }
        $totalCost = $totalHourlyCost + $totalTransportCost;

        //get comments
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findAllCommentsByPeriod($period);

        //new comment form
        $comment = new Comment();
        $comment->setPeriod($period);
        $comment->setCreatedAt(new \DateTime('Now'));
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('admin-period-detail', ['id' => $period->getId()]);
        }




        return $this->render('admin/period/detail.html.twig', [
            'controller_name' => 'PeriodController',
            'activities' => $activities,
            'period' => $period,
            'totalHourlyCost' => $totalHourlyCost,
            'totalTransportCost' => $totalTransportCost,
            'totalCost' => $totalCost,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }



    /*
     * CUSTOMER
     */
    /**
     * @Route("/customer", name="customer-index")
     */
    public function customerIndex()
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        //if user is an admin redirect to admin portal
        if (in_array("ROLE_ADMIN", $user->getRoles())){
            return $this->redirect($this->generateUrl('admin-index'));
        }

        $periods = $this->getDoctrine()
            ->getRepository(Period::class)
            ->findAllPublishedPeriodsPerCustomer($user->getId());

        return $this->render('customer/index.html.twig', [
            'controller_name' => 'PeriodController',
            'periods' => $periods,
        ]);

    }

    /**
     * @Route("/customer/periods/{id}", name="customer-period-detail")
     * @throws \Exception
     */
    public function customerPeriodDetail(int $id, ActivityService $activityService, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        //get all activities of the current week

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllActivitiesInPeriodByCustomer($period->getStartTime(), $period->getEndTime(), $period->getCustomer()->getId());


        $totalHourlyCost = 0;
        $totalTransportCost = 0;
        foreach ($activities as $act) {
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);

            //add up all costs
            $totalHourlyCost += $act->cost;
            $totalTransportCost += $act->transportcost;
        }
        $totalCost = $totalHourlyCost + $totalTransportCost;


        //get comments
        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findAllCommentsByPeriod($period);

        //new comment form
        $comment = new Comment();
        $comment->setPeriod($period);
        $comment->setCreatedAt(new \DateTime('Now'));
        $comment->setAuthor($this->getUser());

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('customer-period-detail', ['id' => $period->getId()]);
        }


        return $this->render('customer/period/detail.html.twig', [
            'controller_name' => 'PeriodController',
            'activities' => $activities,
            'period' => $period,
            'totalHourlyCost' => $totalHourlyCost,
            'totalTransportCost' => $totalTransportCost,
            'totalCost' => $totalCost,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/customer/periods/confirm/{id}", name="customer-period-confirm")
     */
    public function confirm(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CUSTOMER');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        $period->setConfirmed(!$period->getConfirmed());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($period);
        $entityManager->flush();

        return $this->redirectToRoute('customer-period-detail', ['id' => $period->getId()]);
    }

    /**
     * @Route("/customer/periods/xls/{id}", name="customer-period-detail-xls")
     */
    public function customergetxls(int $id, ActivityService $activityService){

        // $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($id);

        //get all activities of the current week

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findAllActivitiesInPeriodByCustomer($period->getStartTime(), $period->getEndTime(), $period->getCustomer()->getId());


        $totalHourlyCost = 0;
        $totalTransportCost = 0;
        foreach ($activities as $act) {
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);
            $act->totalcost = $act->cost + $act->transportcost;
            $act->hours = $act->getStartTime()->diff($act->getEndTime())->h;
            //add up all costs
            $totalHourlyCost += $act->cost;
            $totalTransportCost += $act->transportcost;
        }
        $totalCost = $totalHourlyCost + $totalTransportCost;


        $activitiesArray = [];
        $activitiesArray[0] = ["Dag", "Van", "Tot", "Medewerker","Uren", "Uurkost", "Transportkost","Totale kost" ];
        $counter = 1;
        foreach ($activities as $act) {
            $activitiesArray[$counter]= [$act->getStartTime()->format("d/m/Y"), $act->getStartTime()->format("H:i:s"),  $act->getEndTime()->format("H:i:s"),  $act->getUser()->getName()." ".$act->getUser()->getFirstName(), $act->hours, "€".$act->cost, "€".$act->transportcost, "€".$act->totalcost];
            $counter++;
        }

        $spreadsheet = new Spreadsheet();

        //set header to bold
        $headerStyleArray = [
            'font' => [
                'bold' => true,
            ],
        ];
        $spreadsheet->getActiveSheet()->getStyle('B8:I8')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('B5')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('D5')->applyFromArray($headerStyleArray);
        $spreadsheet->getActiveSheet()->getStyle('F5')->applyFromArray($headerStyleArray);


        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle("Periodeverslag");

        $sheet->setCellValue('A1', $period->getCustomer()->getCompanyName());
        $sheet->setCellValue('A2', "van ".$act->getStartTime()->format("d/m/Y")." tot ".$act->getEndTime()->format("d/m/Y"));
        $sheet->setCellValue('B5', "Totale uurkost");
        $sheet->setCellValue('B6', "€".$totalHourlyCost);
        $sheet->setCellValue('D5', "Totale transportkost");
        $sheet->setCellValue('D6', "€".$totalTransportCost);
        $sheet->setCellValue('F5', "Totale kost");
        $sheet->setCellValue('F6', "€".$totalCost);
        $sheet->fromArray(
            $activitiesArray,
            NULL,
            'B8'
        );

        $writer = new Xlsx($spreadsheet);
        $fileName = 'periodeverslag.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);
        $writer->save($temp_file);
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);

    }

}
