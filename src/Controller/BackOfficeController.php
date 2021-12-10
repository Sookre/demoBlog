<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('back_office/index.html.twig', [
            'controller_name' => 'BackOfficeController',
        ]);
    }

    #[Route('/admin/articles', name: 'app_admin_articles')]
public function adminArticles(EntityManagerInterface $manager, ArticleRepository $articleRepository): Response
{
    $colonnes = $manager->getClassMetadata(Article::class)->getFieldNames();

    //dd($colonnes);
    $article=$articleRepository->findAll();
    //dd($article);



    return $this->render('back_office/admin_articles.html.twig',[
        'colonnes' => $colonnes,
        'data'=>$article,
    ]);
}
}


// Exo : afficher sous la forme de tableau HTML l'ensemble des articles stockés en BDD 

//  1. selectionner sous la forme de tableau  de la table 'article' et transmettre le resultat à la méthode render()

// 2. Dans le template 'admin_articles.html.twig', mettre en forme l'affichage des articles dans un tableau html

// 3. afficher le nom de la catégorie de chaque article
// 4. afficher le nombre de commentaire de chaque article
// 5. prévoir un lien modification/suppression pour chaque article