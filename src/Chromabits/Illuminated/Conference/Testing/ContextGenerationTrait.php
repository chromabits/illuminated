<?php

namespace Chromabits\Illuminated\Conference\Testing;

use Chromabits\Illuminated\Conference\Entities\ConferenceContext;
use Illuminate\Session\SessionManager;
use Mockery;

/**
 * Class ContextGenerationTrait
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Conference\Testing
 */
trait ContextGenerationTrait
{
    /**
     * Create a simple context.
     *
     * @param string $path
     *
     * @return ConferenceContext
     */
    public function makeSimpleContext($path = '/conference')
    {
        /** @var SessionManager $session */
        $session = Mockery::mock(SessionManager::class);

        return new ConferenceContext($path, $session);
    }
}
