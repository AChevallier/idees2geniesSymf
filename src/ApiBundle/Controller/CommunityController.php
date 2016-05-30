<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\UserCommunity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiBundle\Entity\Community;
use Symfony\Component\HttpFoundation\Response;
use ApiBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class CommunityController extends Controller
{

    // Liste les communautés
    public function indexAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $idUser = $user->getId();
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){

                    $qb = $em->createQueryBuilder()
                        ->select('c.id as id, c.name AS nameCommunity, c.description AS descriptionCommunity')
                        ->from('ApiBundle:Community', 'c')
                        ->addOrderBy('c.name', 'ASC');

                        $communities = $qb->getQuery()->getResult();

                    foreach ($communities as $community) {
                        $repository = $this->getDoctrine()->getRepository('ApiBundle:UserCommunity');
                        $joinUser = $repository->findOneBy(array('idUser' => $idUser, 'idCommunity' => $community['id']));

                        if($joinUser){
                            $joinUser = true;
                        }else{
                            $joinUser = false;
                        }

                        $qb = $em->createQueryBuilder()
                            ->select('COUNT(uc.id)')
                            ->from('ApiBundle:UserCommunity', 'uc')
                            ->where('uc.idCommunity = :idCommunity')
                            ->setParameters(array('idCommunity' => $community['id']))
                        ;
                        $nbUsers = $qb->getQuery()->getSingleScalarResult();

                        $tableCommunities[] = array(
                            'id' => $community['id'],
                            'name' => $community['nameCommunity'],
                            'description' => $community['descriptionCommunity'],
                            'joinUser' => $joinUser,
                            'nbUsers' => $nbUsers
                        );
                    }




                        return $this->get('service_data_response')->JsonResponse($tableCommunities);
                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    // Ajoute une communauté
    public function addAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){
                    if($user->getAdministrator() == true) {
                        $data = json_decode($request->getContent(), true);

                        if (!empty(isset($data['name'])) && !empty(isset($data['description']))) {

                            $repository = $em->getRepository('ApiBundle:Community');

                            if (!$community = $repository->findOneBy(array('name' => $data['name']))) {
                                $a = new Community();
                                $a->setName($data['name']);
                                $a->setDescription($data['description']);

                                $em->persist($a);
                                $em->flush();

                                $community = $repository->findOneBy(array('name' => $data['name']));
                                $data = array(
                                    'id' => $community->getId(),
                                    'name' => $community->getName(),
                                    'description' => $community->getDescription(),
                                );
                                return $this->get('service_data_response')->JsonResponse($data);

                            } else {
                                return $this->get('service_errors_messages')->errorMessage("009");
                            }
                        } else {
                            return $this->get('service_errors_messages')->errorMessage("002");
                        }
                    }
                    else{
                        return $this->get('service_errors_messages')->errorMessage("007");
                    }
                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    // Supprime une communauté
    public function deleteAction(Request $request, $id)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){
                    if($user->getAdministrator() == true) {
                        $data = json_decode($request->getContent(), true);

                        if (!empty($id)) {
                            $repository = $em->getRepository('ApiBundle:Community');

                            if ($community = $repository->findOneBy(array('id' => $id))) {

                                $em->remove($community);
                                $em->flush();

                                $data = array(
                                    'message' => "Communauté supprimée.",
                                );
                                return $this->get('service_data_response')->JsonResponse($data);

                            } else {
                                return $this->get('service_errors_messages')->errorMessage("010");
                            }
                        } else {
                            return $this->get('service_errors_messages')->errorMessage("002");
                        }
                    }
                    else{
                        return $this->get('service_errors_messages')->errorMessage("007");
                    }
                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    // Renvoie les communautées de l'utilisateur
    public function myCommunitiesAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){

                    $qb = $em->createQueryBuilder()
                        ->select('uc.idCommunity AS idCommunity, c.name AS nameCommunity, c.description AS descriptionCommunity')
                        ->from('ApiBundle:UserCommunity', 'uc')
                        ->innerJoin('ApiBundle:User', 'u', 'WITH', 'uc.idUser = u.id')
                        ->innerJoin('ApiBundle:Community', 'c', 'WITH', 'uc.idCommunity = c.id')
                        ->where('u.token = :token')
                        ->setParameters(array('token' => $token))
                        ->addOrderBy('c.name', 'ASC')
                    ;
                    $communities = $qb->getQuery()->getResult();

                    $tableCommunities = array();
                    foreach ($communities as $community) {

                        $qb = $em->createQueryBuilder()
                            ->select('count(uc.id)')
                            ->from('ApiBundle:UserCommunity', 'uc')
                            ->where('uc.idCommunity = :idCommunity')
                            ->setParameters(array('idCommunity' => $community['idCommunity']))
                            ->groupBy('uc.idCommunity')
                        ;
                        $nbUsers = $qb->getQuery()->getSingleScalarResult();

                        $qb = $em->createQueryBuilder()
                            ->select('count(i.idCommunauty) AS nbIdeas')
                            ->from('ApiBundle:Idea', 'i')
                            ->where('i.idCommunauty = :idCommunity')
                            ->setParameters(array('idCommunity' => $community['idCommunity']))
                            ->groupBy('i.idCommunauty')
                        ;

                        $nbIdeas = $qb->getQuery()->getOneOrNullResult();

                        if($nbIdeas['nbIdeas'] == null){
                            $nbIdeas = '0';
                        }else{
                            $nbIdeas = $nbIdeas['nbIdeas'];
                        }

                        $tableCommunities[] = array('idCommunity' => $community['idCommunity'],
                            'idCommunity' => $community['idCommunity'],
                            'nameCommunity' => $community['nameCommunity'],
                            'descriptionCommunity' => $community['descriptionCommunity'],
                            'nbUsers' => $nbUsers,
                            'nbIdeas' => $nbIdeas,
                        );
                    }

                    return $this->get('service_data_response')->JsonResponse($tableCommunities);


                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    // Liste les communautés de l'utilisateur demandé
    public function userCommunitiesAction(Request $request, $id)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){
                    if($user->getAdministrator() == true) {

                        if (!empty(isset($id))){

                            if($user = $repository->findOneBy(array('id' => $id))) {

                                $qb = $em->createQueryBuilder()
                                    ->select('uc.idCommunity AS idCommunity, c.name AS nameCommunity, c.description AS descriptionCommunity')
                                    ->from('ApiBundle:UserCommunity', 'uc')
                                    ->innerJoin('ApiBundle:User', 'u', 'WITH', 'uc.idUser = u.id')
                                    ->innerJoin('ApiBundle:Community', 'c', 'WITH', 'uc.idCommunity = c.id')
                                    ->where('u.id = :id')
                                    ->setParameters(array('id' => $id))
                                    ->addOrderBy('c.name', 'ASC')
                                    ;
                                $data = $qb->getQuery()->getResult();

                                return $this->get('service_data_response')->JsonResponse($data);
                            }
                            else{
                                return $this->get('service_errors_messages')->errorMessage("008");
                            }
                        } else {
                            return $this->get('service_errors_messages')->errorMessage("002");
                        }
                    }
                    else{
                        return $this->get('service_errors_messages')->errorMessage("007");
                    }
                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    public function joinAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){

                    $data = json_decode($request->getContent(), true);

                    $repository = $em->getRepository('ApiBundle:Community');

                    if(isset($data['id'])){
                        if($idea = $repository->findOneBy(array('id' => $data['id']))) {

                            $repository = $em->getRepository('ApiBundle:UserCommunity');

                            if($joinUserCommunity = $repository->findOneBy(array('idUser' => $user->getId(), 'idCommunity' => $data['id']))){

                                $em->remove($joinUserCommunity);
                                $em->flush();

                                $data = array(
                                    'join' => false,
                                );

                                return $this->get('service_data_response')->JsonResponse($data);
                            }else{

                                $joinUserCommunity = new UserCommunity();

                                $joinUserCommunity->setIdCommunity($data['id']);
                                $joinUserCommunity->setIdUser($user->getId());

                                $em->persist($joinUserCommunity);
                                $em->flush();

                                $data = array(
                                    'join' => true,
                                );

                                return $this->get('service_data_response')->JsonResponse($data);
                            }

                        }else{
                            return $this->get('service_errors_messages')->errorMessage("011");
                        }
                    }
                    else{
                        return $this->get('service_errors_messages')->errorMessage("002");
                    }
                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }

    // Récupère les information sur la communité demandée
    public function getCommunityAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');

            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $date = new \DateTime();

                if($valideToken > $date){

                    $data = json_decode($request->getContent(), true);
                    $repository = $em->getRepository('ApiBundle:Community');

                    if(!empty($data['id']) && isset($data['id'])){

                        $repository = $em->getRepository('ApiBundle:Community');

                        if($community = $repository->findOneBy(array('id' => $data['id']))) {


                            $qb = $em->createQueryBuilder()
                                ->select('c.id AS id, c.name AS name, c.description AS description')
                                ->from('ApiBundle:Community', 'c')
                                ->where('c.id = :id')
                                ->setParameters(array('id' => $data['id']))
                            ;
                            $community = $qb->getQuery()->getSingleResult();

                            $data = array(
                                'id' => $community['id'],
                                'name' => $community['name'],
                                'description' => $community['description'],
                            );

                            return $this->get('service_data_response')->JsonResponse($data);
                        }
                        else{
                            return $this->get('service_errors_messages')->errorMessage("010");
                        }




                    }else{
                        return $this->get('service_errors_messages')->errorMessage("002");
                    }


                }else{
                    return $this->get('service_errors_messages')->errorMessage("005");
                }
            }else{
                return $this->get('service_errors_messages')->errorMessage("004");
            }
        }catch(Exception $ex) {
            return $this->get('service_errors_messages')->errorMessage("001");
        }
    }
}
