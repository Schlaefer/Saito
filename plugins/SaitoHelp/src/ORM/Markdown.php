<?php

namespace SaitoHelp\ORM;

use Cake\Core\Plugin;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Entity;

class Markdown implements RepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function alias($alias = null)
    {
        // @td
    }

    /**
     * {@inheritDoc}
     */
    public function hasField($field)
    {
        // @td
    }

    /**
     * {@inheritDoc}
     */
    public function find($type = 'all', $options = [])
    {
        $options['conditions'] += ['language' => 'en'];

        $id = $options['conditions']['id'];
        $lang = $options['conditions']['language'];

        $findFiles = function ($id, $lang) {
            list($plugin, $id) = pluginSplit($id);
            if ($plugin) {
                $folderPath = Plugin::path($plugin);
            } else {
                $folderPath = ROOT . DS;
            }
            $folderPath .= 'docs' . DS . 'help' . DS . $lang;

            $folder = new Folder($folderPath);
            $files = $folder->find("$id(-.*?)?\.md");
            return [$files, $folderPath];
        };

        list($files, $folderPath) = $findFiles($id, $lang);

        if (empty($files)) {
            list($lang) = explode('_', $lang);
            list($files, $folderPath) = $findFiles($id, $lang);
        }

        if (!$files) {
            return false;
        }
        $name = $files[0];
        $file = new File($folderPath . DS . $name, false, 0444);
        $text = $file->read();
        $file->close();
        $data = [
            'file' => $name,
            'id' => $id,
            'lang' => $lang,
            'text' => $text
        ];
        $result = new Entity($data);
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function get($primaryKey, $options = [])
    {
        // @td: Implement get() method.
    }

    /**
     * {@inheritDoc}
     */
    public function query()
    {
        // @td: Implement query() method.
    }

    /**
     * {@inheritDoc}
     */
    public function updateAll($fields, $conditions)
    {
        // @td Implement updateAll() method.
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAll($conditions)
    {
        // @td Implement deleteAll() method.
    }

    /**
     * {@inheritDoc}
     */
    public function exists($conditions)
    {
        // @td
    }

    /**
     * {@inheritDoc}
     */
    public function save(EntityInterface $entity, $options = [])
    {
        // @td Implement save() method.
    }

    /**
     * {@inheritDoc}
     */
    public function delete(EntityInterface $entity, $options = [])
    {
        // @td Implement delete() method.
    }

    /**
     * {@inheritDoc}
     */
    public function newEntity($data = null, array $options = [])
    {
        // @td Implement newEntity() method.
    }

    /**
     * {@inheritDoc}
     */
    public function newEntities(array $data, array $options = [])
    {
        // @td Implement newEntities() method.
    }

    /**
     * {@inheritDoc}
     */
    public function patchEntity(
        EntityInterface $entity,
        array $data,
        array $options = []
    ) {
        // @td Implement patchEntity() method.
    }

    /**
     * {@inheritDoc}
     */
    public function patchEntities($entities, array $data, array $options = [])
    {
        // @td Implement patchEntities() method.
    }
}
