<?php


namespace App\Controller;

use App\Entity\Article;
use App\Entity\Groups;
use App\Entity\Meeting;
use App\Entity\Section;
use DateTime;
use Doctrine\DBAL\Types\ArrayType;
use FOS\UserBundle\Model\Group;
use phpDocumentor\Reflection\Types\String_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Tests\Fixtures\includes\HotPath\P1;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
use Symfony\Component\Security\Core\Security;


class GroupsController extends AbstractController
{
    /**
     * @Route("/groups/create", name="groups_index")
     */
    public function create(Security $security, Request $request)
    {
        $user = $security->getUser();

        $groups = new Groups;
        $groups->setCreatedby($user->getUsername());
        $groups->setCreatedAt(new \DateTime("now"));
        $groups->setSubscriber(0);
        $groups->setSubgroups('');
        $groups->setSubscribersName($user->getUsername());

        $form = $this->createFormBuilder($groups)
            ->add('name', TextType::class, ['label' => 'Nom du groupe'])
            ->add('description', TextType::class, ['label' => 'Description du groupe'])
            ->add('accessibility', ChoiceType::class, [
                'choices'  => [
                    'Privé' => 'private',
                    'Public' => 'public'],
                ])
            ->add('tag', TextType::class, [
                'label' => 'Tag de votre groupe',
                'help' => 'Vous pouvez en utiliser plusieurs en les separant avec des points virgules ex : voiture;roue;moto'
            ])
            ->add('save', SubmitType::class, ['label' => 'Creer le groupe'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($groups);
            $entityManager->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('groups/index.html.twig', [
            'form' => $form->createView(),
            'userGroups' => $this->_allUsersGroups($security)
        ]);
    }

    /**
     * @Route("/groups/{id}", name="groups_view")
     * @param $id
     * @param Security $security
     * @return Response
     */
    public function index($id, Security $security)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);

        $article = $this->getDoctrine()
            ->getRepository(Article::class)
            ->findBy(['groupId' => $id]);

        $section = $this->getDoctrine()
            ->getRepository(Section::class)
            ->findBy(['groupId' => $id]);

        if($group == null){
            return $this->render('errorServor/404.html.twig');
        }
        $totalFollowers = $this->_countAllFollowers($id);
        $followed = null;
        $subscribersName = explode(";",$group->getSubscribersName());
        if ($user = $security->getUser() == null)
        {
            return $this->render('groups/show.index.html.twig', [
                'name' => $group->getName(),
                'id' => $id,
                'followed' => false,
                'countFollowers' => $totalFollowers,
                'articles' => $article,
                'connected' => false,
                'section' => $section
            ]);
        }
        $user = $security->getUser();
        $currentUser = $user->getUsername();

            return $this->render('groups/show.index.html.twig', [
                'name' => $group->getName(),
                'id' => $id,
                'followed' => true,
                'countFollowers' => $totalFollowers,
                'articles' => $article,
                'connected' => true,
                'section' => $section
            ]);
    }
    /**
     * @Route("/groups/follow/{id}", name="follow_groups")
    */
    public function followGroups($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);
        $em = $this->getDoctrine()->getManager();
        $subscribersName = explode(";",$group->getSubscribersName());
        $currentUser = $this->get('security.token_storage')->getToken()->getUser()->getUsername();
        array_push($subscribersName, $currentUser);
        $pushToDbFollowing = '';
        foreach ($subscribersName as $value)
        {
            $pushToDbFollowing = $pushToDbFollowing . $value . ";";
        }
        $group->setSubscribersName($pushToDbFollowing);
        $em->flush();
        $this->_addFollowers($id);
        return $this->redirectToRoute('groups_view', ['id' => $id, 'followed' => true]);
    }
    /**
     * @Route("/groups/unfollow/{id}", name="unfollow_groups")
     */
    public function unFollowGroups($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);
        $em = $this->getDoctrine()->getManager();
        $subscribersName = explode(";",$group->getSubscribersName());
        $currentUser = $this->get('security.token_storage')->getToken()->getUser()->getUsername();

        $keyUnfollow = array_search($currentUser, $subscribersName);
        unset($subscribersName[$keyUnfollow]);

            $pushToDbFollowing = "";
        foreach ($subscribersName as $value)
        {
            if($value !== ""){
                $pushToDbFollowing = ";" . $pushToDbFollowing . $value;
            }
        }
        $group->setSubscribersName($pushToDbFollowing);
        $em->flush();
        $this->_lessFollowers($id);
        return $this->redirectToRoute('groups_view', ['id' => $id, 'followed' => false]);
    }

    /**
     * @Route("/groups/subscribers/{id}", name="list_all_subscribers_group")
     */
    public function listAllSubscribersGroup($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findBy(['id' => $id]);
        $allSubscribers = explode(";",$group[0]->getSubscribersName());
        foreach ($allSubscribers as $key => $value){
            if($value == "")
            {
                unset($allSubscribers[$key]);
            }
        }

        return $this->render('groups/subscribers.html.twig', [
            'id' => $id,
            'totalSubscribers' => $allSubscribers,
        ]);
    }

    /**
     * @Route("/search", name="search_groups")
     * @param Request $request
     * @return Response
     */
    public function searchGroups(Request $request)
    {
        $conn = $this->getDoctrine()->getConnection();
        if(isset($_POST['_search'])){
            $search = $_POST['_search'];
        }
        else{
            $search = [0 => "toto"];
        }

        $sql = "
        SELECT * FROM GROUPS 
        WHERE tag LIKE '%$search%'
        OR name LIKE  '%$search%'
        OR description LIKE '%$search%'
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll();

        if($this->_isNull($results)){
            $results = array(0);
        }
            return $this->render('groups/searchGroups.html.twig', [
            'groups' => $results,
            'post' => $search
        ]);
    }

    /**
     * @Route("/create/meeting/{id}", name="create_meeting")
     * @param $id
     * @param Security $security
     * @return Response
     */
    public function createMeeting($id, security $security)
    {

        $conn = $this->getDoctrine()->getConnection();
        if (isset($_POST['validatemeeting']))
        {
            $date = $_POST['date'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $user = $security->getUser();
            $currentUser = $user->getUsername();

            $sql = "
        INSERT INTO meeting
        (name, description, date, created_by, id_groups)
        VALUES
        ('$name', '$description', '$date', '$currentUser', '$id')
        ";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            echo"<script language=\"javascript\">";
            echo"alert('Confirmation de votre meeting prevu pour le $date')";
            echo"</script>";
        }


        return $this->render('meeting/createmeeting.html.twig');
    }

    private function _isNull($parametersToVerify){
        if ($parametersToVerify == null){
            return true;
        }
    }

    private function _allUsersGroups(Security $security){
        $user = $security->getUser();
        $repository = $this->getDoctrine()->getRepository(Groups::class);
        $groups = $repository->findBy([
            'createdby' => $user->getUsername(),
        ]);

        return $groups;
    }

    private function _addFollowers($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);
        $em = $this->getDoctrine()->getManager();

       $group->setSubscriber($group->getSubscriber() +1 );
       $em->flush();
    }

    private function _lessFollowers($id)
    {
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);
        $em = $this->getDoctrine()->getManager();

        $group->setSubscriber($group->getSubscriber() -1 );
        $em->flush();
    }

    private function _countAllFollowers($id){
        $group = $this->getDoctrine()
            ->getRepository(Groups::class)
            ->findOneBy(['id' => $id]);
        $subscribersName = explode(";",$group->getSubscribersName());
        foreach ($subscribersName as $key => $value){
            if($value == "")
            {
                unset($subscribersName[$key]);
            }

        }
        $nbrFollowers = count($subscribersName);
        $group->setSubscriber($nbrFollowers);

        return $nbrFollowers;
    }

}