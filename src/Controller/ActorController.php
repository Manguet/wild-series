<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actor", name="actor_")
 */
class ActorController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index()
    {
        $actors = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findAll();

        return $this->render('actor/index.html.twig', [
            'actors' => $actors,
        ]);
    }

    /**
     * @param string $actorName
     * @Route("/{actorName<^[ a-zA-Z0-9-é]+$>}", defaults={"actorName" = null}, name="show")
     * @return Response
     */
    public function showActor(string $actorName):Response
    {
        if (!$actorName) {
            throw $this
                ->createNotFoundException('No acotr has been sent to find a program in actor\'s table.');
        }
        $actorName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($actorName)), "-")
        );

        $actor = $this->getDoctrine()
            ->getRepository(Actor::class)
            ->findOneBy([
                'name' => mb_strtolower($actorName)
            ]);

        $programs = $actor->getPrograms();


        return $this->render('actor/show.html.twig', [
            'actor'    => $actor,
            'programs' => $programs,
        ]);
    }
}
