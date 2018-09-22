<?php

namespace JJSee\CFStream;

use GuzzleHttp\Client;

class CFStreamLaravel extends CFStream
{
    public function __construct()
    {
        parent::__construct(config('cloudflare.key'), config('cloudflare.zone'), config('cloudflare.email'));
    }
}
