<?php
namespace CarlosIO\Jenkins;

use CarlosIO\Jenkins\Job;
use CarlosIO\Jenkins\Exception\SourceNotAvailableException;

class Source
{
    private $_type;
    private $_base_url;
    private $_json;
    private $_auth = false;

    public function __construct($url, $user = null, $token = null)
    {
        // set url (data source))
        $this->_base_url = $url;

        // set auth too if given now
        $this->setAuth($user, $token);
    }

    public function setAuth($user, $token)
    {
        // configs used for user/token auto
        if ($user && $token) {
            $this->_auth = "$user:$token";
        }
    }

    /**
     * @param $path The endpoint at the configured server to query data for
     */
    public function fetchData($path)
    {
        $this->_json = null;

        $headers = array('Content-Type: application/json');

        // start the curl request
        $http = curl_init($this->_base_url . $path);

        if ($this->_auth) {
            $headers[] = "Authorization: Basic " . base64_encode($this->_auth);
            curl_setopt($http, CURLOPT_USERPWD, $this->_auth);
        }

        // standard configs
        curl_setopt($http, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($http, CURLOPT_TIMEOUT, 30);
        curl_setopt($http, CURLOPT_RETURNTRANSFER, true);

        // make the curl call
        $json = curl_exec($http);
        $http_status = curl_getinfo($http, CURLINFO_HTTP_CODE);

        // close the connection
        curl_close($http);

        if ($http_status == 200) {
            // parse the response
            $this->_json = json_decode($json);
        }

        if (!$this->_json) {
            throw new SourceNotAvailableException();
        }

        return $this->getData();
    }

    public function getData()
    {
        return $this->_json;
    }

    /**
     * Utility to build querys for different data types. These are
     * guidelines, but help to show examples and provide easy default
     * data types that are common to query for.
     */
    public function buildPath($type, $params)
    {
        switch ($type) {
            case 'job':
                $path = "job/{$params['job_name']}/api/json";
                break;
            case 'build':
                $path = "job/{$params['job_name']}/{$params['build_number']}/api/json";
                break;
        }

        return $path;
    }

    // public function getJobs()
    // {
    //     $array = $this->_json->jobs;
    //     $jobs = array();
    //     foreach($array as $row) {
    //         $jobs[] = new Job($row);
    //     }

    //     return $jobs;
    // }
}

