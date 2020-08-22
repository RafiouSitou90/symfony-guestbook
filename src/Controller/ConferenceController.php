<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ConferenceController
 * @package App\Controller
 *
 * @Route("/", name="app_")
 */
class ConferenceController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     */
    public function index ()
    {
        return $this->render('homepage/index.html.twig', []);
    }
}
