<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Form\WorkerType;
use App\Service\ActivityService;
use Doctrine\DBAL\Types\ArrayType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class WorkerController extends AbstractController
{
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/admin/workers", name="admin-worker-index")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $workers = $repository->findByRole("ROLE_WORKER");

        return $this->render('admin/worker/index.html.twig', [
            'controller_name' => 'WorkerController',
            'workers' => $workers
        ]);
    }

    /**
     * @Route("admin/workers/new", name="admin-worker-new")
     */
    public function registerWorker(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $worker = new User();
        $worker->setCompanyName("/");
        $form = $this->createForm(WorkerType::class, $worker);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $worker = $form->getData();
            $worker->setRoles(["ROLE_WORKER"]);
            $worker->setPassword($this->passwordEncoder->encodePassword(
                $worker,
                $worker->getPassword()
            ));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($worker);
            $entityManager->flush();

            return $this->redirectToRoute('admin-worker-detail', ['id' => $worker->getId()]);
        }


        return $this->render('admin/worker/register.html.twig', [
            'controller_name' => 'WorkerController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/workers/edit/{id}", name="admin-worker-edit")
     */
    public function edit(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $worker = $repository->find($id);

        $form = $this->createForm(WorkerType::class, $worker);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $worker = $form->getData();
            $worker->setRoles(["ROLE_WORKER"]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($worker);
            $entityManager->flush();

            return $this->redirectToRoute('admin-worker-detail', ['id' => $worker->getId()]);
        }

        return $this->render('admin/worker/edit.html.twig', [
            'controller_name' => 'WorkerController',
            'worker' => $worker,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/workers/delete/{id}", name="admin-worker-delete")
     */
    public function delete(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $worker = $repository->find($id);

        return $this->render('admin/worker/delete.html.twig', [
            'controller_name' => 'WorkerController',
            'worker' => $worker
        ]);
    }

    /**
     * @Route("/admin/workers/confirmDelete/{id}", name="admin-worker-confirm-delete")
     */
    public function confirmDelete(int $id){

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $worker = $repository->find($id);
        $entityManager->remove($worker);
        $entityManager->flush();

        return $this->redirectToRoute('admin-worker-index');
    }

    /**
     * @Route("/admin/workers/changePassword/{id}", name="admin-worker-change-password")
     */
    public function changePassword(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);
        $worker = $repository->find($id);

        //$form = $this->createForm(WorkerType::class, $worker);

        $form = $this->createFormBuilder()
            ->add('password', TextType::class, [
                'constraints' => new NotBlank(),
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $worker->setPassword($this->passwordEncoder->encodePassword(
                $worker,
                $form->getData()["password"]
            ));

            $entityManager->flush();

            return $this->redirectToRoute('admin-worker-detail', ['id' => $worker->getId()] );
        }

        return $this->render('admin/worker/changePassword.html.twig', [
            'controller_name' => 'WorkerController',
            'worker' => $worker,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/workers/{id}", name="admin-worker-detail")
     */
    public function detail(int $id, ActivityService $activityService)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $worker = $repository->find($id);

        $activities = $this->getDoctrine()
            ->getRepository(Activity::class)
            ->findRecentActivitiesByWorker($worker);

        foreach ($activities as $act) {
            //assign weekday (1-7)
            $act->weekday = $act->getStartTime()->format("N");
            $act->cost = $activityService->calculateCostPerActivity($act);
            $act->transportcost = $activityService->calculateTransportCostsPerActivity($act);
        }

        return $this->render('admin/worker/detail.html.twig', [
            'controller_name' => 'WorkerController',
            'worker' => $worker,
            'activities' => $activities
        ]);
    }
}
