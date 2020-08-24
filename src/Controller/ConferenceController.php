<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConferenceController
 * @package App\Controller
 *
 * @Route("/", name="app_conference_")
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

    public function __construct(ConferenceRepository $conferenceRepository, CommentRepository $commentRepository)
    {
        $this->conferenceRepository = $conferenceRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("", name="index", methods={"GET"})
     */
    public function index ()
    {
        return $this->render('conference/index.html.twig', [
            'conferences' => $this->conferenceRepository->findAll()
        ]);
    }

    /**
     * @Route("/conference/{id}", name="show", methods={"GET"})
     *
     * @param Conference $conference
     * @return Response
     */
    public function show (Conference $conference)
    {
        return $this->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $this->commentRepository->findBy(['conference' => $conference], ['createdAt' => 'DESC'])
        ]);
    }
}
