<?php

abstract class SupervisorAction {

    protected $action = '';
    protected $failonerror = true;
    protected $name = '';
    protected $apiUrl = '';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return boolean
     */
    public function isFailonerror()
    {
        return $this->failonerror;
    }

    /**
     * @param boolean $failonerror
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = $failonerror;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        if (!is_null($this->getApiUrl())) {
            $this->apiUrl = $apiUrl;
        }
    }

    abstract public function execute();

    protected function callRpc($method, $params)
    {
        $post = xmlrpc_encode_request($method, $params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $post);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $output = curl_exec($ch);
echo $output;
        return xmlrpc_decode($output);
    }
}