<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\Service\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use http\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

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
     * @var SpamChecker
     */
    private SpamChecker $spamChecker;

    public function __construct(
        string $photoDir,
        ConferenceRepository $conferenceRepository,
        CommentRepository $commentRepository,
        EntityManagerInterface $entityManager,
        SpamChecker $spamChecker
    )
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
        $this->photoDir = $photoDir;
        $this->spamChecker = $spamChecker;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index ()
    {
        return $this->render('conference/index.html.twig', []);
    }

    /**
     * @Route("/conference/{slug}", name="show", methods={"GET", "POST"})
     *
     * @param Request $request
     * @param Conference $conference
     * @return Response
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
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

            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user_agent'),
                'referer' => $request->headers->get('referer'),
                'permalink' => $request->getUri()
            ];

            if (2 === $this->spamChecker->getSpamScore($comment, $context)) {
                throw new RuntimeException('Blattant spam, go away!');
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_conference_show', ['slug' => $conference->getSlug()]);
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
