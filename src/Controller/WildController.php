<?php
//src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @return Response A response instance
     */
    public function index() :Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table.'
            );
        }

        return $this->render(
            'wild/index.html.twig',
            ['programs' => $programs]
        );
    }


    /**
     * Getting a program with a formatted slug for title
     *
     * @param string $slug The slugger
     * @Route("/show/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show")
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

        $seasons = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findBy([
                'program' => $program,
            ]);

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug'    => $slug,
            'seasons' => $seasons,
        ]);
    }

    /**
     * 3 last programs in category
     *
     * @param string|null $categoryName
     * @return Response
     * @Route("/category/{categoryName<^[a-z0-9-]+$>}", defaults={"categoryName" = null}, name="show_category")
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
        $season = $episode->getSeason();
        $program = $season->getProgram();
        return $this->render('wild/episode.html.twig', [
            'episode' => $episode,
            'season' => $season,
            'program' => $program,
        ]);
    }
}