<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ConferenceController
 * @package App\Controller
 *
 * @Route("", name="app_conference_")
 */
class ConferenceController extends AbstractController
{
    /**
     * @var ConferenceRepository
     */
    private ConferenceRepository $conferenceRepository;
    /**
     * @var CommentRepository
     */
    private CommentRepository $commentRepository;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    private string $photoDir;
    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $bus;
    /**
     * @var NotifierInterface
     */
    private NotifierInterface $notifier;

    public function __construct(
        string $photoDir,
        ConferenceRepository $conferenceRepository,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager,
        MessageBusInterface $bus,
        NotifierInterface $notifier
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
        $this->photoDir = $photoDir;
        $this->bus = $bus;
        $this->notifier = $notifier;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index ()
    {
        $response = new Response($this->render('conference/index.html.twig', [])->getContent());

        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @Route("/conference_header", name="header", methods={"GET"})
     *
     * @return Response
     */
    public function conferenceHeader ()
    {
        $response = new Response($this->render('conference/_header.html.twig', [
            'conferences', $this->conferenceRepository->findAll()
        ])->getContent());

        $response->setSharedMaxAge(3600);

        return $response;
    }

    /**
     * @Route("/conference/{slug}", name="show", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Conference $conference
     * @return Response
     * @throws Exception
     */
    public function show (Request $request, Conference $conference)
    {
        $comment = new Comment();

        $comment_form = $this->createForm(CommentFormType::class, $comment);
        $comment_form->handleRequest($request);

        if ($comment_form->isSubmitted() && $comment_form->isValid()) {
            $comment->setConference($conference);

            /** @var File $photo */
            if ($photo = $comment_form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)). '.' . $photo->guessExtension();

                try {
                    $photo->move($this->photoDir, $filename);
                } catch (FileException $e) {
                }

                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user_agent'),
                'referer' => $request->headers->get('referer'),
                'permalink' => $request->getUri()
            ];

            $reviewUrl = $this->generateUrl(
                'app_admin_review_comment', ['id' => $comment->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $this->bus->dispatch(new CommentMessage($comment->getId(), $reviewUrl, $context));

            $this->notifier->send(new Notification(
                'Thank you for the feedback; your comment will be posted after moderation.',
                ['browser'])
            );

            return $this->redirectToRoute('app_conference_show', ['slug' => $conference->getSlug()]);
        }

        if ($comment_form->isSubmitted()) {
            $this->notifier->send(new Notification(
                    'Can you check your submission? There are some problems with it.',
                    ['browser'])
            );
        }

        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $this->commentRepository->getCommentPaginator($conference, $offset);

        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $comment_form->createView()
        ]);
    }
}
