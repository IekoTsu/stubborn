<?php

namespace App\Controller;

use App\Entity\SweatShirts;
use App\Form\AddSweatShirtType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class BackOfficeController extends AbstractController
{
    #[Route('/admin', name: 'back_office')]
    #[IsGranted('ROLE_ADMIN')]
    public function backOffice(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $newSweatShirt = new SweatShirts();
        $addForm = $this->createForm(AddSweatShirtType::class, $newSweatShirt, ['img_required' => true,]);
            

        $addForm->handleRequest($request);
        if ($addForm->isSubmitted() && $addForm->isValid()) {
            $imageFile = $addForm->get('img')->getData();

            if ($imageFile) {
                $newFilename = $this->uploadImage($imageFile, $slugger);
                $newSweatShirt->setImg($newFilename);
            } else {
                $this->addFlash('alert', 'Veuillez télécharger une image.');
            }
 
            $entityManager->persist($newSweatShirt);
            $entityManager->flush();
            $this->addFlash('success', "un nouveau sweat-shirt a été ajouté avec succès");
            return $this->redirectToRoute('back_office');
        }

        $sweatShirts = $entityManager->getRepository(SweatShirts::class)->findAll();
        $editForms = [];

        foreach ($sweatShirts as $sweatShirt) {
            $form = $this->createForm(AddSweatShirtType::class, $sweatShirt, [ 'img_required' => false,]);
            $editForms[$sweatShirt->getId()] = $form->createView();
        }

        return $this->render('back_office/index.html.twig', [
            'addForm' => $addForm->createView(),
            'sweatShirts' => $sweatShirts,
            'editForms' => $editForms,
        ]);
    }

    #[Route('/admin/edit/{id}', name: 'app_sweatshirt_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editSweatShirt(Request $request, SweatShirts $sweatShirt, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $editForm = $this->createForm(AddSweatShirtType::class, $sweatShirt);

        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            // Check if a new image was uploaded
            $imageFile = $editForm->get('img')->getData();

            if ($imageFile) {
                // Delete the old image file
                $filesystem = new Filesystem();
                $oldImageFilename = $sweatShirt->getImg();
                $oldImagePath = $this->getParameter('images_directory') . '/' . $oldImageFilename;
                
                if ($oldImageFilename && $filesystem->exists($oldImagePath)) {
                    $filesystem->remove($oldImagePath);
                }
                
                // Upload and set the new image
                $newFilename = $this->uploadImage($imageFile, $slugger);
                $sweatShirt->setImg($newFilename);
            }

            $entityManager->flush();
            
            $this->addFlash('success', "Le sweat-shirt a été modifié avec succès.");
            return $this->redirectToRoute('back_office');
        }

        return $this->render('back_office/index.html.twig', [
            'editForm' => $editForm->createView(),
            'sweatShirt' => $sweatShirt,
        ]);
    }

    #[Route('/admin/delete/{id}', name: 'app_sweatshirt_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteSweatShirt(SweatShirts $sweatShirt, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($sweatShirt);
        $entityManager->flush();

        $this->addFlash('alert', "Le sweat-shirt a été retiré avec succès.");
        return $this->redirectToRoute('back_office');
    }

    private function uploadImage($imageFile, SluggerInterface $slugger): string
    {
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

        try {
            $imageFile->move($this->getParameter('images_directory'), $newFilename);
        } catch (FileException $e) {
            $this->addFlash('alert', 'Failed to upload image');
        }

        return $newFilename;
    }
}


