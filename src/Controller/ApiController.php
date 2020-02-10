<?php

namespace App\Controller;

use App\Document\Match;
use App\Document\Missile;
use App\Document\Player;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentNotFoundException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    /**
     * @Route("/start", name="start")
     * @param Request $request
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function start(Request $request): JsonResponse
    {
        $login = $request->cookies->get('login');
        $matchRepo = $this->dm->getRepository(Match::class);
        if (!$match = $matchRepo->findOneBy(['player1.login' => $login])) {
            if (!$match = $matchRepo->findOneBy(['player2.login' => $login])) {
                $player1 = (new Player())
                    ->setNr(1)
                    ->setLogin($login)
                    ->setX(100)
                    ->setY(100)
                    ->setHealth(100);
                $player2 = (new Player())
                    ->setNr(2)
                    ->setLogin('test2')
                    ->setX(1000)
                    ->setY(100)
                    ->setHealth(100);
                $match = (new Match())
                    ->setPlayer1($player1)
                    ->setPlayer2($player2);
                $this->dm->persist($match);
                $this->dm->flush();
            }
        }

        $missiles = $this
            ->dm
            ->getRepository(Missile::class)
            ->findBy(['matchId' => $match->getId()]);

        return new JsonResponse([
            'match_id' => $match->getId(),
            'player1' => [
                'login'  => $match->getPlayer1()->getLogin(),
                'x'      => $match->getPlayer1()->getX(),
                'y'      => $match->getPlayer1()->getY(),
                'health' => $match->getPlayer1()->getHealth(),
            ],
            'player2' => [
                'login'  => $match->getPlayer2()->getLogin(),
                'x'      => $match->getPlayer2()->getX(),
                'y'      => $match->getPlayer2()->getY(),
                'health' => $match->getPlayer2()->getHealth(),
            ],
            'missiles' => array_map(
                fn (Missile $missile) => [
                    'id'         => $missile->getId(),
                    'shooter_nr' => $missile->getShooterNr(),
                    'time'       => $missile->getTime(),
                    'sx'         => $missile->getSx(),
                    'sy'         => $missile->getSy(),
                ],
                $missiles
            ),
        ]);
    }

    /**
     * @Route("/shot", name="shot")
     * @param Request $request
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function shot(Request $request): JsonResponse
    {
        $missile = (new Missile())
            ->setMatchId($request->get('match_id'))
            ->setShooterNr($request->get('shooter_nr'))
            ->setTime($request->get('time'))
            ->setSx($request->get('sx'))
            ->setSy($request->get('sy'))
        ;
        $this->dm->persist($missile);
        $this->dm->flush();

        return new JsonResponse(['missile_id' => $missile->getId()]);
    }

    /**
     * @Route("/remove", name="remove")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function remove(Request $request): Response
    {
        $this->dm->remove($this->dm->find(Missile::class, $request->get('missile_id')));
        $this->dm->flush();

        return new Response();
    }

    /**
     * @Route("/hit", name="hit")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function hit(Request $request): Response
    {
        $matchId = $request->get('match_id');
        $match = $this->dm->find(Match::class, $matchId);
        if (!$match) {
            throw new DocumentNotFoundException("Match {$matchId} not found");
        }
        /** @var Player $player */
        $player = $request->get('player') === '1' ? $match->getPlayer1() : $match->getPlayer2();
        $player->setHealth($player->getHealth() - 10);

        $this->dm->remove($match);
        $this->dm->flush();
        $this->dm->persist($match);
        $this->dm->flush();

        return new Response();
    }

    /**
     * @Route("/stop", name="stop")
     * @param Request $request
     * @return Response
     * @throws MongoDBException
     */
    public function stop(Request $request): Response
    {
        $matchId = $request->get('match_id');
        $match = $this->dm->find(Match::class, $matchId);
        if (!$match) {
            throw new DocumentNotFoundException("Match {$matchId} not found");
        }
        $winner = $request->get('killed') === '1' ? $match->getPlayer2() : $match->getPlayer1();
        $winner = $this->dm->getRepository(User::class)->findOneBy(['login' => $winner->getLogin()]);
        $winner->setScore($winner->getScore() + 1);

        $this->dm->persist($winner);
        $this->dm->remove($match);
        $this->dm->flush();

        return new Response();
    }
}
