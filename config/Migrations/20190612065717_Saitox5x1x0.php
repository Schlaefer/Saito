<?php
use Migrations\AbstractMigration;

class Saitox5x1x0 extends AbstractMigration
{
    public function up()
    {
        $this->table('bookmarks')
            ->changeColumn('comment', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->update();

        //// removed backwards compatibility for Saito 4
        $this->table('users')
            ->removeColumn('user_place_lat')
            ->removeColumn('user_place_lng')
            ->removeColumn('user_place_zoom')
            ->removeColumn('show_userlist')
            ->removeColumn('show_recentposts')
            ->removeColumn('show_recententries')
            ->removeColumn('show_shoutbox')
            ->update();

        if ($this->table('entries')->hasColumn('email_notify')) {
            $this->table('entries')
                ->removeColumn('email_notify')
                ->update();
        }

        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'map_api_key\')');
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'map_enabled\')');
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'shoutbox_enabled\')');
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'shoutbox_max_shouts\')');
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'embedly_key\')');
        $this->execute('DELETE FROM `settings` WHERE `name` IN (\'embedly_enabled\')');

        $this->table('esevents')->drop()->save();

        $this->table('esnotifications')->drop()->save();

        $this->table('shouts')->drop()->save();

        //// Migrate tables to InnoDB
        foreach (['bookmarks', 'categories', 'settings', 'smiley_codes', 'smilies'] as $table) {
            $this->execute('ALTER TABLE ' . $table . ' ENGINE=InnoDB');
        }
    }

    public function down()
    {
        $this->table('bookmarks')
            ->changeColumn('comment', 'string', [
                'default' => null,
                'length' => 255,
                'null' => false,
            ])
            ->update();

        $this->table('entries')
            ->addColumn('email_notify', 'boolean', [
                'after' => 'text',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->update();

        $this->table('users')
            ->addColumn('user_place_lat', 'float', [
                'after' => 'user_place',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('user_place_lng', 'float', [
                'after' => 'user_place_lat',
                'default' => null,
                'length' => null,
                'null' => true,
            ])
            ->addColumn('user_place_zoom', 'integer', [
                'after' => 'user_place_lng',
                'default' => null,
                'length' => 4,
                'null' => true,
            ])
            ->addColumn('show_userlist', 'boolean', [
                'after' => 'slidetab_order',
                'comment' => 'stores if userlist is shown in front layout',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->addColumn('show_recentposts', 'boolean', [
                'after' => 'show_userlist',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->addColumn('show_recententries', 'boolean', [
                'after' => 'show_recentposts',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->addColumn('show_shoutbox', 'boolean', [
                'after' => 'show_recententries',
                'default' => '0',
                'length' => null,
                'null' => false,
            ])
            ->update();

        $this->table('esevents')
            ->addColumn('subject', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('event', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'subject',
                    'event',
                ]
            )
            ->create();

        $this->table('esnotifications')
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('esevent_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('esreceiver_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('deactivate', 'integer', [
                'default' => null,
                'limit' => 8,
                'null' => false,
            ])
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                [
                    'user_id',
                    'esreceiver_id',
                ]
            )
            ->addIndex(
                [
                    'esevent_id',
                    'esreceiver_id',
                    'user_id',
                ]
            )
            ->create();

        $this->table('shouts')
            ->addColumn('created', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('text', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => 11,
                'null' => false,
            ])
            ->addColumn('time', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        foreach (['bookmarks', 'categories', 'settings', 'smiley_codes', 'smilies'] as $table) {
            $this->execute('ALTER TABLE ' . $table . ' ENGINE=MyISAM');
        }
    }
}
