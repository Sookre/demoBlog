<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\Persistence\ManagerRegistry;
use SebastianBergmann\CodeCoverage\Report\Xml\Report;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BlockController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response 
    {
        // méthode rendu, en fonction de la route dans l'URL, la méthode render() envoi un template, un rendu sur le navigateur
        return $this->render('blog/home.html.twig', [
            'title' => 'Bienvenue sur le blog Symfony',
            'age' => 25
        ]);
    }

    #[Route('/blog', name: 'blog')]
    public function blog(ArticleRepository $RepoArticle): Response
    {
        /*
            Symfony est une application qui est capable de répondre à un navigateur lorsque celui-ci appel une addresse (ex: localhost:8000/blog), le controller doit être capable d'envoyer un rendu, un template sur le navigateur

            Ici, lorsque l'on tranmset la route '/blog' dans l'URL, cela execute la méthode index() dans le controller qui renvoie le template '/blog/inddex.html.twig' sur la navigateur
        */

        // $repoArticle est un objet issu de la classe ArticleRepository
        // $repoArticle = $doctrine()->getRepository(Article::class);

        $articles = $RepoArticle->findAll(); // SELECT * FROM article + FETCH_ALL
        dump($articles); 

        return $this->render('blog/blog.html.twig', [
        'formArticle' => $articles
        ]);

        'editMode' => $article->getId()
    }


    #[Route('/blog/12', name: 'blog_show')]
    public function blogShow(): Response
    {
        return $this->render('blog/blog_show.html.twig');
    }

}