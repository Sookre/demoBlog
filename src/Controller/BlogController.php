<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BlogController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        //méthode rendu, en fonction de la route dans l'URL, la méthode render() envoie un template, un rendu sur le navigateur.
        return $this->render('blog/home.html.twig', [
            'title'=> 'Bienvenue sur le blog Symfony',
            'age'=> 25,
        ]);
    }


    #[Route('/blog', name: 'blog')]
    public function blog(ArticleRepository $repoArticle): Response
    {
        //Injection de dépendance : C'est un des fondements de Symfony, ici notre méthod blog DEPEND de la classe ArticleRepository pour fonctionner correctement. Ici Symfony comprend que la méthode blog() attend en argument un objet issu de la classe ArticleRepository, automatiquement Symfony envoie une instance de cette classe en argument. $repoArticle est un objet issu de la class ArticleRepository, nous n'avons plus qu'à piocher dans l'objet pour atteindre des méthodes de la classe.
        /* 
            SYMFONY est une application qui est capable de répondre à un navigateur lorsque celui-ci appel une adresse (ex:localhost:8000/blog), le controller doit être capable d'envoyer un rendu, un template sur le navigateur.
            
            Ici, lorsque l'on transmet la route '/blog' dans l'URL, cela exécute la méthode index() dans le controller qui renvoie le template '/blog/index.html.twig' sur le navigateur.
        */
        

        /*$repoArticle est un objet issu de la classe ArticleRepository
        Pour sélectionner en BDD, nous devons passer par une classe Repository, ces classes permettent uniquement d'exécuter des requêtes de selection SELECT en BDD. Elles contiennent des méthodes misent à disposition par Symfony (findAll(), find(), findBy(), ...)        
        
        Ici nous devons importer au sein de notre controller la classe Article Repository pour pouvoir sélectionner dans la table Article.
        $repoArticle est un objet issu de la classe ArticleRepository
        getRepository() est une méthode issue de l'objet Doctrine permettant ici d'importer la classe ArticleRepository.
        */
        // $repoArticle=$doctrine->getRepository(Article::class);
        
        /*
        findAll() : méthode issue de la classe ArticleRepository permettant de sélectionner l'ensemble de la table SQL et de récupérer un tableau multidimensionnel contenant l'ensemble des articles stockés en BDD.
        */
        $articles=$repoArticle->findAll(); //SELECT * FROM article + Fectch_ALL
      

        //dump() & dd() sont des fonctions de debugages inclues dans Symfony
        // dd($articles);

        return $this->render('blog/blog.html.twig', [
            'articles'=> $articles //On transmet au template les articles sélectionnés en BDD afin que twig traite l'affichage.
        ]);
    }


    #[Route('/blog/new', name:'blog_create')]
    #[Route('/blog/{id}/edit', name:'blog_edit')]
    public function blogCreate(Article $article=null,Request $request, EntityManagerInterface $manager, SluggerInterface $slugger):Response
    {
        //Si la variable article est null alors on rentre dans la route /blog/new, on entre dans le if et on crée une nouvelle instance de l'entité article. 
        //Si la variable $article n'est pas null, on nous sommes sur la route /blog/{id}/edit, nous n'entrons donc pas dans le if. 

        // Si la condition IF retourne TRUE, cela veut dire que $article contient un article stocké en BDD, on stock la photo actuellement de l'article dans la variable $photoActuelle
        if($article)
        {
            $photoActuelle = $article->getPhoto();
        }



        if(!$article)
        {
            $article = new Article;
        }
        

        $formArticle=$this->createForm(ArticleType::class, $article);//Permet de générer un formulaire (via ArticleType correspondant à l'entité Article) que l'on transfert au template blog_create via la variable $formArticle.

        //HandleRequest() permet d'envoyer chaque donnée de $_POST et de les transmettre aux bon setter de l'objet entité $article.
        $formArticle->handleRequest($request);//$request donne accès à toutes les superglobales PHP (post, get, server ....)

        //Si le formulaire a bien été validé (isSubmitted()) et que l'objet entité est correctement remplit (isValid()) alors on entre dans le IF.
        if($formArticle->isSubmitted() && $formArticle->isValid()) //isValid() permet de controler que toutes les valeurs saisies correspondent bien aux valeurs de l'objet $formArticle.
        {
            //Le seul setter auquel on fait appel est setDate() car il n'y a pas de champs DATE dans le formulaire. 
            //Si l'article ne possède pas d'ID, alors on entre dans la condition if. 
            if (!$article->getId())
            $article->setDate(new \DateTime());

                $photo = $formArticle->get('photo')->getData();
                

            if($photo)
            {
                $nomOriginePhoto = pathinfo($photo->getClientOriginalName(), PATHINFO_FILENAME);
                // dd($nomOriginePhoto);

                $secureNomPhoto = $slugger->slug($nomOriginePhoto);

                $nouveauNomFichier = $secureNomPhoto . '-' . uniqid() . '.' . $photo->guessExtension();
                // dd($nouveauNomFichier);

                try
                {
                    // on copie l'image vers le bon chemin, vers le bon dossier 'public/uploads/photos' (services.yaml)
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
            else // sinon aucune image n'a été uplodé, on renvoi dans la bdd la photo actuelle de l'article
            {
                if(isset($photoActuelle))
                $article->setPhoto($photoActuelle);
                else
                $article->setPhoto(null);
            }
            // dd($article);

            //Message de validation insertion
            if(!$article->getId())
                $txt="enregistré";
            else
                $txt="modifié";
            // Méthode permettant d'enregistrer des messages utilisateurs accessibles en session. 
            $this->addFlash('success', "L'article a été $txt avec succès !");

            $manager->persist($article);
            $manager->flush();
            //Si modification on redirige vers l'article modifié.
            return $this->redirectToRoute('blog_show', [
                    'id'=>$article->getId()
            ]);
        }
        //La class Request de Symfony contient toutes les données véhiculées par les superglobales ($_GET, $_POST, $_SERVER, $_COOKIE ...)
        //$request->request : La propriété 'request' de l'objet $request contient toutes les données de $_POST.
        //Si les données dans le tableau ARRAY $_POST sont supérieur à 0, alors on entre dans la condition if. 
        // if($request->request->count()>0)
        // {
            // dd($request);
            //Pour insérer dans la table SQL 'article', nous avons besoin d'un objet de son entité correspondante. 
            // $article=new Article;

            // $article->setTitre($request->request->get('titre'))
            //         ->setContenu($request->request->get('contenu'))
            //         ->setPhoto($request->request->get('photo'))
            //         ->setDate(new \DateTime());

            // dd($article);
            //Méthode issue de l'interface EntityManagerInterface permettant de préparer la requete d'insertion et de garder en mémoire l'objet / la requete.
            // $manager->persist($article);
            
            //flush() : méthode de l'interface EntityManagerInterface permettant véritablement d'exécuter la requete INSERT en BDD (ORM doctrine)
            // $manager->flush();
        
        return $this->render('blog/blog_create.html.twig', [
            'formArticle'=>$formArticle->createView(), //on transmet le formulaire au template afin de pouvoir l'afficher avec Twig.
            //createView() retourne un objet qui représente l'affichage du formulaire, on le récupère dans le template_blog_create.html.twig
            'editMode'=> $article->getId(),
            'photoActuelle' =>$article->getPhoto()
        ]);
    }

    // Méthode permettant d'afficher le détail d'un article.
    // On définit une route 'paramétrée' {id}, ici la route permet de recevoir l'id d'un article stocké en BDD.   
    #[Route('/blog/{id}', name: 'blog_show')]
    public function blogShow(Article $article, Request $request, EntityManagerInterface $manager): Response
    {

        $user = $this->getUser();
        dd($user);

        
        $comment = new Comment;
        $formComment = $this->createForm(CommentType::class, $comment);
        $formComment->handleRequest($request);

        if($formComment->isSubmitted() && $formComment->isValid())
        {
            $comment->setDate(new \DateTime())
                        ->setArticle($article);

            //dd($comment);

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', "Félicitations ! Votre commentaire a bien été posté !");

            return $this->redirectToRoute('blog_show', [
                'id' => $article->getID()
            ]);
        }

        /* 
        Ici, nous envoyons un id dans l'URL et nous imposons en argument un objet issu de l'entité/class Article donc de la table SQL. Symfony comprend est capable de sélectionner en BDD l'article en fonction de l'id passé en URL et de l'envoyer en URL et de l'envoyer automatiquement en argument de la méthode blogShow() dans la variable $article.
        */

        // dd($article);
        //renvoie un objet de la class ArticleRepository
        // $repoArticle=$doctrine->getRepository(Article::class);
        
        //on recupère l'article avec le id appelé via la méthode find issue de la classe ArticleRepositary permettant de sélectionner un élément par son ID.
        // $article=$repoArticle->find($id);
        // dd($article);

        //L'id transmit dans la route 'blog/5' est transmit automatiquement en argument de la méthode blogShow($id) dans la variable de récéption $id
        // dd($id);
        

        return $this->render('blog/blog_show.html.twig', [
            'article'=>$article, //on transmet le template de l'article sélectionné en BDD afin que Twig puisse traiter et afficher les données sur la page.
             'formComment'=> $formComment->createView()
        ]);
    }
    
}

