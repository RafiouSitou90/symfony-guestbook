<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Message\CommentMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    private string $noReplyEmail;

    public function __construct (
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        MailerInterface $mailer,
        string $noReplyEmail
    )
    {
        $this->entityManager = $entityManager;
        $this->bus = $bus;
        $this->mailer = $mailer;
        $this->noReplyEmail = $noReplyEmail;
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
            $reviewUrl = $this->generateUrl(
                'app_admin_review_comment', ['id' => $comment->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl));

            $this->mailer->send((new TemplatedEmail())
                ->subject('Comment posted')
                ->from(new Address($this->noReplyEmail, 'Symfony Guestbook'))
                ->to($comment->getEmail())
                ->htmlTemplate('emails/user_comment_notification.html.twig')
                ->context(['comment' => $comment])
            );
        }
        return $this->render('admin/review.html.twig', [
            'transition' => $transition,
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/http-cache/{uri<.*>}", methods={"PURGE"})
     *
     * @param KernelInterface $kernel
     * @param Request $request
     * @param string $uri
     * @return Response
     */
    public function purgeHttpCache (KernelInterface $kernel, Request $request, string $uri)
    {
        if ('prod' === $kernel->getEnvironment()) {
            return new Response('KO', Response::HTTP_BAD_REQUEST);
        }

        $store = (new class($kernel) extends HttpCache{})->getStore();
        $store->purge($request->getSchemeAndHttpHost(). '/' . $uri);

        return new Response('Done');
    }

}
