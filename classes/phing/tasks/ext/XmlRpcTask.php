<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

/**
 * Make XMLRPC Calls
 *
 * <xmlrpc url="http://user:pass@127.0.0.1:1337/RPC2" method="supervisor.stopProcess" resultProperty="xmlResult" failonerror="false">
 *   <param name="name" type="string" value="foobar"></param>
 * </xmlrpc>
 *
 * @author Suat Özgür <suat.oezguer@mindgeek.com>
 * @package   phing.tasks.ext
 */

class XmlRpcTask extends Task {

    private $url = null;
    private $method = null;
    private $resultProperty = null;
    private $failonerror = true;
    private $params = array();

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
        $this->failonerror = StringHelper::booleanValue($failonerror);
    }

    /**
     * @return null
     */
    public function getResultProperty()
    {
        return $this->resultProperty;
    }

    /**
     * @param null $resultProperty
     */
    public function setResultProperty($resultProperty)
    {
        $this->resultProperty = $resultProperty;
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    public function addParam(Parameter $param) {
        $this->params[] = $param;
    }


    public function main()
    {
        $params = array();

        /** @var Parameter $param */
        foreach ($this->params as $param) {

            $value = $param->getValue();
            switch ($param->getType()) {
                case 'boolean':
                    $value = boolval($value);
                    break;

                case 'dateTime.iso8601':
                    xmlrpc_set_type($value, 'datetime');
                    break;

                case 'datetime':
                case 'base64':
                    xmlrpc_set_type($value, $param->getType());
                    break;

                case 'double':
                    $value = floatval($value);
                    break;

                case 'int':
                case 'i4':
                    $value = (int)$value;
                    break;

                case 'string':
                case null:
                    // default
                    break;

                default:
                    $this->log('Warning: unsupported type "' . $param->getType() . '" for param "' . $param->getName() . '"', Project::MSG_WARN );
                    break;
            }

            $params[$param->getName()] = $value;
        }


        $result = $this->executeRpcCall($this->getMethod(), $params);
        if (!is_null($this->getResultProperty())) {
            $this->project->setProperty(
                $this->getResultProperty(),
                $result
            );
        }
        $this->log( 'response=' . htmlspecialchars($result), Project::MSG_VERBOSE );
    }

    private function executeRpcCall($method, $params) {
        if (count($params) == 0) {
            $params = array(null);
        }

        $funcParams =  array_merge( array($method), array_values($params) );
        $postData = call_user_func_array('xmlrpc_encode_request', $funcParams);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);
        if ($response === false) {
            $msg = 'POST error: ' . curl_error($ch);
            $this->log($msg, ($this->isFailonerror() ? Project::MSG_ERR : Project::MSG_WARN) );
            if ($this->isFailonerror()) {
                throw new BuildException($msg);
            }
        }

        $responseDecoded = xmlrpc_decode($response);

        if (is_array($responseDecoded) && xmlrpc_is_fault($responseDecoded)) {
            $msg = 'XMLRPC fault: ' . $responseDecoded['faultString'] . ' (' . $responseDecoded['faultCode'] . ')';
            $this->log($msg, ($this->isFailonerror() ? Project::MSG_ERR : Project::MSG_WARN) );
            if ($this->isFailonerror()) {
                throw new BuildException($msg);
            }
        }

        return $response;
    }
}