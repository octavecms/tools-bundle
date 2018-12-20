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
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->secret = $secret;
    }


    /**
     * @param $response
     * @param null $secret
     * @return mixed
     */
    public function verify($response, $secret = null)
    {
        if ($secret) {
            $secret = $this->secret;
        }

        $post_data = http_build_query(
            array(
                'secret' => $secret,
                'response' => $response
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

        if (isset($result->score)) {
            // Recaptcha V3
            return $result->success && $result->score > 0.5;
        } else {
            // Recaptcha V2
            return $result->success;
        }
    }
}