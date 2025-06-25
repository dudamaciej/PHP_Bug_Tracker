<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\AdminUser;
use App\Form\ProfileType;
use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/profile')]
#[IsGranted('ROLE_ADMIN')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'admin_profile')]
    public function view(): Response
    {
        /** @var AdminUser $user */
        $user = $this->getUser();
        return $this->render('admin/profile/view.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'admin_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em): Response
    {
        /** @var AdminUser $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Profile updated successfully.');
            return $this->redirectToRoute('admin_profile');
        }
        return $this->render('admin/profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password', name: 'admin_profile_password')]
    public function changePassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        /** @var AdminUser $user */
        $user = $this->getUser();
        
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);
        
        if ($request->isMethod('POST') && $form->isSubmitted()) {
            if ($form->isValid()) {
                $formData = $form->getData();
                $currentPassword = $formData['currentPassword'];
                $newPassword = $formData['newPassword'];
                
                if (!$hasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Current password is incorrect. Please try again.');
                } else {
                    $user->setPassword($hasher->hashPassword($user, $newPassword));
                    $em->flush();
                    $this->addFlash('success', 'Password changed successfully.');
                    return $this->redirectToRoute('admin_profile');
                }
            } else {
                foreach ($form->getErrors(true) as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
            
            return $this->redirectToRoute('admin_profile_password');
        }
        
        return $this->render('admin/profile/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 