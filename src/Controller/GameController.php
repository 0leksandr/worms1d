<?php

namespace App\Controller;

use App\Document\Test;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @Route("/game", name="game")
     */
    public function index(): Response
    {
        return $this->render('game.html.twig');
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(): Response
    {
        $test = (new Test())->setField('this is a test');
        $this->documentManager->persist($test);
        $this->documentManager->flush();
        $tests = $this->documentManager->getRepository(Test::class)->findAll();
        dump($tests);
        return new Response(print_r($tests, true));
    }
}
