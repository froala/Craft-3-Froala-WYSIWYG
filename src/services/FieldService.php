<?php

namespace froala\craftfroalawysiwyg\services;

use craft\base\Component;
use craft\errors\InvalidSubpathException;
use craft\errors\InvalidVolumeException;
use craft\helpers\Html;
use craft\helpers\HtmlPurifier;
use craft\models\FolderCriteria;
use craft\models\VolumeFolder;

/**
 * Class FieldVolume
 */
class FieldService extends Component
{
    /**
     * @var \craft\elements\Entry|\craft\elements\User|null
     */
    protected $element;

    /**
     * @var \craft\base\VolumeInterface
     */
    private $volume;

    /**
     * @param \craft\elements\Entry|\craft\elements\User $element
     */
    public function setElement($element)
    {
        $this->element = $element;
    }

    /**
     * Get available transforms.
     *
     * @return array
     */
    public function getTransforms()
    {
        $allTransforms = \Craft::$app->getAssetTransforms()->getAllTransforms();
        $transformList = [];

        foreach ($allTransforms as $transform) {
            $transformList[] = [
                'handle' => Html::encode($transform->handle),
                'name' => Html::encode($transform->name)
            ];
        }

        return $transformList;
    }

    /**
     * Returns the folder-id to store uploads in
     *
     * @param int $volumeId
     * @param string $subPath
     * @param bool $createDynamicFolders
     *
     * @return string|null
     * @throws \Exception
     */
    public function determineFolderId($volumeId, $subPath, $createDynamicFolders = true)
    {
        try {

            if (!is_numeric($volumeId)) {
                $volumeId = (integer) ltrim($volumeId, 'folder:');
            }

            $this->volume = \Craft::$app->getVolumes()->getVolumeById($volumeId);
            $folder = $this->resolveSourcePathToFolderId($volumeId, $subPath, $createDynamicFolders);

        } catch (InvalidSubpathException $e) {

            // If this is a new element, the sub path probably just contained a token that returned null, like {id}
            // so use the user's upload folder instead
            if (empty($this->element->id) || !$createDynamicFolders) {

                $userModel = \Craft::$app->getUser()->getIdentity();
                $userFolder = \Craft::$app->getAssets()->getUserTemporaryUploadFolder($userModel);
                $folderName = 'field_' . $this->element->id;

                $folder = \Craft::$app->getAssets()->findFolder([
                    'parentId' => $userFolder->id,
                    'name'     => $folderName,
                ]);

                if (empty($folder)) {
                    $folder = $this->_createSubFolder($userFolder, $folderName);
                }

            } else {
                // Existing element, so this is just a bad subpath
                throw $e;
            }
        }

        if (!empty($folder)) {

            return $this->getFolderParentsById($folder);
        }

        return null;
    }

    /**
     * @param VolumeFolder $folder
     * @return string
     */
    private function getFolderParentsById($folder)
    {
        $tree = [];

        // when reached folder already is the root folder
        if (empty($folder->parentId)) {
            $tree[] = 'folder:' . $folder->id;
        } else {

            $rootFolder = \Craft::$app->getAssets()->getRootFolderByVolumeId($folder->volumeId);
            $tree[] = 'folder:' . $rootFolder->id;

            $folderTree = \Craft::$app->getAssets()->getFolderTreeByFolderId($folder->id);
            foreach ($folderTree as $folder) {

                $tree[] = 'folder:' . $folder->id;
            }
        }

        // return folder tree like 'folder:1/folder:2/folder:3' etc.
        return implode('/', $tree);
    }

    /**
     * @param int $volumeId
     * @param string $subPath
     * @param bool $createDynamicFolders
     *
     * @return VolumeFolder
     *
     * @throws InvalidSubpathException
     * @throws InvalidVolumeException
     * @throws \Exception
     */
    private function resolveSourcePathToFolderId($volumeId, $subPath, $createDynamicFolders = true)
    {
        $rootFolder = \Craft::$app->getAssets()->getRootFolderByVolumeId($volumeId);

        // Make sure the root folder actually exists
        if (!$rootFolder) {
            throw new InvalidVolumeException();
        }

        // Are we looking for a sub folder?
        $subPath = is_string($subPath) ? trim($subPath, '/') : '';

        if (strlen($subPath) === 0) {
            $folder = $rootFolder;
        } else {

            // Prepare the path by parsing tokens and normalizing slashes.
            try {
                $renderedSubPath = \Craft::$app->getView()->renderObjectTemplate($subPath, $this->element);
            } catch (\Exception $e) {
                throw new InvalidSubpathException($subPath);
            }

            // Did any of the tokens return null?
            if (strlen($renderedSubPath) === 0 ||
                trim($renderedSubPath, '/') != $renderedSubPath ||
                strpos($renderedSubPath, '//') !== false
            ) {
                throw new InvalidSubpathException($subPath);
            }

            $subPath = $renderedSubPath;
            if (\Craft::$app->getConfig()->getGeneral()->convertFilenamesToAscii) {
                $subPath = HtmlPurifier::cleanUtf8($renderedSubPath);
            }

            $criteria = new FolderCriteria();
            $criteria->parentId = $rootFolder->id;
            $criteria->name = $subPath;

            $folder = \Craft::$app->getAssets()->findFolder($criteria);

            // Ensure that the folder exists
            if (!$folder) {
                if (!$createDynamicFolders) {
                    throw new InvalidSubpathException($subPath);
                }

                // Start at the root, and, go over each folder in the path and create it if it's missing.
                $parentFolder = $rootFolder;
                $segments = explode('/', $subPath);

                foreach ($segments as $segment) {

                    $criteria = new FolderCriteria();
                    $criteria->parentId = $parentFolder->id;
                    $criteria->name = $segment;

                    $folder = \Craft::$app->getAssets()->findFolder($criteria);

                    // Create it if it doesn't exist
                    if (!$folder) {
                        $folder = $this->_createSubFolder($parentFolder, $segment);
                        $folder = \Craft::$app->getAssets()->getFolderById($folder->id);
                    }

                    // In case there's another segment after this...
                    $parentFolder = $folder;
                }
            }
        }

        return $folder;
    }

    /**
     * @param VolumeFolder $currentFolder
     * @param string $folderName
     * @return VolumeFolder
     * @throws \Exception
     */
    private function _createSubFolder(VolumeFolder $currentFolder, $folderName)
    {
        $folderPath = $currentFolder->path . $folderName . '/';
        $newFolder = new VolumeFolder([
            'name'     => $folderName,
            'parentId' => $currentFolder->id,
            'volumeId' => $this->volume->id,
            'path'     => $folderPath,
        ]);

        \Craft::$app->getAssets()->createFolder($newFolder);

        return $newFolder;
    }
}