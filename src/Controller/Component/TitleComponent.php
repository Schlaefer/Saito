<?php

namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Text;
use Saito\Posting\Posting;

class TitleComponent extends Component
{
    /**
     * {@inheritDoc}
     */
    public function startup(Event $event)
    {
        $this->_Controller = $event->subject();
    }

    /**
     * {@inheritDoc}
     */
    public function beforeRender()
    {
        $this->_setLayoutTitles();
    }

    /**
     * sets layout/title/page vars
     *
     * - title_for_page: title for the page, maybe used on page for headers
     * - forum_name: forum name
     * - title_for_layout: title + forum name for HTML header tag
     *
     * @return void
     */
    protected function _setLayoutTitles()
    {
        //= page
        if (isset($this->_Controller->viewVars['title_for_page'])) {
            $page = $this->_Controller->viewVars['title_for_page'];
        } else {
            $controller = $this->_Controller->request->controller;
            $action = $this->_Controller->request->action;
            $key = lcfirst($controller) . '/' . $action;
            $page = __d('page_titles', $key);
            if ($key === $page) {
                $page = null;
            }
        }
        $this->_Controller->set('title_for_page', $page);

        //= forum
        $forum = Configure::read('Saito.Settings.forum_name');
        $this->_Controller->set('forum_name', $forum);

        //= layout
        if (isset($this->_Controller->viewVars['title_for_layout'])) {
            $layout = $this->_Controller->viewVars['title_for_layout'];
        } else {
            $layout = $page;
        }
        if ($layout) {
            $layout = Text::insert(
                __('forum-title-template'),
                ['page' => $layout, 'forum' => $forum]
            );
        } else {
            $layout = $forum;
        }
        $this->_Controller->set('title_for_layout', $layout);
    }

    /**
     * Set title
     *
     * @param Posting $posting posting
     * @param string $type type
     * @return void
     */
    public function setFromPosting(Posting $posting, $type = null)
    {
        if ($type === null) {
            $template = __(':subject | :category');
        } else {
            $template = __(':subject (:type) | :category');
        }
        $this->_Controller->set(
            'title_for_layout',
            Text::insert(
                $template,
                [
                    'category' => $posting->get('category')['category'],
                    'subject' => $posting->get('subject'),
                    'type' => $type
                ]
            )
        );
    }
}
