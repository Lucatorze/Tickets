<?php

namespace TicketsBundle\Controller;

use TicketsBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

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

        $tickets = $em->getRepository('TicketsBundle:Ticket')->findAll();

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
        $form = $this->createForm('TicketsBundle\Form\TicketType', $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $time = new \Datetime('Europe/Paris');
            $em = $this->getDoctrine()->getManager();
            $ticket->setCreated($time);
            $em->persist($ticket);
            $em->flush($ticket);

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
     * @Route("/{id}", name="ticket_show")
     * @Method("GET")
     */
    public function showAction(Ticket $ticket)
    {
        $deleteForm = $this->createDeleteForm($ticket);

        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('TicketsBundle:Comment')->findBy(array('idTicket' => $ticket->getId()));

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

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $time = new \Datetime('Europe/Paris');
            $ticket->setUpdated($time);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_show', array('id' => $ticket->getId()));
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
        $form = $this->createDeleteForm($ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($ticket);
            $em->flush($ticket);
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
