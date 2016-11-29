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

require_once "phing/Task.php";
require_once "phing/tasks/ext/supervisor/SupervisorStart.php";
require_once "phing/tasks/ext/supervisor/SupervisorStop.php";
require_once "phing/tasks/ext/supervisor/SupervisorRestart.php";

/**
 * SupervisorTask
 * start/stop/restart a supervisor process via xmlrpc (http://supervisord.org/)
 *
 * <supervisor apiurl="http://user:pass@hostname">
 *     <process name="name" action="start" failonerror="true" />
 *     <process name="name" action="start" failonerror="false" />
 * </supervisor>
 *
 */
class SupervisorTask extends Task {

    private $apiUrl = '';
    private $processList = array();

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
        $this->apiUrl = $apiUrl;
    }

    public function addStart(SupervisorStart $item) {
        $this->processList[] = $item;
    }

    public function addStop(SupervisorStop $item) {
        $this->processList[] = $item;
    }

    public function addRestart(SupervisorRestart $item) {
        $this->processList[] = $item;
    }


    public function init()
    {
        if (!function_exists('xmlrpc_encode_request')) {
            throw new BuildException('php-xmlrpc extension is not installed');
        }

        if (!function_exists('curl_init')) {
            throw new BuildException('php-curl extension is not installed');
        }
    }

    public function main()
    {
        /** @var SupervisorAction $process */
        foreach ($this->processList as $process) {
            $process->setApiUrl($this->getApiUrl());
            $process->execute();
        }
        $this->log('supervisortask main');
        curl_init();
        xmlrpc_encode_request("foo", array('x' => 'y'));
    }


}