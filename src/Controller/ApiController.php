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
                if ($user1 = $this->dm->getRepository(User::class)->findOneBy(['await' => true])) {
                    if ($user1->getLogin() !== $login) {
                        $user1->setAwait(false);
                        $this->dm->persist($user1);
                        $this->dm->flush();
                        $match = $this->startMatch($user1->getLogin(), $login);
                    }
                }
            }
        }

        if (!$match) {
            $user = $this->dm->getRepository(User::class)->findOneBy(['login' => $login]);
            if (!$user) {
                throw new DocumentNotFoundException("User $login not found");
            }
            $user->setAwait(true);
            $this->dm->persist($user);
            $this->dm->flush();

            return new JsonResponse(['await' => true]);
        }

        return new JsonResponse(['match_id' => $match->getId()]);
    }

    /**
     * @Route("/bot", name="bot")
     * @param Request $request
     * @return JsonResponse
     * @throws MongoDBException
     */
    public function bot(Request $request): JsonResponse
    {
        $login = $request->cookies->get('login');
        $match = $this->startMatch($login, 'bot');
        return new JsonResponse(['match_id' => $match->getId()]);
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
     * @Route("/health", name="health")
     * @param Request $request
     * @return Response
     * @throws DocumentNotFoundException
     * @throws MongoDBException
     */
    public function health(Request $request): Response
    {
        $matchId = $request->get('match_id');
        $match   = $this->dm->find(Match::class, $matchId);
        if (!$match) {
            throw new DocumentNotFoundException("Match {$matchId} not found");
        }
        $health = $request->get('health');
        if ($request->get('player') === '1') {
            $match->setPlayer1Health($health);
        } else {
            $match->setPlayer2Health($health);
        }

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

    /**
     * @Route("/update", name="update")
     * @param Request $request
     * @return JsonResponse
     * @throws DocumentNotFoundException
     */
    public function update(Request $request): JsonResponse
    {
        $matchId = $request->get('match_id');
        $match = $this->dm->find(Match::class, $matchId);
        if (!$match) {
            throw new DocumentNotFoundException("Match {$matchId} not found");
        }
        $login = $request->cookies->get('login');

        return new JsonResponse([
            'match_id' => $match->getId(),
            'login'    => $login,
            'player1'  => [
                'login'  => $match->getPlayer1()->getLogin(),
                'x'      => $match->getPlayer1()->getX(),
                'y'      => $match->getPlayer1()->getY(),
                'health' => $match->getPlayer1Health(),
            ],
            'player2'  => [
                'login'  => $match->getPlayer2()->getLogin(),
                'x'      => $match->getPlayer2()->getX(),
                'y'      => $match->getPlayer2()->getY(),
                'health' => $match->getPlayer2Health(),
            ],
            'missiles' => array_map(
                fn (Missile $missile) => [
                    'id'         => $missile->getId(),
                    'shooter_nr' => $missile->getShooterNr(),
                    'time'       => $missile->getTime(),
                    'sx'         => $missile->getSx(),
                    'sy'         => $missile->getSy(),
                ],
                $this->dm->getRepository(Missile::class)->findBy(['matchId' => $matchId])
            ),
        ]);
    }

    /**
     * @param string $user1Login
     * @param string $user2Login
     * @return Match
     * @throws MongoDBException
     */
    private function startMatch(string $user1Login, string $user2Login): Match
    {
        $player1 = (new Player())
            ->setNr(1)
            ->setLogin($user1Login)
            ->setX(100)
            ->setY(100);
        $player2 = (new Player())
            ->setNr(2)
            ->setLogin($user2Login)
            ->setX(-200)
            ->setY(100);
        $match = (new Match())
            ->setPlayer1($player1)
            ->setPlayer1Health(100)
            ->setPlayer2($player2)
            ->setPlayer2Health(100);
        $this->dm->persist($match);
        $this->dm->flush();

        return $match;
    }
}
