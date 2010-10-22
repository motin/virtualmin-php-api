<?php

class Virtualmin_Server
{
    public function __construct()
    {
        throw new Virtualmin_Exception(__CLASS__ . ' is not in use yet');
    }
    
    /**
     * Create Domain
     *
     * @param  string $name     Name of virtual server to create
     * @param  string $pass     Password for virtual server user
     * @param  array  $options  Array of features for domain
     * @param  string $output   Method of output
     * @return string
     */
    public function create($name, $pass, array $options = null, $output = null)
    {
        $option = '';
        if (is_array($options)) {
            foreach ($options as $key => $value) {
                $option .= '&' . $key . '=' . $value;
            }
        }
        
        if (null !== $output) {
            $output = '&' . $output . '=1';
        }
        
        $result = $this->execute('program=create-domain&domain=' . $name . '&pass=' . $pass . $option . $output);
        return $result;
    }
    
    /**
     * List Domains
     *
     * @param  bool  $output  Method of output
     * @param  bool  $array   Return out as array
     * @return mixed
     */
    public function list($output = null, $array = false)
    {
        if (null !== $output) {
            $output = "&output=" . $output;
        }
        $result = $this->execute('program=list-domains' . $output);
        
        if ($array) {
            $result = explode("\n", $result);
        }
        
        return $result;
    }
    
    /**
     * Disable Domain
     *
     * @param  string $domain Name of virtual server
     * @return string
     */
    public function disable($domain)
    {
        $result = $this->execute('program=disable-domain&domain=' . $domain);
        return $result;
    }
    
    /**
     * Enable Domain
     *
     * @param  string $domain Name of virtual server
     * @return string
     */
    public function enable($domain)
    {
        $result = $this->execute('program=enable-domain&domain=' . $domain);
        return $result;
    }
    
    /**
     * Delete Domain
     *
     * @param  string $domain Name of virtual server
     * @return string
     */
    public function delete($domain)
    {
        $result = $this->execute('program=delete-domain&domain=' . $domain);
        return $result;
    }
    
    /**
     * Execute Program
     *
     * @param  string $program Program to run
     * @return string
     */
    protected function _execute($program)
    {
        $result = shell_exec("wget -O - --quiet ".
            "--http-user=" . $this->getUsername() . " " .
            "--http-passwd=" . $this->getPassword() . " " .
            "--no-check-certificate " .
            "'" . $this->getHost() . ":" . $this->getPort() .
            "/virtual-server/remote.cgi?" . $program . "'");
        return $result;
    }
}