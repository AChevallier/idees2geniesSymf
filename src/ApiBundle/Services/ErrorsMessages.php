<?php

namespace ApiBundle\Services;
use Symfony\Component\HttpFoundation\JsonResponse;


class ErrorsMessages
{
    public function __construct() {

    }

    // Fonction qui renvoie un message d'erreur en JSON avec un code 500
    public function errorMessage($errorCode){
        switch ($errorCode) {
            case "001":
                $arr = array('code_metier' => '001', 'message' => "Une erreur interne s'est produite.");
                break;
            case "002":
                $arr = array('code_metier' => '002', 'message' => "Paramètre(s) manquant(s).");
                break;
            case "003":
                $arr = array('code_metier' => '003', 'message' => "Le login ou le mot de passe est incorrect.");
                break;
            case "004":
                $arr = array('code_metier' => '004', 'message' => "Accès refusé.");
                break;
            case "005":
                $arr = array('code_metier' => '005', 'message' => "Votre token est expiré.");
                break;
            case "006":
                $arr = array('code_metier' => '006', 'message' => "Cet utilisateur existe déjà.");
                break;
            case "007":
                $arr = array('code_metier' => '007', 'message' => "Vous n'êtes pas administrateur.");
                break;
            case "008":
                $arr = array('code_metier' => '008', 'message' => "Utilisateur inconnu.");
                break;
            case "009":
                $arr = array('code_metier' => '009', 'message' => "Cette communauté existe déjà.");
                break;
            case "010":
                $arr = array('code_metier' => '010', 'message' => "Cette communauté n'existe pas.");
                break;
            case "011":
                $arr = array('code_metier' => '011', 'message' => "Cette idée n'existe pas.");
                break;
            case "012":
                $arr = array('code_metier' => '012', 'message' => "Ce commentaire n'existe pas.");
                break;
            case "013":
                $arr = array('code_metier' => '013', 'message' => "Les champs doivent être remplis.");
                break;
            case "014":
                $arr = array('code_metier' => '014', 'message' => "Ce commentaire n'existe pas.");
                break;
            default:
                $arr = array('code_metier' => '001', 'message' => "Une erreur interne s'est produite.");
        }

        $reponse = $arr = array('error' => $arr);
        $reponse = json_encode($reponse,JSON_UNESCAPED_UNICODE);

        return new JsonResponse($reponse, 500);
    }
}