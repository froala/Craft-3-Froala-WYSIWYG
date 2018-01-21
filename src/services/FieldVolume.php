<?php

namespace froala\craftfroalawysiwyg\services;

use craft\base\Component;
use craft\errors\InvalidSubpathException;
use craft\errors\InvalidVolumeException;
use craft\helpers\HtmlPurifier;
use craft\models\VolumeFolder;

/**
 * Class FieldVolume
 */
class FieldVolume extends Component
{
    /**
     * @var \craft\elements\Entry|\craft\elements\User|null
     */
    protected $element;

    /**
     * @param \craft\elements\Entry|\craft\elements\User $element
     */
    public function setElement($element)
    {
        $this->element = $element;
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
        $folderId = null;

        try {

            if (!is_numeric($volumeId)) {
                $volumeId = (integer) ltrim($volumeId, 'folder:');
            }

            $folderId = $this->resolveSourcePathToFolderId($volumeId, $subPath, $createDynamicFolders);

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

                if ($folder) {
                    $folderId = $folder->id;
                } else {
                    $folderId = $this->_createSubFolder($userFolder, $folderName);
                }

            } else {
                // Existing element, so this is just a bad subpath
                throw $e;
            }
        }

        if (!empty($folderId)) {

            return 'folder:' . $folderId . ':single';
        }

        return $folderId;
    }

    /**
     * @param int $volumeId
     * @param string $subPath
     * @param bool $createDynamicFolders
     *
     * @return int|null
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

            $folder = \Craft::$app->getAssets()->findFolder([
                'parentId' => $rootFolder->id,
                'name'     => $subPath,
            ]);

            // Ensure that the folder exists
            if (!$folder) {
                if (!$createDynamicFolders) {
                    throw new InvalidSubpathException($subPath);
                }

                // Start at the root, and, go over each folder in the path and create it if it's missing.
                $parentFolder = $rootFolder;
                $segments = explode('/', $subPath);

                foreach ($segments as $segment) {
                    $folder = \Craft::$app->getAssets()->findFolder([
                        'parentId' => $parentFolder->id,
                        'name'     => $segment,
                    ]);

                    // Create it if it doesn't exist
                    if (!$folder) {
                        $folderId = $this->_createSubFolder($parentFolder, $segment);
                        $folder = \Craft::$app->getAssets()->getFolderById($folderId);
                    }

                    // In case there's another segment after this...
                    $parentFolder = $folder;
                }
            }
        }

        return $folder->id;
    }

    /**
     * @param VolumeFolder $currentFolder
     * @param string $folderName
     * @return integer
     * @throws \Exception
     */
    private function _createSubFolder(VolumeFolder $currentFolder, $folderName)
    {
        $newFolder = new VolumeFolder([
            'name'     => $folderName,
            'parentId' => $currentFolder->id,
            'volumeId' => $currentFolder->volumeId,
            'path'     => $currentFolder->path . $folderName . '/',
        ]);

        \Craft::$app->getAssets()->createFolder($newFolder);

        return $newFolder->id;
    }
}