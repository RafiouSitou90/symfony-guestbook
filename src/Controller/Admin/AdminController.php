<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Registry;

/**
 * Class AdminController
 * @package App\Controller\Admin
 *
 * @Route("/admin", name="app_admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;

    public function __construct (EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @Route("/comment/review/{id}", name="review_comment")
     *
     * @param Request $request
     * @param Comment $comment
     * @param Registry $registry
     * @return Response
     */
    public function reviewComment (Request $request, Comment $comment, Registry $registry)
    {
        $accepted = !$request->query->get('reject');

        $machine = $registry->get($comment);

        if ($machine->can($comment, 'publish')) {
            $transition = $accepted ? 'publish' : 'reject';

        } elseif ($machine->can($comment, 'publish_ham')) {
            $transition = $accepted ? 'publish_ham' : 'reject_ham';

        } else {

            return new Response('Comment already reviewed or not in the right state.');
        }

        $machine->apply($comment, $transition);
        $this->entityManager->flush();

        if ($accepted) {
            $this->bus->dispatch(new CommentMessage($comment->getId()));
        }
        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }

}
