<?php

namespace App\Controller;

use DateTime;
use Doctrine\DBAL\Types\ArrayType;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\P1;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
Use App\Entity\User;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\HttpFoundation\Request;

class SecurityAdministration extends AbstractController
{
    /**
     * @Route("/administration", name="administration")
     */
    public function index()
    {
        if ($this->get("security.authorization_checker")->isGranted('ROLE_ADMIN')) {
            return $this->render('security/administration.html.twig', [
                "lastUsersSignup" => $this->_getLastUsersSignup(),
                 "totalUsers" => $this->_getTotalUsers()
                ]
            );
        }
        else{
            return $this->redirect('/');
        }
    }
    /**
     * @Route("/administration/users", name="administration_users")
     */
    public function users()
    {
        if ($this->get("security.authorization_checker")->isGranted('ROLE_ADMIN')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findAll();
            return $this->render('security/administration_users.html.twig',
            ["users" => $user]
            );
        }
        else{
            return $this->redirect('/');
        }
    }

    /**
     * @Route("/administration/users/edit/{id}", name="administration_users_edit")
     * @param $id
     * @param Request $request
         * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editUsers($id, Request $request)
    {
        if ($this->get("security.authorization_checker")->isGranted('ROLE_ADMIN')) {
            $user = new User();

            $form = $this->createFormBuilder($user)
                ->add('Email')
                ->add('Username')
                ->add('Save', SubmitType::class, ['label' => 'Update User'])
                ->getForm();
            $form->handleRequest($request);

            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository(User::class)->find($id);

            if ($form->isSubmitted() && $form->isValid()) {
                $edit = $form->getData();

                $username = ($edit->getUsername() != NULL) ? $user->setUsername($edit->getUsername()) : NULL;
                $email= ($edit->getEmail() != NULL) ? $user->setEmail($edit->getEmail()) : NULL;
                $em->flush();

            }

            return $this->render('security/administration_users_edit.html.twig', [
                'form' => $form->createView()
            ]);

        }
        else{
            return $this->redirect('/');
        }
    }

    /**
     * @Route("/administration/users/delete/{id}", name="administration_users_delete")
     */
    public function deleteUser($id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository(User::class)->find($id);
        $em->remove($user);
        $em->flush();
        $this->addFlash('message', 'User deleted successfully');

        return $this->redirectToRoute('administration_users');
    }


    private function _getLastUsersSignup()
    {
        $user = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        foreach ($user as $recentSignup){
            $recentSignup = $recentSignup->getCreatedAt();
            $diff = $recentSignup->diff(new \DateTime("now"));
            $totalUser = array();
            if(intval($diff->format("%R%a")[1]) > 1)
            {
                $totalUser[] = array($diff->format("%R%a")[1]);
            }

        }
        return count($totalUser);
    }

    private function _getTotalUsers()
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();
        return count($users);
    }

}