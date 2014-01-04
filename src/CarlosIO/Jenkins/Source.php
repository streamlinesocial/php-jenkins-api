<?php
namespace CarlosIO\Jenkins;

use CarlosIO\Jenkins\Job;
use CarlosIO\Jenkins\Exception\SourceNotAvailableException;

class Source
{
    private $_url;
    private $_json;

    public function __construct($url, $user = false, $token = false)
    {
        $this->_url = $url;

        $headers = array('Content-Type: application/json');

        // start the curl request
        $process = curl_init($url);

        // configs used for user/token auto
        if ($user && $token) {
            $headers[] = "Authorization: Basic " . base64_encode("$user:$token");
            curl_setopt($process, CURLOPT_USERPWD, "$user:$token");
        }

        // standard configs
        curl_setopt($process, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($process);
        curl_close($process);

        $this->_json = @json_decode($json);

        if (!$this->_json) {
            throw new SourceNotAvailableException();
        }
    }

    public function getJobs()
    {
        $array = $this->_json->jobs;
        $jobs = array();
        foreach($array as $row) {
            $jobs[] = new Job($row);
        }

        return $jobs;
    }
}

