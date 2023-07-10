<?php
namespace App\Components\Errors;

class ResourceMessage {

    public static function resourceNotFoundMessage($resourceName,$resourceId):string{
        return "Pas de ressource {$resourceName} trouver pour l'id {$resourceId} cette ressource à peut être été supprimée ou n'existe plus";
    }

    public static function resourceIsUpdateMessage($resourceName):string{
        return "La ressource {$resourceName} a été mise à jour";
    }

    public static function resourceIsNotUpdateMessage($resourceName){
        return "Une erreur s'est produite lors de la mise à jour de {$resourceName}";
    }

    public static function resourceIsDeleteMessage($resourceName):string{
        return "La ressource {$resourceName} a été supprimée";
    }

    public static function resourceUniqueMessage(string $resourceName):string{
        return "Une ressource {$resourceName} porte déja le meme nom";
    }

    public static function resourceValidationMessage(?string $resourceName=null):string{
        return "Une erreur de validation s'est produite {$resourceName}";
    }

    public static function resourceSavedMessage(?string $resourceName=null):string{
        return "Une erreur s'est produite lors de l'enrégistrement {$resourceName}";
    }

    public static function resourceCreatedMessage(string $resourceName):string{
        return "La ressource {$resourceName} a été ajoutée ";
    }

    public static function resourceFetchMessage(string $resourceName):string{
        return "Une ressource de {$resourceName}";
    }

    public static function resourceListMessage(string $resourceName):string{
        return "La liste des {$resourceName}";
    }

    public static function resourceAlreadyHaveThatMessage(string $firstResourceName,string $secondResourceName){
        return "{$firstResourceName} dispose déja de cette ressource {$secondResourceName}";
    }

    public static function resourceFileCanNotBeCreate(string $resourceName){
        return "Une erreur s'est produite lors de la creation d'une ressource Fichier pour {$resourceName}";
    }
}
