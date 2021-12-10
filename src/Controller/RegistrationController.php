<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        if($this->getUser())
        {
            return $this->redirectToRoute('blog');
        }


        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'userRegistration' => true // on précise dans quelle consition on entre dans la classe RegistrationFormType pour afficher un formulaire en particulier, ma classe contient plusieurs formulaire
        ]);
        $form->handleRequest($request);

        // on fait appel l'objet $userpasswordHasher de l'interface UserPasswordHasherInterface afin d'encoder le mot de passe en BDD
        if ($form->isSubmitted() && $form->isValid()) {
           
            $hash = $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                
            );

            // dd($hash);

            $user->setpassword($hash);

            $this->addFlash('success', "Félicitation ! vous êtes maintenant inscrit sur le site");

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    #[Route('/profil', name: 'app_profil')]
    public function userProfil(): Response
    {
        // si getUser est null, et ne renvoi aucune données, cela veut dire que l'internaute n'est pas authentifié, il n'a rien à faire sur la route '/profil/, on le redirige vers la route de connexion
        if(!$this->getUser())
        {
            return $this->redirectToRoute('app_login');
        }

        // retourne un objet App\Entity\User contenant  le sinformations de l'utilisateur authentifié sur le site, ces données sont stockées dans le site
        $user = $this->getUser();

        return $this->render('registration/profil.html.twig', [
            'user' => $user
        ]);

    }

    #[Route('/profil/{id}/edit', name: 'app_profil_edit')]
    public function userProfilEdit(User $user, Request $request, EntityManagerInterface $manager): Response
    {

        //dd($user);

        $formUpdate = $this->createForm(RegistrationFormType::class, $user, [
            'userUpdate' => true
        ]);

        $formUpdate->handleRequest($request);

        if($formUpdate->isSubmitted() && $formUpdate->isValid())
        {
            //dd($user);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', "Vous avez modifié vos informations , merci de vous authentifier à nouveau. ");

            return $this->redirectToRoute('app_logout');
        }

        return $this->render('registration/profil_edit.html.twig', [
            'formUpdate' => $formUpdate->createView()
        ]);
    }

}


// exo : créer une page profil affichant les données de l'utilisateur authentifié

        //  1. créer une nouvelle route '/profil'
        // 2. créer une méthode userProfil()
        // 3. cette méthode renvoi un template 'registration/profil.html.twig'
        // 4. afficher dans ce template les informations de l'utilisateur connecté
