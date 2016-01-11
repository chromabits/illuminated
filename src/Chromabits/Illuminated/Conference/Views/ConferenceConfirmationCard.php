<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Chromabits\Nucleus\Support\Std;
use Chromabits\Nucleus\View\BaseHtmlRenderable;
use Chromabits\Nucleus\View\Bootstrap\Card;
use Chromabits\Nucleus\View\Bootstrap\CardBlock;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Common\Anchor;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\HeaderFour;
use Chromabits\Nucleus\View\Common\Paragraph;
use GuzzleHttp\Query;
use GuzzleHttp\Url;

/**
 * Class ConferenceConfirmationCard
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class ConferenceConfirmationCard extends BaseHtmlRenderable
{
    /**
     * @var mixed
     */
    protected $content;

    /**
     * @var string
     */
    protected $cancelUri;

    /**
     * @var Url
     */
    protected $confirmUri;

    /**
     * @var string
     */
    protected $actionDescription;

    /**
     * @var ConferenceContext
     */
    protected $context;

    /**
     * Construct an instance of a ConferenceConfirmationCard.
     *
     * @param ConferenceContext $context
     */
    public function __construct(ConferenceContext $context)
    {
        parent::__construct();

        $this->content = null;
        $this->actionDescription = 'Action description not provided';
        $this->context = $context;

        $request = $context->getRequest();
        $query = new Query($request->query->all());

        $query->set('confirm', 'true');

        $this->confirmUri = Url::fromString($request->getUri());
        $this->confirmUri->setQuery($query);

        $this->cancelUri = $context->lastUrl();
    }

    /**
     * Get this object with some content.
     *
     * @param mixed $content
     *
     * @return ConferenceConfirmationCard
     */
    public function withContent($content)
    {
        $copy = clone $this;

        $copy->content = $content;

        return $copy;
    }

    /**
     * Get this object with some description.
     *
     * @param string $description
     *
     * @return ConferenceConfirmationCard
     */
    public function withDescription($description)
    {
        $copy = clone $this;

        $copy->actionDescription = $description;

        return $copy;
    }

    /**
     * Get this object with a specific confirm URI.
     *
     * @param string $uri
     *
     * @return ConferenceConfirmationCard
     */
    public function withConfirmUri($uri)
    {
        $copy = clone $this;

        $copy->confirmUri = $uri;

        return $copy;
    }

    /**
     * Get this object with a specific cancel URI.
     *
     * @param string $uri
     *
     * @return ConferenceConfirmationCard
     */
    public function withCancelUri($uri)
    {
        $copy = clone $this;

        $copy->cancelUri = $uri;

        return $copy;
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        $content = Std::coalesce(
            $this->content,
            [
                new HeaderFour([], 'Confirmation required'),
                new Paragraph([], [
                    'The action you are about to perform is sort of ',
                    'important. Take some time to ponder on it:',
                ]),
                new Card(
                    ['class' => 'card text-warning'],
                    new CardBlock([], $this->actionDescription)
                )
            ]
        );

        return new Card([], [
            new CardBlock([], $content),
            new Div(['class' => 'card-footer'], [
                new Div(['class' => 'btn-toolbar pull-xs-right'], [
                    new Div(['class' => 'btn-group'], [
                        new Anchor(
                            [
                                'href' => $this->cancelUri,
                                'class' => 'btn btn-secondary'
                            ],
                            'Cancel'
                        ),
                    ]),
                    new Div(['class' => 'btn-group'], [
                        new Anchor(
                            [
                                'href' => $this->confirmUri,
                                'class' => 'btn btn-primary'
                            ],
                            'Confirm'
                        ),
                    ]),
                ]),
                new Row([], []),
            ]),
        ]);
    }
}
