<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Entity\Program;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use App\Service\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actor", name="actor_")
 */
class ActorController extends AbstractController
{

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     */
    public function new(Request $request, Slugify $slugify): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($actor->getName());
            $actor->setSlug($slug);
            $entityManager->persist($actor);
            $entityManager->flush();

            return $this->redirectToRoute('actor_all');
        }

        return $this->render('actor/new.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/all", name="all", methods={"GET"})
     * @param ActorRepository $actorRepository
     * @return Response
     */
    public function all(ActorRepository $actorRepository): Response
    {
        return $this->render('actor/all.html.twig', [
            'actors' => $actorRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     * @param Request $request
     * @param Slugify $slugify
     * @return Response
     */
    public function edit(Request $request, Actor $actor, Slugify $slugify): Response
    {
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($actor->getName());
            $actor->setSlug($slug);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('actor_all');
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="delete", methods={"DELETE"})
     * @param Request $request
     * @param Actor $actor
     * @return Response
     */
    public function delete(Request $request, Actor $actor): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($actor);
            $entityManager->flush();
        }

        return $this->redirectToRoute('actor_all');
    }

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
     * @Route("/{actorName}", defaults={"actorName" = null}, name="show")
     * @return Response
     */
    public function showActor(string $actorName):Response
    {
        if (!$actorName) {
            throw $this
                ->createNotFoundException('No actor has been sent to find a program in actor\'s table.');
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
