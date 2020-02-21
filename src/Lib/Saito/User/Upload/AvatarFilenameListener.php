<?php
declare(strict_types=1);

/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace Saito\User\Upload;

use App\Model\Entity\User;
use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Filesystem\Folder;
use Cake\Utility\Text;
use Proffer\Lib\ProfferPath;

/**
 * Handles files for Avatars-Images
 */
class AvatarFilenameListener implements EventListenerInterface
{
    /**
     * Upload root directory
     *
     * @var string
     */
    private $rootDirectory;

    /**
     * Constructor
     *
     * @param string $rootDirectory the upload root
     */
    public function __construct(string $rootDirectory)
    {
        $this->rootDirectory = $rootDirectory;
    }

    /**
     * {@inheritDoc}
     */
    public function implementedEvents(): array
    {
        return [
            'Proffer.afterPath' => 'change',
            'Model.afterSaveCommit' => 'onModelAfterSaveCommit',
        ];
    }

    /**
     * Rename a file and change it's upload folder before it's processed
     *
     * @param \Cake\Event\Event $event The event class with a subject of the entity
     * @param \Proffer\Lib\ProfferPath $path path
     * @return \Proffer\Lib\ProfferPath $path
     */
    public function change(Event $event, ProfferPath $path)
    {
        /** @var \App\Model\Entity\User $user */
        $user = $event->getSubject();

        if ($user->isDirty('avatar')) {
            $this->deleteExistingFilesForUser($user);
        }

        /** @var \Psr\Http\Message\UploadedFileInterface $avatar */
        $avatar = $user->get('avatar');

        // Detect and select the right file extension
        switch ($avatar->getClientMediaType()) {
            default:
            case "image/jpeg":
                $ext = '.jpg';
                break;
            case "image/png":
                $ext = '.png';
                break;
        }

        // `webroot/files/<table>/<field>/<seed>/<file>`

        $newFilename = Text::uuid() . $ext;

        // $path->setTable('avatars');

        $imgDir = $user->get('avatar_dir') ?: $user->get('id');
        $path->setSeed($imgDir);

        // Change the filename in both the path to be saved, and in the entity data for saving to the db
        $path->setFilename($newFilename);

        // Must return the modified path instance, so that things are saved in the right place
        return $path;
    }

    /**
     * Handle delete files if avatar is unset in DB
     *
     * Proffer only handles deleting an image when DB row is deleting, but
     * we don't delete the user, only set the avatar image null.
     *
     * @param \Cake\Event\Event $event event
     * @return void
     */
    public function onModelAfterSaveCommit(Event $event): void
    {
        /** @var \App\Model\Entity\User $user */
        $user = $event->getData('entity');
        $avatar = $user->get('avatar');
        if (!$user->isDirty('avatar') || !empty($avatar)) {
            return;
        }
        $this->deleteExistingFilesForUser($user);
    }

    /**
     * Delete existing avatar images for a user
     *
     * @param \App\Model\Entity\User $user user
     * @return void
     */
    private function deleteExistingFilesForUser(User $user): void
    {
        $dir = $user->get('id');
        if (!empty($dir)) {
            $folder = new Folder(
                $this->rootDirectory . '/users/avatar/' . $dir
            );
            $folder->delete();
        }
    }
}
