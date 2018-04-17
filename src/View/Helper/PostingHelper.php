<?php
/**
 * Saito - The Threaded Web Forum
 *
 * @copyright Copyright (c) the Saito Project Developers 2015
 * @link https://github.com/Schlaefer/Saito
 * @license http://opensource.org/licenses/MIT
 */

namespace App\View\Helper;

use App\Model\Entity\Entry;
use Cake\Core\Configure;
use Saito\Posting\Basic\BasicPostingInterface;
use Saito\Posting\PostingInterface;
use Saito\Posting\Renderer\HelperTrait;
use Saito\Thread\Renderer;

/**
 * Class PostingHelper
 *
 * @package App\View\Helper
 */
class PostingHelper extends AppHelper
{

    use HelperTrait;

    public $helpers = ['Form', 'Html', 'TimeH'];

    /**
     * @var array perf-cheat for renderers
     */
    protected $_renderers = [];

    /**
     * get paginated index
     *
     * @param int $tid tid
     * @param null|string $lastAction last action
     * @return string
     */
    public function getPaginatedIndexPageId(int $tid, ?string $lastAction = null): string
    {
        $params = [];
        if ($lastAction !== 'add') {
            $session = $this->request->getSession();
            if ($session->read('paginator.lastPage')) {
                $params[] = 'page=' . $session->read('paginator.lastPage');
            }
        }
        $params[] = 'jump=' . $tid;

        return '/?' . implode('&', $params);
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
        $webroot = $this->request->getAttribute('webroot');
        $url = "{$webroot}entries/view/{$id}";
        $link = "<a href=\"{$url}\" class=\"{$options['class']}\">" . $this->getSubject($posting) . '</a>';

        return $link;
    }

    /**
     * category select
     *
     * @param Entry $posting posting
     * @param array $categories categories
     *
     * @return string
     */
    public function categorySelect(Entry $posting, array $categories)
    {
        if ($posting->isRoot()) {
            $html = $this->Form->control(
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
     * Render view counter
     *
     * @param BasicPostingInterface $posting posting
     *
     * @return string
     */
    public function views(BasicPostingInterface $posting)
    {
        return __('views_headline') . ': ' . $posting->get('views');
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
                case 'thread':
                    $renderer = new Renderer\ThreadHtmlRenderer($this);
                    break;
                case (is_string($renderer)):
                    $renderer = new $renderer($this);
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
