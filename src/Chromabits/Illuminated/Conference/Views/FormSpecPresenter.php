<?php

namespace Chromabits\Illuminated\Conference\Views;

use Chromabits\Illuminated\Meditation\Constraints\DateTimeStringConstraint;
use Chromabits\Nucleus\Control\Maybe;
use Chromabits\Nucleus\Data\ArrayList;
use Chromabits\Nucleus\Data\ArrayMap;
use Chromabits\Nucleus\Exceptions\LackOfCoffeeException;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Meditation\Constraints\AbstractConstraint;
use Chromabits\Nucleus\Meditation\Constraints\BooleanConstraint;
use Chromabits\Nucleus\Meditation\Constraints\InArrayConstraint;
use Chromabits\Nucleus\Meditation\Constraints\NumericConstraint;
use Chromabits\Nucleus\Meditation\Constraints\PrimitiveTypeConstraint;
use Chromabits\Nucleus\Meditation\Exceptions\InvalidArgumentException;
use Chromabits\Nucleus\Meditation\FormSpec;
use Chromabits\Nucleus\Meditation\Primitives\ScalarTypes;
use Chromabits\Nucleus\Support\Html;
use Chromabits\Nucleus\View\Bootstrap\Row;
use Chromabits\Nucleus\View\Common\Button;
use Chromabits\Nucleus\View\Common\Div;
use Chromabits\Nucleus\View\Common\Form;
use Chromabits\Nucleus\View\Common\Input;
use Chromabits\Nucleus\View\Common\Option;
use Chromabits\Nucleus\View\Common\Paragraph;
use Chromabits\Nucleus\View\Common\Select;
use Chromabits\Nucleus\View\Common\Small;
use Chromabits\Nucleus\View\Common\TextArea;
use Chromabits\Nucleus\View\Interfaces\RenderableInterface;
use Chromabits\Nucleus\View\Interfaces\SafeHtmlProducerInterface;
use Chromabits\Nucleus\View\Node;
use Chromabits\Nucleus\View\SafeHtmlWrapper;

/**
 * Class FormSpecPresenter.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Views
 */
class FormSpecPresenter extends BaseObject implements
    RenderableInterface,
    SafeHtmlProducerInterface
{
    /**
     * @var FormSpec
     */
    protected $spec;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * Construct an instance of a FormSpecPresenter.
     *
     * @param FormSpec $spec
     * @param array $attributes
     *
     * @throws LackOfCoffeeException
     */
    public function __construct(FormSpec $spec, array $attributes = [])
    {
        parent::__construct();

        $this->spec = $spec;
        $this->attributes = $attributes;
    }

    /**
     * Render a single field into an input field.
     *
     * @param string $fieldName
     *
     * @return Input
     * @throws InvalidArgumentException
     */
    public function renderField($fieldName)
    {
        // First, attempt to render a select field if the spec mentions an
        // InArrayConstraint.
        $constraints = $this->spec
            ->getFieldConstraints($fieldName)
            ->find(function (AbstractConstraint $constraint) {
                return ($constraint instanceof InArrayConstraint
                    || $constraint instanceof BooleanConstraint
                    || $constraint instanceof NumericConstraint
                    || $constraint instanceof DateTimeStringConstraint
                );
            })
            ->bind(function (AbstractConstraint $constraint) use ($fieldName) {
                $attributes = ArrayMap::of([
                    'id' => $fieldName,
                    'class' => 'form-control',
                    'name' => $fieldName,
                ]);

                if ($constraint instanceof InArrayConstraint) {
                    return Maybe::just(new Select(
                        [
                            'id' => $fieldName,
                            'class' => 'c-select',
                            'name' => $fieldName,
                        ],
                        ArrayList::of($constraint->getAllowed())
                            ->map(function ($item) {
                                return new Option(
                                    ['value' => (string) $item],
                                    (string) $item
                                );
                            })
                    ));
                } elseif ($constraint instanceof BooleanConstraint) {
                    $attributes = $attributes
                        ->insert('type', 'checkbox')
                        ->insert('value', 'true');
                    $default = $this->spec->getFieldDefault($fieldName);

                    if ($default->isJust()) {
                        $attributes = $attributes->insert('checked', null);
                    }
                } elseif ($constraint instanceof NumericConstraint) {
                    $attributes = $attributes->insert('type', 'number');
                    $default = $this->spec->getFieldDefault($fieldName);

                    if ($default->isJust()) {
                        $attributes = $attributes->insert(
                            'value',
                            Maybe::fromJust($default)
                        );
                    }
                } elseif ($constraint instanceof DateTimeStringConstraint) {
                    $attributes = $attributes->insert('type', 'datetime');
                    $default = $this->spec->getFieldDefault($fieldName);

                    if ($default->isJust()) {
                        $attributes = $attributes->insert(
                            'value',
                            Maybe::fromJust($default)
                        );
                    }
                }

                return Maybe::just(new Input($attributes->toArray()));
            });

        if ($constraints->isJust()) {
            return Maybe::fromJust($constraints);
        }

        // Otherwise, we default to render the thing closes to the type of the
        // field.
        $type = $this->spec->getFieldType($fieldName);

        if ($type instanceof PrimitiveTypeConstraint) {
            $attributes = ArrayMap::of([
                'id' => $fieldName,
                'class' => 'form-control',
                'name' => $fieldName,
            ]);

            switch($type->toString()) {
                case ScalarTypes::SCALAR_STRING:
                    $attributes = $attributes->insert('type', 'text');
                    $default = $this->spec->getFieldDefault($fieldName);
                    $isTextArea = $this->spec->getFieldAnnotation(
                        $fieldName,
                        'textarea'
                    );

                    if ($default->isJust()) {
                        $attributes = $attributes->insert(
                            'value',
                            Maybe::fromJust($default)
                        );
                    }

                    if ($isTextArea->isJust()
                        && Maybe::fromJust($isTextArea) === true
                    ) {
                        return new TextArea(
                            $attributes
                                ->delete('value')
                                ->toArray(),
                            Maybe::fromMaybe('', $attributes->lookup('value'))
                        );
                    }

                    return new Input($attributes->toArray());
                case ScalarTypes::SCALAR_BOOLEAN:
                    $attributes = $attributes
                        ->insert('type', 'checkbox')
                        ->insert('value', 'true');
                    $default = $this->spec->getFieldDefault($fieldName);

                    if ($default->isJust()) {
                        $attributes = $attributes->insert('checked', null);
                    }

                    return new Input($attributes->toArray());
                case ScalarTypes::SCALAR_INTEGER:
                case ScalarTypes::SCALAR_FLOAT:
                    $attributes = $attributes->insert('type', 'number');
                    $default = $this->spec->getFieldDefault($fieldName);

                    if ($default->isJust()) {
                        $attributes = $attributes->insert(
                            'value',
                            Maybe::fromJust($default)
                        );
                    }

                    return new Input($attributes->toArray());
            }
        }

        // If it is not a primitive type, we bail and render a simple text
        // field.
        return new Input([
            'id' => $fieldName,
            'class' => 'form-control',
            'name' => $fieldName,
            'type' => 'text',
        ]);
    }

    /**
     * Render field and description.
     *
     * @param string $fieldName
     *
     * @return mixed
     * @throws InvalidArgumentException
     */
    protected function renderFullField($fieldName)
    {
        $fieldNodes = $this->renderField($fieldName);
        $fieldNotes = ArrayList::zero();
        $fieldRequired = $this->spec->getFieldRequired($fieldName);

        if (!is_array($fieldNodes)) {
            $nodes = ArrayList::of([$fieldNodes]);
        } else {
            $nodes = ArrayList::of($fieldNodes);
        }

        if ($fieldRequired) {
            $fieldNotes = $fieldNotes->append(ArrayList::of([
                new Small(['class' => 'text-warning'], 'Required. '),
            ]));
        }

        if ($this->spec->getFieldDescription($fieldName)->isJust()) {
            $fieldNotes = $fieldNotes->append(ArrayList::of([
                new Small(['class' => 'text-muted'], [
                    Maybe::fromJust(
                        $this->spec->getFieldDescription($fieldName)
                    )
                ]),
            ]));
        }

        if ($fieldNotes->count() > 0) {
            return $nodes
                ->append(ArrayList::of([
                    new Paragraph([], $fieldNotes)
                ]))
                ->toArray();
        }

        return $nodes->toArray();
    }

    /**
     * Render the object into a string.
     *
     * @return mixed
     */
    public function render()
    {
        $addColon = function ($label) {
            return Maybe::just(vsprintf('%s:', [$label]));
        };

        return (new Form(
            $this->attributes,
            $this->spec
                ->getAnnotations()
                ->map(function ($_, $key) use ($addColon) {
                    return new Row(['class' => 'form-group'], [
                        new Node(
                            'label',
                            ['class' => 'col-sm-2 form-control-label'],
                            Maybe::fromMaybe(
                                vsprintf('%s:', [$key]),
                                $this->spec
                                    ->getFieldLabel($key)
                                    ->bind($addColon)
                            )
                        ),
                        new Div(
                            ['class' => 'col-sm-8'],
                            $this->renderFullField($key)
                        )
                    ]);
                })
                ->append(ArrayMap::of([
                    new Row(['class' => 'form-group'], [
                        new Div(['class' => 'col-sm-offset-2 col-sm-10'], [
                            new Div(['class' => 'btn-group'], [
                                new Button(
                                    [
                                        'type' => 'reset',
                                        'class' => 'btn btn-secondary'
                                    ],
                                    'Reset'
                                ),
                                new Button(
                                    [
                                        'type' => 'submit',
                                        'class' => 'btn btn-primary'
                                    ],
                                    'Submit'
                                ),
                            ])
                        ])
                    ])
                ]))
        ))->render();
    }

    /**
     * Get a safe HTML version of the contents of this object.
     *
     * @return SafeHtmlWrapper
     */
    public function getSafeHtml()
    {
        return Html::safe($this->render());
    }
}
