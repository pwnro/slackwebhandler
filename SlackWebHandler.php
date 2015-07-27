<?php

namespace Nutzu\MonologExtra;

use \Monolog\Logger;

/**
 * Class to send log data to a slack channel through slack webhooks
 * 
 * @see https://api.slack.com/incoming-webhooks
 */
class SlackWebHandler extends \Monolog\Handler\AbstractProcessingHandler
{
    protected $hooksurl;
    protected $channel;
    protected $username;

    public function __construct(
        $hooksurl,
        $channel,
        $username,
        $level = Logger::ERROR,
        $bubble = true
    ) {
        parent::__construct($level, $bubble);

        $this->hooksurl         = $hooksurl;
        $this->channel          = $channel;
        $this->username         = $username;
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        if (!$this->isCurlEnabled()) {
            throw new MissingExtensionException('The curl PHP extension is required to use the SlackWebHandler');
        }
        
        $message = $record['channel'] . '-' . $record['level_name'] . ': ' . $record['message'];
        
        $payload = array(
            'channel'       => $this->channel,
            'username'      => $this->username,
            'text'          => $message,
            'icon_emoji'    => ":ghost:"
        );
        $payload = json_encode($payload);
        
        // post data to slack webhook
        $ch = curl_init();
        
        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $this->hooksurl);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query(array('payload' => $payload)));
        
        //execute post
        $result = curl_exec($ch);
        
        //close connection
        curl_close($ch);
    }
    
    protected function isCurlEnabled()
    {
        return extension_loaded('curl');
    }    
}
