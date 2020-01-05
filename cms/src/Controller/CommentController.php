<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Period;
use App\Entity\Comment;
use App\Form\ActivityType;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment", name="comment")
     */
    public function index()
    {
        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    /**
     * @Route("/admin/comments/new/{periodId}", name="admin-comment-new")
     */
    public function registerComment(int $periodId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //get period
        $repository = $this->getDoctrine()->getRepository(Period::class);
        $period = $repository->find($periodId);

        $comment = new Comment();
        $comment->setPeriod($period);
        $comment->setCreatedAt(new \DateTime('Now'));

        $form = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('admin-period-detail', ['id' => $period->getId()]);
        }


        return $this->render('admin/comment/register.html.twig', [
            'controller_name' => 'CommentController',
            'form' => $form->createView(),
        ]);
    }
}

