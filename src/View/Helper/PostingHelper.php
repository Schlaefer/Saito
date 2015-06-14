<?php

namespace App\View\Helper;

use Cake\Core\Configure;
use Cake\ORM\Entity;
use Saito\App\Registry;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\Posting;
use Saito\Posting\PostingInterface;
use Saito\Thread\Renderer;

class PostingHelper extends AppHelper
{

    use \Saito\Posting\Renderer\HelperTrait;

    public $helpers = ['Form', 'Html', 'TimeH'];

    /**
     * @var array perf-cheat for renderers
     */
    protected $_renderers = [];

    /**
     * get paginated index
     *
     * @param int $tid tid
     * @param string $lastAction last action
     * @return string
     */
    public function getPaginatedIndexPageId($tid, $lastAction)
    {
        $indexPage = '/entries/index';

        if ($lastAction !== 'add') {
            $session = $this->request->session();
            if ($session->read('paginator.lastPage')) {
                $indexPage .= '/page:' . $session->read('paginator.lastPage');
            }
        }
        $indexPage .= '/jump:' . $tid;

        return $indexPage;
    }

    /**
     * Get fast link for posting.
     *
     * @param BasicPostingInterface $posting posting
     * @param array $options options
     * @return string HTML
     */
    public function getFastLink(BasicPostingInterface $posting, array $options = [])
    {
        $options += ['class' => ''];
        $id = $posting->get('id');
        $out = "<a href='{$this->request->webroot}entries/view/{$id}' class='{$options['class']}'>" . $this->getSubject($posting->toPosting()) . '</a>';
        return $out;
    }

    /**
     * category select
     *
     * @param Entity $posting posting
     * @param array $categories categories
     * @return string
     */
    public function categorySelect(Entity $posting, array $categories)
    {
        if ($posting->isRoot()) {
            $html = $this->Form->input(
                'category_id',
                [
                    'options' => $categories,
                    'empty' => true,
                    'label' => __('Category'),
                    'tabindex' => 1,
                    'error' => ['notEmpty' => __('error_category_empty')]
                ]
            );
        } else {
            // Send category for easy access in entries/preview when answering
            // (not used when saved).
            $html = $this->Form->hidden('category_id');
        }
        return $html;
    }

    /**
     * renders a posting tree as thread
     *
     * @param PostingInterface $tree passed as reference to share CU-decorator "up"
     * @param array $options options
     *    - 'renderer' [thread]|mix
     * @return string
     * @internal param CurrentUser $CurrentUser current user
     */
    public function renderThread(PostingInterface $tree, array $options = [])
    {
        $options += [
            'lineCache' => $this->_View->get('LineCache'),
            'maxThreadDepthIndent' => (int)Configure::read(
                'Saito.Settings.thread_depth_indent'
            ),
            'renderer' => 'thread',
            'rootWrap' => false
        ];
        $renderer = $options['renderer'];
        unset($options['renderer']);

        if (isset($this->_renderers[$renderer])) {
            $renderer = $this->_renderers[$renderer];
        } else {
            $name = $renderer;
            switch ($name) {
                case 'mix':
                    $renderer = new Renderer\MixHtmlRenderer($this);
                    break;
                default:
                    $renderer = new Renderer\ThreadHtmlRenderer($this);
            }
            $this->_renderers[$name] = $renderer;
        }
        $renderer->setOptions($options);
        return $renderer->render($tree);
    }
}
