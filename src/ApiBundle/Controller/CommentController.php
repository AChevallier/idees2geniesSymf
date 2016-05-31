<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use ApiBundle\Entity\User;
use ApiBundle\Entity\Idea;
use ApiBundle\Entity\Comment;
use ApiBundle\Entity\VoteUserIdea;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class CommentController extends Controller
{
    public function addAction(Request $request)
    {
        try{
            $token = $request->headers->get('token');

            $em = $this->getDoctrine()->getEntityManager();
            $repository = $em->getRepository('ApiBundle:User');
            $data = json_decode($request->getContent(), true);
            $user = $repository->findOneBy(array('token' => $token));

            if($user){
                $valideToken = $user->getValideToken();
                $idUser = $user->getId();
                $date = new \DateTime();

                if($valideToken > $date){

                    if(isset($data['comment']) && isset($data['idIdea'])){

                        $repository = $em->getRepository('ApiBundle:Idea');
                        if($idea = $repository->findOneBy(array('id' => $data['idIdea']))){

                            $comment = new Comment();
                            $comment->setComment($data['comment']);
                            $comment->SetIdIdea($data['idIdea']);
                            $comment->setPublicateDate($date = new \DateTime());
                            $comment->setIdUser($user->getId());

                            $em->persist($comment);
                            $em->flush();

                            $data = array(
                                'Ok' => 'Oui',
                            );

                            return $this->get('service_data_response')->JsonResponse($data);


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

}