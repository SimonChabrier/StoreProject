<?php

namespace App\Service;


use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


// TODO typer les paramètres et les retours de fonctions

class JsonFileUtils extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get the path for the JSON directory
     * 
     * @return string The path for the JSON directory
     */
    private function getJsonPath()
    {
        return $this->getParameter('kernel.project_dir') . '/public/json';
    }

    /**
     * Get the full path for a JSON file
     *
     * @param string $fileName The name of the JSON file
     * @return string The full path for the JSON file
     */
    private function getJsonFilePath(string $fileName)
    {
        return $this->getParameter('kernel.project_dir') . '/public/json/' . $fileName;
    }

    /**
     * Check if the json directry exist
     * If not, create it with the right permissions (0775)
     * and give the right permissions to the www-data group
     * for the server to be able to write in it when app is in production.
     * 
     * @return boolean
     * @return int $fileCreationDate
     */
    public function createJsonDirectory()
    {
        $publicDirectory = $this->getJsonPath();

        // if the json directory doesn't exist, create it
        if (!is_dir($publicDirectory)) {
            // Crée le dossier avec les droits 0775
            if (mkdir($publicDirectory, 0775, true)) {
                // Si l'identifiant de groupe (GID) de www-data existe, on utilise chgrp pour changer le groupe
                if (function_exists('posix_getgrnam')) {
                    $groupInfo = posix_getgrnam('www-data');
                    if ($groupInfo !== false) {
                        chgrp($publicDirectory, $groupInfo['gid']);
                    }
                }
                // Donne des droits d'écriture au groupe www-data pour le serveur...
                chmod($publicDirectory, 0775);
                return true;
            }
            return false;
        }

        return true;
    }

    /**
     * Check if the json file exist and get its creation date
     *
     * @param string $fileName
     * @return false|int
     */
    public function checkJsonFile(string $fileName)
    {
        // Return the file creation date if it exists, otherwise false
        return file_exists($this->getJsonFilePath($fileName)) ? filectime($this->getJsonFilePath($fileName)) : false;
    }

    /**
     * Delete the json file if it's older than 1 hour
     *
     * @param string $fileName
     * @return void
     */
    public function deleteOldJsonFile(string $fileName)
    {
        $fileCreationDate = $this->checkJsonFile($fileName);

        if ($fileCreationDate !== false) {
            $now = time();
            $diff = $now - $fileCreationDate;

            if ($diff > 3600) {
                unlink($this->getJsonFilePath($fileName));
            }
        }
    }

    /**
     * Create a JSON file from an Entity
     *
     * @param [Entity] $object The Entity to be serialized and saved as JSON
     * @param [string] $context Serialization context (e.g., 'product:read' or 'user:read')
     * @param [string] $fileName The name of the JSON file to be created
     * @param [string] $format The serialization format (e.g., 'json')
     * @return array The decoded JSON content as an array
     */
    public function createJsonFile(array $object, string $context, string $fileName, string $format): array
    {
        // Delete old file if necessary
        $this->deleteOldJsonFile($fileName);

        // Check and initialize JSON directory if it doesn't exist
        $this->createJsonDirectory();

        // Construct the full path for the JSON file
        $jsonFilePath = $this->getJsonFilePath($fileName);

        // Serialize the object using the specified format and context
        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);

        // Save the serialized object as a JSON file in the public folder
        file_put_contents($jsonFilePath, $object);

        // Read the contents of the saved JSON file
        $jsonFile = file_get_contents($jsonFilePath);

        // Return the decoded JSON content as an array
        return json_decode($jsonFile, true);
    }

    /**
     * Delete the json file if it exists
     *
     * @param string $fileName
     * @return bool
     */
    public function jsonFileDelete($fileName)
    {
        $filePath = $this->getJsonFilePath($fileName);

        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }

        return false;
    }


    // -----------------  JSON FILE UTILS FOR UPDATE JSON FILE CONTENT ----------------- //

    /**
     * Add to the json file
     *
     * @param [type] $object
     * @param [type] $context
     * @param [type] $fileName
     * @param [type] $format
     * @return array
     */
    public function addItemToJsonFile($object, $context, $fileName, $format)
    {
        $jsonFilePath = $this->getJsonFilePath($fileName);

        $jsonFile = file_get_contents($jsonFilePath);
        $jsonFile = json_decode($jsonFile, true);

        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);
        $object = json_decode($object, true);

        array_push($jsonFile, $object);
        $jsonFile = json_encode($jsonFile);

        file_put_contents($jsonFilePath, $jsonFile);

        return json_decode($jsonFile, true);
    }

    /**
     * Update the json file
     *
     * @param [type] $object
     * @param [type] $context
     * @param [type] $fileName
     * @param [type] $format
     * @return array
     */
    public function updateItemInJsonFile($object, $context, $fileName, $format)
    {
        $jsonFilePath = $this->getJsonFilePath($fileName);

        $jsonFile = file_get_contents($jsonFilePath);
        $jsonFile = json_decode($jsonFile, true);

        $object = $this->serializer->serialize($object, $format, ['groups' => $context]);
        $object = json_decode($object, true);

        foreach ($jsonFile as $key => $user) {
            if ($user['id'] == $object['id']) {
                $jsonFile[$key] = $object;
            }
        }

        $jsonFile = json_encode($jsonFile);
        file_put_contents($jsonFilePath, $jsonFile);

        return json_decode($jsonFile, true);
    }

    /**
     * Delete user from json file
     *
     * @param [type] $id
     * @param [type] $fileName
     * @return array|bool
     */
    public function deleteItemFromJsonFile($id, $fileName)
    {
        $jsonFilePath = $this->getJsonFilePath($fileName);

        $jsonFile = file_get_contents($jsonFilePath);

        if ($jsonFile) {
            $jsonFile = json_decode($jsonFile, true);

            foreach ($jsonFile as $key => $user) {
                if ($user['id'] == $id) {
                    unset($jsonFile[$key]);
                }
            }

            $jsonFile = json_encode(array_values($jsonFile));
            file_put_contents($jsonFilePath, $jsonFile);

            return json_decode($jsonFile, true);
        }

        return false;
    }

    /**
     * Search user by id in the json file
     *
     * @param [type] $id
     * @param [type] $fileName
     * @return array|bool
     */
    public function searchUserInJsonFile($id, $fileName)
    {
        $jsonFilePath = $this->getJsonFilePath($fileName);

        $jsonFile = file_get_contents($jsonFilePath);

        if ($jsonFile) {
            $jsonFile = json_decode($jsonFile, true);

            foreach ($jsonFile as $user) {
                if ($user['id'] == $id) {
                    return $user;
                }
            }
        }

        return false;
    }
}
