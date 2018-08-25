<?php

namespace OpenClassrooms\Akismet\Client;

/**
 * @author Arnaud Lefèvre <arnaud.lefevre@openclassrooms.com>
 */
interface ApiClient
{
    /**
     * @param string $resource
     * @param array  $params
     *
     * @return string
     */
    public function post($resource, array $params);
} 
