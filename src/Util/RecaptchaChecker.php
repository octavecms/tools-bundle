<?php

namespace Octave\ToolsBundle\Util;

class RecaptchaChecker
{
    /** @var string */
    private $secret;

    /**
     * RecaptchaChecker constructor.
     * @param $secret
     */
    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    /**
     * @param $response
     * @return mixed
     */
    public function verify($response)
    {
        $post_data = http_build_query(
            array(
                'secret' => $this->secret,
                'response' => $response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        );
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $post_data
            )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result = json_decode($response);

        return $result->success;
    }
}