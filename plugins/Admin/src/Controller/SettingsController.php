<?php

namespace Admin\Controller;

use Cake\Http\Exception\NotFoundException;

class SettingsController extends AdminAppController
{

    public $name = 'Settings';

    public $helpers = [
        'Setting',
        'TimeH'
    ];

    protected $settingsShownInAdminIndex = [
        'api_crossdomain' => 1,
        'api_enabled' => 1,
        'autolink' => 1,
        'bbcode_img' => 1,
        'block_user_ui' => 1,
        // Activates and deactivates the category-chooser on entries/index
        'category_chooser_global' => 1,
        // Allows users to show the category-chooser even if the default
        // setting `category_chooser_global` is off
        'category_chooser_user_override' => 1,
        'edit_delay' => 1,
        'edit_period' => 1,
        'embedly_enabled' => 1,
        'embedly_key' => 1,
        'email_contact' => 1,
        'email_register' => 1,
        'email_system' => 1,
        'forum_disabled' => 1,
        'forum_disabled_text' => 1,
        'forum_email' => 1,
        'forum_name' => 1,
        'quote_symbol' => 1,
        'smilies' => 1,
        'signature_separator' => 1,
        'stopwatch_get' => 1,
        'store_ip' => 1,
        'store_ip_anonymized' => 1,
        'subject_maxlength' => 1,
        'text_word_maxlength' => 1,
        'thread_depth_indent' => 1,
        'timezone' => 1,
        'topics_per_page' => 1,
        'tos_enabled' => 1,
        'tos_url' => 1,
        'upload_max_img_size' => 1,
        'upload_max_number_of_uploads' => 1,
        'video_domains_allowed' => 1,
    ];

    /**
     * index settings
     *
     * @return void
     */
    public function index()
    {
        $settings = $this->Settings->getSettings();
        foreach ($settings as $key => $value) {
            $this->request = $this->request->withData($key, $value);
        }
        $settings = array_intersect_key($settings, $this->settingsShownInAdminIndex);
        $this->set('Settings', $settings);
    }

    /**
     * edit setting
     *
     * @param null $id settings-ID
     *
     * @return \Cake\Network\Response|void
     */
    public function edit($id = null)
    {
        if (!$id) {
            throw new NotFoundException;
        }

        $setting = $this->Settings->get($id);
        if (empty($setting)) {
            throw new NotFoundException;
        }

        if ($this->request->is(['post', 'put'])) {
            $this->Settings->patchEntity(
                $setting,
                $this->request->getData(),
                ['fields' => 'value']
            );
            if ($this->Settings->save($setting)) {
                $this->Flash->set('Saved. @lo', ['element' => 'notice']);

                return $this->redirect(['action' => 'index', '#' => $id]);
            }
            $this->Flash->set('Something went wrong @lo', ['element' => 'error']);
        }

        $this->set('setting', $setting);

        if ($id === 'timezone') {
            $this->render('timezone');
        }
    }
}
