<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Service\Utils\ConfigurationService;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Service\Notify\EmailService;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Security\RegistrationFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;


class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    private ConfigurationService $configuration;

    public function __construct(
        EmailVerifier $emailVerifier, 
        ConfigurationService $configuration
    )
    {
        $this->emailVerifier = $emailVerifier;
        $this->configuration = $configuration;

    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager, EmailService $emailService): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address($this->configuration->getConfiguration()->getAdminMail(), 'Sneakers Shop'))
                    ->to($user->getEmail())
                    ->subject('Merci de confirmer votre Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            // on retourne le user à l'authenticator pour qu'il soit authentifié directement après son inscription.
            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // si le user a déjà vérifié son email, on le redirige vers la page d'accueil et on affiche un message flash
        if ($user->isVerified() === true) {
            $this->addFlash('warning', 'Votre email est déjà vérifié !');
            return $this->redirectToRoute('app_home');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {

            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));
            return $this->redirectToRoute('app_register');
        }

        // We redirect on home page after email verification and add dispay a flash message
        $this->addFlash('success', 'Votre email a bien été vérifié !');
        return $this->redirectToRoute('app_home');
    }
}
