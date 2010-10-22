<?php
/**
 * Virtualmin Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ashley@ls12style.co.uk so we can send you a copy immediately.
 *
 * @category    Virtualmin
 * @package     Virtualmin_Core
 * @copyright   Copyright (c) 2010 Ashley Broadley (http://ls12style.co.uk)
 * @license     http://www.opensource.org/licenses/bsd-license.php     New BSD License
 */
/**
 * @category    Virtualmin
 * @package     Virtualmin_Core
 * @copyright   Copyright (c) 2010 Ashley Broadley (http://ls12style.co.uk)
 * @license     http://www.opensource.org/licenses/bsd-license.php     New BSD License
 * @author      Ashley Broadley <ashley@ls12style.co.uk>
 */

/**
 * @see Virtualmin_Exception
 */
require_once 'Exception.php';

class Virtualmin_Core
{
    /**
     * Default HTTP Host URI to Virtualmin server
     *
     * @var string
     */
    protected $_host            = 'http://localhost';
    
    /**
     * Default port
     *
     * @var integer
     */
    protected $_port            = 10000;
    
    /**
     * Number of seconds to wait before timeout
     *
     * @var integer
     */
    protected $_timeout         = 30;
    
    /**
     * Server username
     *
     * @var string
     */
    protected $_username;
    /**
     * Server user's password
     *
     * @var string
     */
    protected $_password;
    
    /**
     * Full URI of program and options
     *
     * @var string
     */
    protected $_program;
    
    /**
     * Constructor
     *
     * @param  array $options array of configuration options
     * @throws Virtualmin_Exception When invalid options are provided
     * @return void
     */
    public function __construct(array $options = null)
    {
        if (!is_array($options)) {
            throw new Virtualmin_Exception(__METHOD__ .' expects it\'s first parameter to be an array.' );
        }
        
        if (!isset($options['host'], $options['username'], $options['password'])) {
            throw new Virtualmin_Exception('Missing one of: Host, Username or Password.');
        }
        
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }
    
    /**
     * Setter magic method
     *
     * Checks to see if specified option has a setter method.
     *
     * @param  string $name name of property
     * @param  string $value value of property
     * @throws Virtualmin_Exception When invalid option is provided
     * @return void
     */
    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (!method_exists($this, $method)) {
            throw new Virtualmin_Exception('Invalid Virtualmin property');
        }
        $this->$method($value);
    }
    
    /**
     * Getter magic method
     *
     * Checks to see if specified option has a getter method.
     *
     * @param  string $name name of property
     * @throws Virtualmin_Exception When invalid option is requested
     * @return mixed
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (!method_exists($this, $method)) {
            throw new Virtualmin_Exception('Invalid Virtualmin property');
        }
        return $this->$method();
    }
    
    /**
     * Call magic method
     *
     * Checks to see if the specified server is online and accepting connections
     * via the specified port.
     *
     * If the user is calling any other method than execute, and it doesn't
     * exist, then an exception is thrown.
     *
     * @param  string $name     name of method
     * @param  array  $params   array of parameters
     * @throws Virtualmin_Exception When server is not accepting connections | Invalid method is called
     * @return Virtualmin_Core::_execute();
     */
    public function __call($name, $params)
    {
        if ('execute' == $name) {
            /* Check for HTTPS in hostname and use ssl://, default tcp:// */
            $protocol = 'tcp';
            if (strpos($this->getHost(), "https") == 1) {
                $protocol = 'ssl';
            }
            
            /* Remove http(s):// from host */
            $hostname = preg_replace('~http(.*)://~i', '', $this->getHost());
            
            /* Need to supress error to prevent PHP from spitting out warning */
            $fp = @fsockopen($protocol . '://' . $hostname,
                $this->getPort(),
                $errorNo,
                $errorStr,
                $this->getTimeout());
                
            if (!$fp) {
                throw new Virtualmin_Exception("Unable to connect to server: " . $this->getHost());
            }
            
            return $this->_execute(implode(', ', $params));
        }
        
        throw new Virtualmin_Exception('Unknown method called: ' . $name);
    }
    
    /**
     * Set all server options
     *
     * Sets options as protected properties. Array Keys are the names of the
     * protected option to set. Key Values are the value the option is set to.
     *
     * Calls the setter method for each option provided.
     *
     * @param  array $options array of options to be set
     * @return Virtualmin_Core
     */
    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods)) {
                $this->$method($value);
            }
        }
        return $this;
    }
    
    /**
     * Set Host
     *
     * @param  string $host URI of server
     * @return Virtualmin_Core
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }
    
    /**
     * Get Host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->_host;
    }
    
    /**
     * Set Username
     *
     * @param  string $username username of administritive user
     * @return Virtualmin_Core
     */
    public function setUsername($username)
    {
        $this->_username = $username;
        return $this;
    }
    
    /**
     * Get Username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->_username;
    }
    
    /**
     * Set Password
     *
     * @param  string $password administritive users password
     * @return Virtualmin_Core
     */
    public function setPassword($password)
    {
        $this->_password = $password;
        return $this;
    }
    
    /**
     * Get Password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->_password;
    }
    
    /**
     * Set Port
     *
     * @param  integer $port Port for virtualmin
     * @return Virtualmin_Core
     */
    public function setPort($port)
    {
        $this->_port = (int) $port;
        return $this;
    }
    
    /**
     * Get Port
     *
     * @return integer
     */
    public function getPort()
    {
        return $this->_port;
    }
    
    /**
     * Set Timeout
     *
     * @param  integer $timeout length of seconds to wait
     * @return Virtualmin_Core
     */
    public function setTimeout($timeout)
    {
        $this->_timeout = (int) $timeout;
        return $this;
    }
    
    /**
     * Get Timeout
     *
     * @return integer
     */
    public function getTimeout()
    {
        return $this->_timeout;
    }
}