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
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
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
}


// exo : créer une page profil affichant les données de l'utilisateur authentifié

        //  1. créer une nouvelle route '/profil'
        // 2. créer une méthode userProfil()
        // 3. cette méthode renvoi un template 'registration/profil.html.twig'
        // 4. afficher dans ce template les informations de l'utilisateur connecté