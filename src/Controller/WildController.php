<?php
//src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Category;
use App\Entity\Program;
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
     * @Route("/show/{slug}",
     *      requirements={"slug"="^[a-z0-9]+(?:-[a-z0-9]+)*$"},
     *      name="show")
     * @param string $slug
     * @return Response
     */
    public function show(string $slug = ''): Response
    {
        if ($slug === '') {
            $title = 'Aucune série sélectionnée, veuillez choisir une série';
        } else {
            $title = ucwords(str_replace('-', ' ', $slug));
        }
        return $this->render('wild/show.html.twig', ['title' => $title]);
    }

    /**
     * Show 3 programs by id
     *
     * @param string $categoryName
     * @Route("/category/{category}", name="show_category")
     * @return Response
     */
    public function showByCategory(string $categoryName):Response
    {
        if (!$categoryName) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }

        $programs = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findBy([
                'name' => 'Horror',
                'id'   => 'DESC',
                3
            ]);

        if (!$programs ) {
            throw $this->createNotFoundException(
                'No programs with ' . $categoryName . 'category in program\'s table.'
            );
        }

        return $this->render('wild/category.html.twig', [
            'programs' => $programs,
        ]);
    }

}