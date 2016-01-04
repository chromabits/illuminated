<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Support\Html;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\ListItem;
use Chromabits\Nucleus\View\Common\UnorderedList;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Pagination\UrlWindow;
use Chromabits\Nucleus\View\Node;

/**
 * Class BootstrapFourPaginatorPresenter.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class BootstrapFourPaginatorPresenter extends BaseObject implements
    RenderableInterface
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var array
     */
    protected $window;

    /**
     * Construct an instance of a BootstrapFourPaginatorPresenter.
     *
     * @param Paginator $paginator
     * @param UrlWindow $window
     */
    public function __construct(Paginator $paginator, UrlWindow $window = null)
    {
        parent::__construct();

        $this->paginator = $paginator;
        $this->window = Std::coalesce($window, new UrlWindow($paginator))
            ->get();
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        if ($this->paginator->hasPages()) {
            return new Node('nav', ['class' => 'text-xs-center'], [
                new UnorderedList(['class' => 'pagination'], $this->getLinks())
            ]);
        }

        return new Node('nav', [], []);
    }

    /**
     * @param string $text
     *
     * @return ListItem
     */
    protected function renderPreviousButton($text = null)
    {
        $content = Std::coalesce($text, Html::safe('&laquo;'));

        if ($this->paginator->currentPage() <= 1) {
            return $this->getDisabledPageWrapper($content);
        }

        return $this->getPageWrapper(
            $content,
            $this->paginator->url(
                $this->paginator->currentPage() - 1
            )
        );
    }

    /**
     * @param string $text
     *
     * @return ListItem
     */
    protected function renderNextButton($text = null)
    {
        $content = Std::coalesce($text, Html::safe('&raquo;'));

        if (!$this->paginator->hasMorePages()) {
            return $this->getDisabledPageWrapper($content);
        }

        return $this->getPageWrapper(
            $content,
            $this->paginator->url(
                $this->paginator->currentPage() + 1
            )
        );
    }

    /**
     * @param mixed $content
     * @param string $url
     *
     * @return ListItem
     */
    protected function getPageWrapper($content, $url)
    {
        return new ListItem(
            ['class' => 'page-item'],
            new Anchor(
                [
                    'href' => $url,
                    'class' => 'page-link',
                ],
                $content
            )
        );
    }

    /**
     * @param mixed $content
     *
     * @return ListItem
     */
    protected function getDisabledPageWrapper($content)
    {
        return new ListItem(
            ['class' => 'page-item disabled'],
            new Anchor(
                [
                    'href' => '#',
                    'class' => 'page-link',
                ],
                $content
            )
        );
    }

    /**
     * @param mixed $content
     *
     * @return ListItem
     */
    protected function getActivePageWrapper($content)
    {
        return new ListItem(
            ['class' => 'page-item active'],
            new Anchor(
                [
                    'href' => '#',
                    'class' => 'page-link',
                ],
                $content
            )
        );
    }

    /**
     * @return array
     */
    protected function getLinks()
    {
        $links = [
            $this->renderPreviousButton(),
        ];

        if (is_array($this->window['first'])) {
            foreach ($this->window['first'] as $page => $url) {
                if ($this->paginator->currentPage() == $page) {
                    $links[] = $this->getActivePageWrapper(
                        (string) $page,
                        $url
                    );

                    continue;
                }

                $links[] = $this->getPageWrapper((string) $page, $url);
            }
        }

        if (is_array($this->window['slider'])) {
            $links[] = $this->getDisabledPageWrapper('...');
            foreach ($this->window['slider'] as $page => $url) {
                if ($this->paginator->currentPage() == $page) {
                    $links[] = $this->getActivePageWrapper(
                        (string) $page,
                        $url
                    );

                    continue;
                }

                $links[] = $this->getPageWrapper((string) $page, $url);
            }
        }

        if (is_array($this->window['last'])) {
            $links[] = $this->getDisabledPageWrapper('...');
            foreach ($this->window['last'] as $page => $url) {
                if ($this->paginator->currentPage() == $page) {
                    $links[] = $this->getActivePageWrapper(
                        (string) $page,
                        $url
                    );

                    continue;
                }

                $links[] = $this->getPageWrapper((string) $page, $url);
            }
        }

        $links[] = $this->renderNextButton();

        return $links;
    }
}