<?php

namespace App\Controller;

use App\Entity\Period;
use App\Form\CustomerType;
use Doctrine\DBAL\Types\ArrayType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Rate;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;



class CustomerController extends AbstractController
{

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }
    /**
     * @Route("/admin/customers", name="admin-customer-index")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $customers = $repository->findByRole("ROLE_CUSTOMER");

        return $this->render('admin/customer/index.html.twig', [
            'controller_name' => 'CustomerController',
            'customers' => $customers
        ]);
    }

    /**
     * @Route("admin/customers/new", name="admin-customer-new")
     */
    public function registerCustomer(Request $request)
    {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $customer = new User();
        $form = $this->createForm(CustomerType::class, $customer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customer = $form->getData();
            $customer->setRoles(["ROLE_CUSTOMER"]);
            $customer->setPassword($this->passwordEncoder->encodePassword(
                $customer,
                $customer->getPassword()
            ));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customer);
            $entityManager->flush();

            //set standard hourly/transport rates
            $rate = new Rate();
            $rate->setCustomer($customer);
            $rate->setHourlyRate(35);
            $rate->setTransportCostRate(0.2);
            $entityManager->persist($rate);
            $entityManager->flush();


            return $this->redirectToRoute('admin-customer-detail', ['id' => $customer->getId()]);
        }


        return $this->render('admin/customer/register.html.twig', [
            'controller_name' => 'CustomerController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/customers/edit/{id}", name="admin-customer-edit")
     */
    public function edit(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $customer = $repository->find($id);

        $form = $this->createForm(CustomerType::class, $customer);


        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $customer = $form->getData();
            $customer->setRoles(["ROLE_CUSTOMER"]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($customer);
            $entityManager->flush();

            return $this->redirectToRoute('admin-customer-detail', ['id' => $customer->getId()]);
        }

        return $this->render('admin/customer/edit.html.twig', [
            'controller_name' => 'CustomerController',
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/customers/delete/{id}", name="admin-customer-delete")
     */
    public function delete(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $customer = $repository->find($id);

        return $this->render('admin/customer/delete.html.twig', [
            'controller_name' => 'CustomerController',
            'customer' => $customer
        ]);
    }

    /**
     * @Route("/admin/customers/confirmDelete/{id}", name="admin-customer-confirm-delete")
     */
    public function confirmDelete(int $id){

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(User::class);
        $customer = $repository->find($id);
        $entityManager->remove($customer);
        $entityManager->flush();

        return $this->redirectToRoute('admin-customer-index');
    }

    /**
     * @Route("/admin/customers/changePassword/{id}", name="admin-customer-change-password")
     */
    public function changePassword(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);
        $customer = $repository->find($id);

        //$form = $this->createForm(CustomerType::class, $customer);

        $form = $this->createFormBuilder()
            ->add('password', TextType::class, [
                'constraints' => new NotBlank(),
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $customer->setPassword($this->passwordEncoder->encodePassword(
                $customer,
                $form->getData()["password"]
            ));

            $entityManager->flush();

            return $this->redirectToRoute('admin-customer-detail', ['id' => $customer->getId()] );
        }

        return $this->render('admin/customer/changePassword.html.twig', [
            'controller_name' => 'CustomerController',
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/customers/changeRates/{id}", name="admin-customer-change-rates")
     */
    public function changeRates(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $entityManager = $this->getDoctrine()->getManager();
        $repository = $entityManager->getRepository(User::class);
        $customer = $repository->find($id);
        $rates = $customer->getRate();

        //dd($rates);
        $form = $this->createFormBuilder()
            ->add('hourlyrate', NumberType::class, [
                'constraints' => new NotBlank(),
                'data' => $rates->getHourlyRate()
            ])
            ->add('transportcostrate', NumberType::class, [
                'constraints' => new NotBlank(),
                'data' => $rates->getTransportCostRate()
            ])
            ->add('save', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $rates->setHourlyRate($form->getData()["hourlyrate"]);
            $rates->setTransportCostRate($form->getData()["transportcostrate"]);
            $entityManager->persist($rates);
            $entityManager->flush();
            return $this->redirectToRoute('admin-customer-detail', ['id' => $customer->getId()] );
        }

        return $this->render('admin/customer/changeRates.html.twig', [
            'controller_name' => 'CustomerController',
            'customer' => $customer,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/customers/{id}", name="admin-customer-detail")
     */
    public function detail(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(User::class);
        $customer = $repository->find($id);

        $periods = $this->getDoctrine()
            ->getRepository(Period::class)
            ->findAllPeriodsPerCustomer($customer);

        return $this->render('admin/customer/detail.html.twig', [
            'controller_name' => 'CustomerController',
            'customer' => $customer,
            'periods' => $periods
        ]);
    }



}
