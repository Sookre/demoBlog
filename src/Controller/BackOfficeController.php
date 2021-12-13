<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Form\ArticleType;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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

#[Route('/admin/article/add', name: 'app_admin_articles_add')]
 #[Route('/admin/article/{id}/update', name: 'app_admin_article_update')]
 public function ajoutArticleForm(Article $article = null, Request $request, EntityManagerInterface $manager): Response
 {

    if($article)
    {
        $photoActuelle = $article->getPhoto();
    }
    
    if(!$article)
    {
     $article = new Article;
    }
     

    $formArticle = $this->createform(ArticleType::class, $article);
    $formArticle->handleRequest($request);

    if($formArticle->isSubmitted() && $formArticle->isValid())
    {

        if($article->getId())
            $txt = 'modifié';
        else
            $txt = 'enregistré';

        if(!$article->getId())
    $article->setDate(new \Datetime());

        $photo = $formArticle->get('photo')->getData();

        if($photo)
        {
            $nomOriginePhoto = pathinfo($photo->getClientOriginallaName(), PATHINFO_FILENAME);

            $nouveauNomFichier = $nomOriginePhoto . '-' . uniqid() . '.' . $photo->guessExtension();

            try
            {
                $photo->move(
                    $this->getParameter('photo_directory'),
                    $nouveauNomFichier
                );
            }
            catch(FileException $e)
            {

            }
            $article->setPhoto($nouveauNomFichier);
        }
        else
        {
            if(isset($photoActuelle))
            $article->setPhoto($photoActuelle);
            else$article->setPhoto(null);

        }


    $manager->persist($article);
    $manager->Flush();

    $this->addFlash('success', "L'article a été enregistré avec succés.");
    return $this->redirectToRoute('app_admin_articles');
    }

    return $this->render('back_office/ajout_article_form.html.twig',[
        'formArticle'=>$formArticle->createView(),
        'photoActuelle'=>$article->getPhoto()
    ]);

}

#[Route('/admin/categories', name: 'app_admin_categories')]
public function adminCategories(EntityManagerInterface $manager, CategoryRepository $categoryRepository) : Response
 {

    $category = new Category;

    $titreCat = $manager->getClassMetadata(Category::class)->getFieldNames();
    // dd($titreCat);

    $categoriy=$categoryRepository->findAll();
 

    return $this->render('back_office/admin_categories.html.twig',[
        'category' => $category,
        'titreCat' => $titreCat,
     
        

    ]); 





 }

}

/*
        Exo : affichage et suppression catégorie 
        1. Création d'une nouvelle route '/admin/categories' (name: app_admin_categories)
        2. Création d'une nouvelle méthode adminCategories()
        3. Création d'un nouveau template 'admin_categories.html.twig'
        4. Selectionner les noms des champs/colonnes de la table Category, les transmettre au template et les afficher 
        5. Selectionner dans le controller l'ensemble de la table 'category' (findAll) et transmettre au template (render) et les afficher sur le template (Twig), afficher également le nombre d'article liés à chaque catégorie
        6. Prévoir un lien 'modifier' et 'supprimer' pour chaque categorie
        7. Réaliser le traitement permettant de supprimer une catégorie de la BDD
    */