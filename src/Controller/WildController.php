<?php
//src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ProgramSearchType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wild", name="wild_")
 */
class WildController extends AbstractController
{
    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @param Request $request
     * @return Response A response instance
     */
    public function index(Request $request) :Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }

        $form = $this->createForm(
            ProgramSearchType::class,
            null,
            ['method' => Request::METHOD_GET]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            $program = $this->getDoctrine()
                ->getRepository(Program::class)
                ->findOneBy([
                    'title' => $data,
                ]);
            $seasons = $this->getDoctrine()
                ->getRepository(Season::class)
                ->findBy([
                    'program' => $program,
                ]);

            $actors = $program->getActors();

            return $this->render(
                'wild/show.html.twig', [
                    'program' => $program,
                    'seasons' => $seasons,
                    'actors'  => $actors,
                ]
            );
        }

        return $this->render(
            'wild/index.html.twig', [
                'programs' => $programs,
                'form'     => $form->createView(),
                ]
        );
    }


    /**
     * Getting a program with a formatted slug for title
     *
     * @param string $slug The slugger
     * @Route("/show/{slug<^[ a-zA-Z0-9-é]+$>}", defaults={"slug" = null}, name="show")
     * @return Response
     */
    public function showByProgram(string $slug):Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );

        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy([
                'title' => mb_strtolower($slug)
            ]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with '.$slug.' title, found in program\'s table.'
            );
        }

        $actors = $program->getActors();

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy([
                'program' => $program,
            ]);

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'    => $slug,
            'seasons' => $seasons,
            'actors'  => $actors,
        ]);
    }

    /**
     * 3 last programs in category
     *
     * @param string|null $categoryName
     * @return Response
     * @Route("/category/{categoryName<^[ a-z0-9-é]+$>}", defaults={"categoryName" = null}, name="show_category")
     */
    public function showByCategory(?string $categoryName):Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No category has been find in categorie\'s table.');
        }
        $categoryName = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($categoryName)), "-")
        );

        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy([
                'name' => mb_strtolower($categoryName),
            ]);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category],
                ['id'       => 'DESC'],
                3);

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program with '.$categoryName.' category, found in Program\'s table.'
            );
        }
        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
        ]);
    }

    /**
     * seasons from a program
     *
     * @param int $id
     * @return Response
     * @Route("/season/{id<^[0-9-]+$>}", defaults={"id" = null}, name="season")
     */
    public function showBySeason(int $id) : Response
    {
        if (!$id) {
            throw $this
                ->createNotFoundException('No season has been find in season\'s table.');
        }

        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->find($id);

        $program = $season->getProgram();
        $episodes = $season->getEpisodes();

        if (!$season) {
            throw $this->createNotFoundException(
                'No season with '.$id.' season, found in Season\'s table.'
            );
        }

        return $this->render('wild/season.html.twig', [
            'season'   => $season,
            'program'  => $program,
            'episodes' => $episodes,
        ]);
    }

    /**
     * @param Episode $episode
     * @Route("/episode/{id}", name="episode")
     * @return Response
     */
    public function showEpisode(Episode $episode): Response
    {
        $comments = $episode->getComments();
        $season   = $episode->getSeason();
        $program  = $season->getProgram();

        return $this->render('wild/episode.html.twig', [
            'episode'  => $episode,
            'season'   => $season,
            'program'  => $program,
            'comments' => $comments,
        ]);
    }

    /**
     * @Route("/comment/{id}", name="comment", methods={"GET","POST"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function newComment(Request $request, int $id): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->getUser();
            $comment->setUser($author);
            $episode = $this->getDoctrine()->getRepository(Episode::class)->find($id);
            $comment->setEpisode($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('wild_episode', ['id' => $id]);
        }

        return $this->render('wild/Form/CommentForm.html.twig', [
            'form'    => $form->createView(),
        ]);
    }

    /**
     * @Route("/deleteComment/{id}", name="delete", methods={"DELETE", "GET"})
     * @param EntityManagerInterface $entityManager
     * @param int $id
     * @return Response
     */
    public function delete(EntityManagerInterface $entityManager, int $id): Response
    {
        $comment = $this->getDoctrine()->getRepository(Comment::class)->find($id);
        $episode= $comment->getEpisode();
        $episodeId = $episode->getId();
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('wild_episode', ['id' => $episodeId]);
    }

    /**
     * @Route("/my-profil", name="my_profil")
     * @return Response
     */
    public function profilUser(): Response
    {
        return $this->render('security/profil.html.twig');
    }
}