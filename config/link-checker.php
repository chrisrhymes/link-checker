<?php

/**
 * ChrisRhymes\LinkChecker configuration
 */
return [
    /**
     * The time to wait for a response
     */
    'timeout' => 10,

    /**
     * The rate limit to check broken links per minute
     * This is applied on a per domain basis
     */
    'rate_limit' => 5,

    /**
     * Retry the CheckLinkFailed job until a specified time (in minutes)
     */
    'retry_until' => 10,

    /**
     * Set a custom user agent
     */
    'user_agent' => 'link-checker',

    /**
     * Describes the SSL certificate verification behavior of a request
     */
    'verify' => true,
];
