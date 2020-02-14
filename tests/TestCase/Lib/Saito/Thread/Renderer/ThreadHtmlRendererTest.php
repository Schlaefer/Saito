<?php
declare(strict_types=1);

namespace Saito\Test\Thread\Renderer;

use App\View\Helper\PostingHelper;
use Cake\I18n\Time;
use Cake\View\View;
use Saito\Posting\Posting;
use Saito\Test\SaitoTestCase;
use Saito\Thread\Renderer\ThreadHtmlRenderer;
use Saito\User\CurrentUser\CurrentUserFactory;

class ThreadHtmlRendererTest extends SaitoTestCase
{
    /**
     * tests that posting of ignored user is/not ignored
     */
    public function testIgnore()
    {
        $entry = [
            'id' => 1,
            'tid' => 0,
            'pid' => 0,
            'subject' => 'a',
            'text' => 'b',
            'time' => new Time(),
            'last_answer' => new Time(),
            'fixed' => false,
            'solves' => '',
            'user_id' => 1,
            'category' => [
                'id' => 1,
                'accession' => 0,
                'description' => 'd',
                'category' => 'c',
            ],
            'user' => ['id' => 1, 'username' => 'u'],
        ];

        $entries = $this->getMockBuilder('\Saito\Posting\Posting')
            ->setConstructorArgs([$entry])
            ->setMethods(['isIgnored'])
            ->getMock();
        $entries->withCurrentUser($this->SaitoUser);
        $entries->expects($this->once())
            ->method('isIgnored')
            ->will($this->returnValue(true));

        $xPathQuery = '//ul[@data-id=1]/li[contains(@class,"ignored")]';

        //= posting should be ignored
        $options = ['maxThreadDepthIndent' => 25];
        $renderer = new ThreadHtmlRenderer($this->PostingHelper, $options);
        $result = $renderer->render($entries);
        $this->assertXPath($result, $xPathQuery);

        //= posting should not ignored with 'ignore' => false flag set
        $options['ignore'] = false;
        $renderer->setOptions($options);
        $result = $renderer->render($entries);
        $this->assertNotXPath($result, $xPathQuery);
    }

    public function testNesting()
    {
        $entry = $entry1 = $entry2 = $entry3 = [
            'id' => 1,
            'tid' => 0,
            'pid' => 0,
            'subject' => 'a',
            'text' => 'b',
            'time' => new Time(),
            'last_answer' => new Time(),
            'fixed' => false,
            'solves' => '',
            'user_id' => 1,
            'category' => [
                'id' => 1,
                'accession' => 0,
                'description' => 'd',
                'category' => 'c',
            ],
            'user' => ['username' => 'u'],
        ];

        $entry1['subject'] = 'b';
        $entry2['subject'] = 'c';
        $entry3['subject'] = 'd';

        // root + 2 sublevels
        $entries = $entry;
        $entries['_children'] = [$entry1 + ['_children' => [$entry2]], $entry3];

        $entries = (new Posting($entries))->withCurrentUser($this->SaitoUser);

        $renderer = new ThreadHtmlRenderer(
            $this->PostingHelper,
            ['maxThreadDepthIndent' => 9999]
        );
        $result = $renderer->render($entries);

        $this->assertXPath($result, '//ul[@data-id=1]/li', 2);
        $this->assertXPath($result, '//ul[@data-id=1]/li/ul/li', 3);
        $this->assertXPath($result, '//ul[@data-id=1]/li/ul/li/ul/li');
    }

    public function testThreadMaxDepth()
    {
        $SaitoUser = $this->getMockBuilder('SaitoUser')
            ->setMethods(['getId', 'hasBookmarks'])
            ->getMock();
        $SaitoUser->Postings = $this->createMock(
            '\Saito\User\ReadPostings\ReadPostingsDummy'
        );

        $entry = [
            'id' => 1,
            'pid' => 0,
            'tid' => 0,
            'subject' => 'a',
            'text' => 'b',
            'time' => new Time(),
            'last_answer' => new Time(),
            'fixed' => false,
            'solves' => '',
            'user_id' => 1,
            'category' => [
                'id' => 1,
                'accession' => 0,
                'description' => 'd',
                'category' => 'c',
            ],
            'user' => ['username' => 'u'],
        ];

        // root + 2 sublevels
        $entries = $entry;
        $entries['_children'] = [
            $entry + [
                '_children' => [
                    $entry,
                ],
            ],
        ];

        $entries = (new Posting($entries))->withCurrentUser($this->SaitoUser);

        // max depth should not apply
        $renderer = new ThreadHtmlRenderer(
            $this->PostingHelper,
            ['maxThreadDepthIndent' => 9999]
        );
        $result = $renderer->render($entries);
        $this->assertEquals(substr_count($result, '<ul'), 3);

        // max depth should only allow 1 level
        $renderer->setOptions(['maxThreadDepthIndent' => 2]);
        $result = $renderer->render($entries);
        $this->assertEquals(substr_count($result, '<ul'), 2);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->PostingHelper = $this->_setupPostingHelper();

        $this->SaitoUser = CurrentUserFactory::createDummy();
    }

    protected function _setupPostingHelper()
    {
        $View = new View();

        return new PostingHelper($View);
    }
}
