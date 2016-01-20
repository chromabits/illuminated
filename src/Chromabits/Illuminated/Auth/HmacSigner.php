<?php

/**
 * Copyright 2015, Eduardo Trujillo <ed@chromabits.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This file is part of the Illuminated package
 */

namespace Chromabits\Illuminated\Auth;

use Carbon\Carbon;
use Chromabits\Illuminated\Auth\Models\KeyPair;
use Chromabits\Nucleus\Foundation\BaseObject;
use Chromabits\Nucleus\Hashing\HmacHasher;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HmacSigner.
 *
 * @author Eduardo Trujillo <ed@chromabits.com>
 * @package Chromabits\Illuminated\Auth
 */
class HmacSigner extends BaseObject
{
    /**
     * @var KeyPair
     */
    protected $keyPair;

    /**
     * @var HmacHasher
     */
    protected $hasher;

    /**
     * Construct an instance of a HmacSigner.
     *
     * @param KeyPair $keyPair
     * @param HmacHasher $hasher
     *
     * @throws \Chromabits\Nucleus\Exceptions\LackOfCoffeeException
     */
    public function __construct(KeyPair $keyPair, HmacHasher $hasher = null)
    {
        parent::__construct();

        $this->keyPair = $keyPair;
        $this->hasher = $hasher === null ? new HmacHasher() : $hasher;
    }

    /**
     * Sing the request using HMAC.
     *
     * @param Request $request
     *
     * @return Request
     */
    public function sign(Request $request)
    {
        $contentHash = $this->hash($request->getContent());

        $request = clone $request;

        $request->headers->set('Content-Hash', $contentHash);
        $request->headers->set(
            'Authorization',
            vsprintf('Hash %s', [$this->keyPair->public_id])
        );

        return $request;
    }

    /**
     * Generate the content hash using the content string and the current date.
     *
     * @param string $content
     *
     * @return string
     */
    public function hash($content)
    {
        return $this->hasher->hash(
            $content . Carbon::now()->format('Y-m-d H:i'),
            $this->keyPair->secret_key
        );
    }
}
