<?php

namespace TicketsBundle\Controller;

use TicketsBundle\Entity\Ticket;
use TicketsBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;use Symfony\Component\HttpFoundation\Request;

/**
 * Comment controller.
 *
 * @Route("comment")
 */
class CommentController extends Controller
{
    /**
     * Creates a new comment entity.
     *
     * @Route("/{id}/new", name="comment_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, Ticket $ticket)
    {
            $comment = new Comment();
            $form = $this->createForm('TicketsBundle\Form\CommentType', $comment);
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

                $comment->setTicket($ticket);
                $comment->setAuthor($user);
                $comment->setCreated($time);

                $em->persist($comment);
                $em->flush($comment);

                return $this->redirectToRoute('ticket_show', array('id' => $ticket->getId()));
            }

            return $this->render('TicketsBundle:comment:new.html.twig', array(
                'comment' => $comment,
                'form' => $form->createView(),
            ));
        }

    /**
     * Finds and displays a comment entity.
     *
     * @Route("/{id}", name="comment_show")
     * @Method("GET")
     */
    public function showAction(Comment $comment)
    {
        $deleteForm = $this->createDeleteForm($comment);

        return $this->render('TicketsBundle:comment:show.html.twig', array(
            'comment' => $comment,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing comment entity.
     *
     * @Route("/{id}/edit", name="comment_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Comment $comment)
    {
        $deleteForm = $this->createDeleteForm($comment);
        $editForm = $this->createForm('TicketsBundle\Form\CommentType', $comment);
        $editForm->handleRequest($request);

        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'ROLE_ADMIN' ) )
        {
            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $time = new \Datetime('Europe/Paris');
                $comment->setUpdated($time);
                $this->getDoctrine()->getManager()->flush();

                return $this->redirectToRoute('ticket_show', array('id' => $comment->getTicket()->getId()));
            }
        }else{
            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('TicketsBundle:comment:edit.html.twig', array(
            'comment' => $comment,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a comment entity.
     *
     * @Route("/{id}", name="comment_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Comment $comment)
    {
        $form = $this->createDeleteForm($comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush($comment);
        }

        return $this->redirectToRoute('ticket_index');
    }

    /**
     * Creates a form to delete a comment entity.
     *
     * @param Comment $comment The comment entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Comment $comment)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('comment_delete', array('id' => $comment->getId())))
            ->setMethod('DELETE')
            ->getForm()
            ;
    }
}
