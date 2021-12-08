<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use phpDocumentor\Reflection\Types\Void_;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
       $faker = \Faker\Factory::create('fr_FR');


       // Création de 3 catégories
        for($cat = 1; $cat <= 3; $cat++)
        {
            $category = new Category;

            $category->setTitre($faker->word)
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 10 article par catégorie
            for($art = 1; $art <= mt_rand(4,10); $art++)
            {
                $article = new Article; 

                $contenu = '<p>' . join('</p><p>', $faker->paragraphs(5)) . '</p>';
                $article->setTitre($faker->sentence())
                        ->setContenu($contenu)
                        ->setPhoto(null)
                        ->setDate($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category); // on relie les articles aux categorie déclaréesci-dessus, le setteur attend en argument l'objet entité entité $category pur la clé étrangère et non un int

                        $manager->persist($article);

                    for($cmt = 1; $cmt <= mt_rand(4,10); $cmt++)
                    {
                        $comment = new Comment;

                        // Traitement des dates
                        $now = new DateTime();
                        $interval = $now->diff($article->getDate()); // retourne un timestanp (un temps en secondes ) entre la date de création des articles et aujourd'hui 

                        $days = $interval->days;

                        // Traitement des paragraphes commentaire

                        $contenu = '<p>' . join('</p><p>',$faker->paragraphs(2)) . '</p>';

                        $comment->setAuteur($faker->name)
                                ->setCommentaire($contenu)
                                ->setDate($faker->dateTimeBetween("-$days days")) 
                                ->setArticle($article);

                        $manager->persist($comment);
                    }
            }
        }
        $manager->flush();
    }
}
