<?php

namespace App\Controller;

use App\Document\Match;
use App\Document\Missile;
use App\Document\Player;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    private const COOKIE_LOGIN = 'login';

    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @Route("/login_form", name="login_form")
     * @return Response
     */
    public function loginForm(): Response
    {
        return $this->render('login_form.html.twig');
    }

    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function login(Request $request): Response
    {
        $login = $request->get('login');
        if (!$this->dm->getRepository(User::class)->findOneBy(['login' => $login])) {
            $this->dm->persist(
                (new User())
                    ->setLogin($login)
                    ->setScore(0)
                    ->setAchievements([])
            );
            $this->dm->flush();
        }

        $response = new RedirectResponse($this->generateUrl('game'));
        $response->headers->setCookie(new Cookie(self::COOKIE_LOGIN, $login));
        return $response;
    }

    /**
     * @Route("/logout", name="logout")
     * @return Response
     */
    public function logout(): Response
    {
        $response = new RedirectResponse($this->generateUrl('login_form'));
        $response->headers->removeCookie(self::COOKIE_LOGIN);
        return $response;
    }

    /**
     * @Route("/", name="main")
     * @param Request $request
     * @return Response
     */
    public function main(Request $request): Response
    {
        if (!$request->cookies->has(self::COOKIE_LOGIN)) {
            return new RedirectResponse($this->generateUrl('login_form'));
        }
        return $this->render('main.html.twig');
    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function leaderboard(Request $request): Response
    {
        return $this->render(
            'leaderboard.html.twig',
            [
                'leaders' => $this
                    ->dm
                    ->createQueryBuilder(User::class)
                    ->sort('score', 'desc')
                    ->limit(10)
                    ->getQuery()
                    ->execute()
                ,
            ]
        );
    }

    /**
     * @Route("/game", name="game")
     */
    public function game(): Response
    {
        return $this->render('game.html.twig');
    }

    /**
     * @Route("/test")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function test(Request $request): Response
    {
        if ($request->get('remove')) {
            foreach ([
                ...$this->dm->getRepository(Match::class)->findAll(),
                ...$this->dm->getRepository(Missile::class)->findAll(),
                ...$this->dm->getRepository(Player::class)->findAll(),
                ...$this->dm->getRepository(User::class)->findAll(),
            ] as $document) {
                $this->dm->remove($document);
            }
            $this->dm->flush();
        }
        return new Response(print_r([
//            $this->dm->getRepository(Player::class)->findAll(),
            $this->dm->getRepository(Match::class)->findAll(),
            $this->dm->getRepository(Missile::class)->findAll(),
            $this->dm->getRepository(User::class)->findAll(),
        ], true));
    }
}
