<?php

namespace Cumulus\Cumulus\CloudflareEndpoints;

use stdClass;
use Cloudflare\API\Endpoints\API;
use Cloudflare\API\Adapter\Adapter;
use Cloudflare\API\Traits\BodyAccessorTrait;

class UserApiToken implements API
{
    use BodyAccessorTrait;

    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public function verifyToken(): stdClass
    {
        $user = $this->adapter->get('user/tokens/verify');
        $this->body = json_decode($user->getBody());
        return $this->body->result;
    }
}