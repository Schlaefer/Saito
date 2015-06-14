<?php

namespace Saito\User\Upload;

use Cake\Event\Event;
use Cake\Event\EventListenerInterface;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;
use Cake\Utility\Text;
use Proffer\Lib\ProfferPath;

class AvatarFilenameListener implements EventListenerInterface
{
    /**
     * {@inheritDoc}
     */
    public function implementedEvents()
    {
        return [
            'Proffer.afterPath' => 'change',
        ];
    }

    /**
     * Rename a file and change it's upload folder before it's processed
     *
     * @param Event $event The event class with a subject of the entity
     * @param ProfferPath $path path
     * @return ProfferPath $path
     */
    public function change(Event $event, ProfferPath $path)
    {
        $user = $event->subject();

        $this->deleteExisting($user);

        // Detect and select the right file extension
        switch ($event->subject()->get('avatar')['type']) {
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

        // If a seed is set in the data already, we'll use that rather than make a new one each time we upload
        $imgDir = $user->get('image_dir');
        if (empty($imgDir)) {
            $path->setSeed($user->get('id'));
        }

        // Change the filename in both the path to be saved, and in the entity data for saving to the db
        $path->setFilename($newFilename);
        $user['avatar']['name'] = $newFilename;

        // Must return the modified path instance, so that things are saved in the right place
        return $path;
    }

    /**
     * Delete existing avatar.
     *
     * @param Entity $user user
     * @return void
     */
    public function deleteExisting($user)
    {
        if ($user->dirty('avatar')) {
            $dir = $user->get('avatar_dir');
            if (!empty($dir)) {
                $folder = new Folder(
                    WWW_ROOT . 'useruploads/users/avatar/' . $dir
                );
                $folder->delete();
            }
        }
    }
}
