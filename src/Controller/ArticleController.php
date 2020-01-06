<?php
/**
 * Created by PhpStorm.
 * User: Mehdy
 * Date: 22/05/2019
 * Time: 12:20
 */

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use phpDocumentor\Reflection\Types\Nullable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/groups/{id}/article/create", name="create_article")
     * @param $id
     * @param Request $request
     * @param Security $security
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createArticle($id,Request $request,Security $security)
    {

        $user = $security->getUser();
        $article = new Article();

        $article->setCreatedBy($user->getUsername());
        $article->setLikes(0);
        $article->setGroupId($id);
        $article->setSectionId(0);

        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('description', TextType::class)
            ->add('videolink', TextType::class)
            ->add('imagelink', TextType::class)
            ->add('tag', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Article'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            return $this->redirectToRoute('groups_view', ['id' => $id]);
        }

        return $this->render('article/create.html.twig', [
            'id' => $id,
            'form' => $form->createView()
        ]);
    }
    /**
     * @Route("/groups/{id}/article/{articleId}", name="show_article")
     */
    public function showArticle($id, $articleId,Request $request, Security $security)
    {
        $comment = new Comment();
        $user = $security->getUser();

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findOneBy(['id' => $articleId]);

        $allComments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['articleId' => $articleId], ['createdAt' => 'DESC']);

        $comment->setCreatedBy($user->getUsername());
        $comment->setArticleId($articleId);

        $commentForm = $this->createFormBuilder($comment)
            ->add('title', TextType::class)
            ->add('body', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Commenter'])
            ->getForm();

        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('show_article',
                [
                    'id' => $id,
                    'articleId' => $articleId,
                    'article' => $article,
                    'commentForm' => $commentForm->createView(),
                    'comments' => $allComments,
                ]);
        }


        return $this->render('article/show.html.twig', [
            'id' => $id,
            'articleId' => $articleId,
            'article' => $article,
            'commentForm' => $commentForm->createView(),
            'comments' => $allComments,
        ]);
    }

    /**
     * @Route("/groups/{id}/article/{articleId}/comment", name="create_comment")
     */
    public function createComment(Request $request,Security $security)
    {
        $user = $security->getUser();
        $comment = new Comment();

    }

}