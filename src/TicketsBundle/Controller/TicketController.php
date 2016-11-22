<?php

namespace TicketsBundle\Controller;

use TicketsBundle\Entity\Ticket;
use TicketsBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class TicketController extends Controller
{
    /**
     * Lists all ticket entities.
     *
     * @Route("/", name="ticket_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'ROLE_ADMIN' ) )
        {
            $tickets = $em->getRepository('TicketsBundle:Ticket')->findAll();


        }elseif ($this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' )){
            $user = $this->container->get('security.token_storage')->getToken()->getUser();

            $tickets = $em->getRepository('TicketsBundle:Ticket')->findByAuthor($user);
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }


        return $this->render('TicketsBundle:ticket:index.html.twig', array(
            'tickets' => $tickets,
        ));
    }

    /**
     * Creates a new ticket entity.
     *
     * @Route("/new", name="ticket_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $ticket = new Ticket();
        $comment = new Comment();

        $form = $this->createForm('TicketsBundle\Form\TicketType', $ticket);
        $form->handleRequest($request);

        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
        {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }else{
            return $this->redirectToRoute('fos_user_security_login');
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $time = new \Datetime('Europe/Paris');

            $em = $this->getDoctrine()->getManager();

            $ticket->setCreated($time);
            $ticket->setAuthor($user);

            $comment->setContent($request->request->get('content'));
            $comment->setTicket($ticket);
            $comment->setCreated($time);
            $comment->setAuthor($user);

            $em->persist($ticket);
            $em->flush($ticket);

            $em->persist($comment);
            $em->flush($comment);

            return $this->redirectToRoute('ticket_show', array('id' => $ticket->getId()));
        }

        return $this->render('TicketsBundle:ticket:new.html.twig', array(
            'ticket' => $ticket,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ticket entity.
     *
     * @Route("/ticket/{id}", name="ticket_show")
     * @Method("GET")
     */
    public function showAction(Ticket $ticket)
    {

        if( $ticket->getAuthor() ==  $this->container->get('security.token_storage')->getToken()->getUser() || $this->container->get( 'security.authorization_checker' )->isGranted( 'ROLE_ADMIN' ))
        {
            $deleteForm = $this->createDeleteForm($ticket);

            $comments = $this->getDoctrine()
                ->getRepository('TicketsBundle:Comment')
                ->findBy(['ticket' => $ticket], array('id' => 'ASC'));

        }else{
            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('TicketsBundle:ticket:show.html.twig', array(
            'ticket' => $ticket,
            'delete_form' => $deleteForm->createView(),
            'comments' => $comments,
        ));
    }

    /**
     * Displays a form to edit an existing ticket entity.
     *
     * @Route("/{id}/edit", name="ticket_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Ticket $ticket)
    {
        $deleteForm = $this->createDeleteForm($ticket);
        $editForm = $this->createForm('TicketsBundle\Form\TicketType', $ticket);
        $editForm->handleRequest($request);

        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'ROLE_ADMIN' ) )
        {
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $time = new \Datetime('Europe/Paris');
                $ticket->setUpdated($time);
                $ticket->setAuthor($this->getUser()->getId());
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('ticket_show', array('id' => $ticket->getId()));
            }
        }else{
            return $this->redirectToRoute('ticket_index');
        }



        return $this->render('TicketsBundle:ticket:edit.html.twig', array(
            'ticket' => $ticket,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ticket entity.
     *
     * @Route("/{id}", name="ticket_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Ticket $ticket)
    {

        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'ROLE_ADMIN' ) )
        {
            $form = $this->createDeleteForm($ticket);
            $form->handleRequest($request);
            $em = $this->getDoctrine()->getManager();
            $comments = $em->getRepository('TicketsBundle:Comment')->findByTicket($ticket->getId());
            if ($form->isSubmitted() && $form->isValid()) {
                foreach ($comments as $comment) {
                    $em->remove($comment);
                }
                $em->remove($ticket);
                $em->flush($ticket);
            }
        }else{
            return $this->redirectToRoute('ticket_index');
        }

        return $this->redirectToRoute('ticket_index');
    }

    /**
     * Creates a form to delete a ticket entity.
     *
     * @param Ticket $ticket The ticket entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Ticket $ticket)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('ticket_delete', array('id' => $ticket->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
