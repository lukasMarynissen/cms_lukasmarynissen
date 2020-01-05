<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends AbstractController
{
    /**
     * @Route("/admin/messages", name="admin-messages")
     */
    public function index()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $messages = $this->getDoctrine()
            ->getRepository(Message::class)
            ->findMessagesBySenderType("ROLE_CUSTOMER");

        return $this->render('admin/message/index.html.twig', [
            'controller_name' => 'MessageController',
            'messages' => $messages
        ]);
    }



    /**
     * @Route("/admin/messages/reply/{id}", name="admin-message-reply")
     */
    public function reply(int $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        //get original message
        $repository = $this->getDoctrine()->getRepository(Message::class);
        $originalMessage = $repository->find($id);


        $message = new Message();
        $message->setSender($originalMessage->getRecipient());
        $message->setRecipient($originalMessage->getSender());
        $message->setRelatedPeriod($originalMessage->getRelatedPeriod());
        $message->setSenderType("ROLE_ADMIN");
        $message->setCreatedAt(new \DateTime('Now'));
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($message);
            $entityManager->flush();

            return $this->redirectToRoute('admin-message-detail', ['id' => $message->getId()]);
        }

        return $this->render('admin/message/register.html.twig', [
            'controller_name' => 'MessageController',
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/admin/messages/{id}", name="admin-message-detail")
     */
    public function detail(int $id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repository = $this->getDoctrine()->getRepository(Message::class);
        $message = $repository->find($id);


        return $this->render('admin/message/detail.html.twig', [
            'controller_name' => 'MessageController',
            'message' => $message,
        ]);
    }
}
