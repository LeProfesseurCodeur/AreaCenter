<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Section;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class SectionController extends AbstractController
{
    /**
     * @Route("/groups/{id}/section/create", name="create_section")
     * @param $id
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create($id, Request $request, Security $security)
    {
        $user = $security->getUser();
        $section = new Section();

        $section->setGroupId($id);
        $section->setCreatedBy($user->getUsername());
        $section->setSubscriber(1);
        $section->setSubscribersName(";" . $user->getUsername());

        $form = $this->createFormBuilder($section)
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Section'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($section);
            $entityManager->flush();
            return $this->redirectToRoute('groups_view', ['id' => $id]);
        }


        return $this->render('section/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/groups/{id}/section/{idSection}", name="show_section")
     * @param $id
     * @param $idSection
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($id, $idSection)
    {
        $articleSection = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(['sectionId' => $idSection]);

        return $this->render('section/show.html.twig', [
            'idSection' => $idSection,
            'id' => $id,
            'articleSection' => $articleSection
        ]);
    }

    /**
     * @Route("/groups/{id}/section/{idSection}/createarticle", name="create_section_article")
     * @param $id
     * @param $idSection
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createSectionArticle($id, Request $request, Security $security, $idSection)
    {
        $user = $security->getUser();
        $article = new Article();

        $article->setCreatedBy($user->getUsername());
        $article->setLikes(0);
        $article->setGroupId($id);
        $article->setSectionId($idSection);

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('tag', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Article'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('show_section', ['id' => $id, 'idSection' => $idSection]);
        }

        return $this->render('section/createArticle.html.twig', [
            $idSection => 'idSection',
            'form' => $form->createView()
        ]);
    }

}
