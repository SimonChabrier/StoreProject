<?php

namespace App\Service;

// create a conveter object to json file class using the SerializerInterface
// this class get objet in entry and return a json file in the public folder

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JsonManager extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Check if the json file exist
     * Get the json file creation date
     * @return boolean
     * @return int $fileCreationDate
     */
    public function checkJsonFile($fileName) 
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        if (file_exists($publicDirectory.'/json/'.$fileName)) {
            $fileCreationDate = filectime($publicDirectory.'/json/'.$fileName);
            return $fileCreationDate;
        }
        return false;
    }

    /**
     * Create a json file from object or objects
     *
     * @param [Entity] $object
     * @param [string] $context
     * @param [string] $fileName
     * @param [string] $format
     * @return void
     */
    public function jsonFileInit($object, $context, $fileName, $format)
    {   
        // supprimer le fichier json existant qui n'est plus à jour
        // car on a modifié une entité
        $this->jsonFileDelete($fileName);

        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);
        file_put_contents($fileName, $object);
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';

        if (!file_exists($publicDirectory.'/json')) {
            mkdir($publicDirectory.'/json', 0777, true);
        }

        rename($publicDirectory.'/'.$fileName, $publicDirectory.'/json/'.$fileName);

        $jsonFile =  file_get_contents($publicDirectory.'/json/'.$fileName);
        return json_decode($jsonFile, true);
    }

    public function jsonFileDelete($fileName)
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        unlink($publicDirectory.'/json/'.$fileName);
        return true;
    }

    /**
     * Add to the json file 
     *
     * @param [type] $object
     * @param [type] $context
     * @param [type] $fileName
     * @param [type] $format
     * @return void
     */
    public function addItemToJsonFile($object, $context, $fileName, $format)
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        $jsonFile =  file_get_contents($publicDirectory.'/json/'.$fileName);
        $jsonFile = json_decode($jsonFile, true);

        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);
        $object = json_decode($object, true);

        array_push($jsonFile, $object);
        $jsonFile = json_encode($jsonFile);

        file_put_contents($fileName, $jsonFile);
        rename($publicDirectory.'/'.$fileName, $publicDirectory.'/json/'.$fileName);

        return json_decode($jsonFile, true);
    }

    /**
     * Update the json file
     *
     * @param [type] $object
     * @param [type] $context
     * @param [type] $fileName
     * @param [type] $format
     * @return void
     */
    public function updateItemInJsonFile($object, $context, $fileName, $format)
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        $jsonFile =  file_get_contents($publicDirectory.'/json/'.$fileName);
        $jsonFile = json_decode($jsonFile, true);

        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);
        $object = json_decode($object, true);
        // update the user by id in the json file
        foreach ($jsonFile as $key => $user) {
            if ($user['id'] == $object['id']) {
                $jsonFile[$key] = $object;
            }
        }

        $jsonFile = json_encode($jsonFile);
        file_put_contents($fileName, $jsonFile);
        // move the json file to the public folder using rename($from, $to)
        rename($publicDirectory.'/'.$fileName, $publicDirectory.'/json/'.$fileName);
        return json_decode($jsonFile, true);
    }

    /**
     * Delete user from json file
     *
     * @param [type] $id
     * @param [type] $fileName
     * @return void
     */
    public function deleteItemFromJsonFile($id, $fileName)
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        $jsonFile =  file_get_contents($publicDirectory.'/json/'.$fileName);
        if($jsonFile) {
            $jsonFile = json_decode($jsonFile, true);
            // find user by id in the json file and remove it
            foreach ($jsonFile as $key => $user) {
                if ($user['id'] == $id) {
                    unset($jsonFile[$key]);
                }
            }

            // the ------- unset() ------- function remove the object datas AND the object index key in the objects array.
            // So I need to reindex objects using  ----- array_values() ------ 
            // This will  preserve the initial array indexes notation :  [{...} {...}] like used in Front End 
            // json_encode only when some indexes are empty create a re-indexed json object like this {0: {}, 1: {}...}
            // And the front end will be broken cause it wait a json object like this [{...} {...}] in ajax response

            $jsonFile = json_encode(array_values($jsonFile));
            file_put_contents($fileName, $jsonFile);
            rename($publicDirectory.'/'.$fileName, $publicDirectory.'/json/'.$fileName);

            return json_decode($jsonFile, true);
        }

        return false;
    }

    /**
     * Search user by id in the json file 
     *
     * @param [type] $id
     * @param [type] $fileName
     * @return void
     */
    public function searchUserInJsonFile($id, $fileName)
    {
        $publicDirectory = $this->getParameter('kernel.project_dir').'/public';
        $jsonFile =  file_get_contents($publicDirectory.'/json/'.$fileName);
       
        if($jsonFile) {
            $jsonFile = json_decode($jsonFile, true);

            // find user by id in the json file and return it
            foreach ($jsonFile as $user) {

                if ($user['id'] == $id) {
                    return $user;
                }
            }
        }

        return false;
    }
}


