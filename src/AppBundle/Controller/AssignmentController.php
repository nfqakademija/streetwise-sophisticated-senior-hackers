<?php

namespace AppBundle\Controller;

use AppBundle\Controller\Admin\CommentTrait;
use AppBundle\Entity\Assignment;
use AppBundle\Entity\Comment;
use AppBundle\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Assignment controller.
 *
 * @Route("assignment")
 */
class AssignmentController extends Controller
{
    /**
     * Trait with comment methods
     */
    use CommentTrait;

    /**
     * Finds and displays a assignment entity.
     *
     * @Route("/{id}", name="assignment_show")
     * @Method({"GET", "POST"})
     *
     * @param Assignment $assignment
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Assignment $assignment, Request $request)
    {
        $this->denyAccessUnlessGranted('show', $assignment);

        $comments = $this->getEntityComments($assignment);

        $comment = new Comment();
        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->denyAccessUnlessGranted('new', $comment);

            $comment->setAuthor($this->getUser());
            $comment = $commentForm->getData();
            $thread = $this->getThreadPromise($assignment);
            $comment->setThread($thread);

            $em = $this->get('doctrine.orm.default_entity_manager');
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute(
                'assignment_show',
                [
                    'id' => $assignment->getId(),
                ]
            );
        }

        return $this->render(
            'assignment/show.html.twig',
            [
                'assignment' => $assignment,
                'comment_form' => $commentForm->createView(),
                'comments' => $comments,
            ]
        );
    }
}
