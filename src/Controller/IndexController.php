<?php

namespace App\Controller;

use App\Entity\Friends;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
Use App\Entity\User;

class IndexController extends AbstractController
{
    /**
     * @Route("/index", name="homepage")
     */
    public function index()
    {
        if ($this->get("security.authorization_checker")->isGranted('IS_AUTHENTICATED_FULLY')) {
        }
        return $this->render('homepage.html.twig');
    }

    /**
     * @Route("/friends", name="friends")
     */
    public function friendsPage()
    {
        $friends = $this->getDoctrine()
            ->getRepository(Friends::class)
            ->findOneBy(['name' => $this->_currentUser()->getUsername()]);

        $friends = explode(";",$friends->getFriendsname());

        return $this->render('friends/friends.html.twig',[
            'friends' => $friends
        ]);
    }
    /**
    * @Route("/searchfriends", name="search_friends")
    */
    public function searchFriends(Request $request)
    {
        $conn = $this->getDoctrine()->getConnection();
        if(isset($_POST['_search'])){
            $search = $_POST['_search'];
        }
        else{
            $search = [0 => "toto"];
        }

        $sql = "
        SELECT * FROM friends
        WHERE name LIKE '%$search[0]%'
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $this->render('friends/search.html.twig',[
            'friends' => $results
        ]);
    }
    /**
     * @Route("/addfriends", name="add_friends")
     */
    public function addFriends()
    {
        $friend = $this->getDoctrine()
            ->getRepository(Friends::class)
            ->findOneBy(['name' => $this->_currentUser()->getUsername()]);
        $allFriends = $friend->getFriendsname();

        if (isset($_POST['button']))
        {
            $allFriends = $allFriends . $_POST['friendname'] . ";";
        }

        $em = $this->getDoctrine()->getManager();
        $friend->setFriendsName($allFriends);
        $em->flush();

        return $this->render('friends/search.html.twig',
            [
                'post' => $_POST
            ]);
    }

    private function _currentUser(){
        $currentUser = $this->get('security.token_storage')->getToken()->getUser();
        return $currentUser;
    }
}