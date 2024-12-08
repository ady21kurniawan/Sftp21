<?php

namespace Ady21kurniawan\Sftp21;
use phpseclib3\Net\SFTP;
use phpseclib3\Exception\UnableToConnectException;
use League\Flysystem\AdapterInterface;

class Sftp21 implements AdapterInterface{
    private $host;
    private $port;
    private $username;
    private $password;
    private $root;
    private $timeout = 30;
    private $is_connected = false;
    private $error_connection_message = "";
    private $sftp_libs = null;


    public function __construct($configs = [])
    {
        $this->setProperties($configs);
        $this->connect();
    }

    private function setProperties($configs)
    {
        $props = array_keys(get_object_vars($this));
        foreach( $configs as $key => $config )
        {
            if(in_array($key, $props))
            {
                $this->$key = $config;
            }
        }
    }

    private function connect()
    {
        try{

            $sftp = new SFTP($this->host, $this->port, $this->timeout);
            $sftp->login($this->username, $this->password);
            
            if (!$sftp->chdir($this->root)) {
                throw new UnableToConnectException("your root directory : {$this->root} not found");
            }

            if($sftp->pwd())
            {
                $this->is_connected = true;
                $this->sftp_libs = $sftp;
            }

        }catch( UnableToConnectException $e )
        {
            $this->error_connection_message = $e->getMessage();
        }catch (\Exception $e) {
            $this->error_connection_message = $e->getMessage();
        }
    }

    public function isConnected()
    {
        return $this->is_connected;
    }

    public function connectionStatus()
    {
       if($this->is_connected)
       {
            return "connecting success to server : {$this->host}:{$this->port} with root directory: {$this->root}";
       }
       return $this->error_connection_message;
    }

    public function workdir()
    {
        return $this->root;
    }

    public function listContents($directory = '' , $recursive = false) {
        // Implementasi metode read
        if(empty($directory || is_null($directory)))
        {
            $directory = $this->workdir();
        }
       
        $lists = $this->sftp_libs->rawlist($directory);
        $listing = [];
        foreach($lists as $list)
        {
            if($list["filename"] === '.' || $list["filename"] === '..')
            {
                continue;
            }

            $type = $list["type"] === 1 ? 'file' : 'dir';
            $filesize = $list["size"] ? $list["size"] : 0; // in bites
            $timestamp = $list['mtime'] ? $list['mtime'] : 0;
            $mode = $list['mode'] ? $list['mode'] : 0;
            array_push($listing, [
                'path' => $list['filename'],
                'type' => $type,
                'size' => $filesize,
                'timestamp' => $timestamp,
                'mode' => $mode
            ]);
        }
        return $listing;
    }

    /***
     * notes : 
     * if you can note write, please check permission folder or run 'chmod 777 
     * <folder_name>'
     * 
    */
    public function write($path, $contents, $visibility = null) {
        // Implementasi metode write
        $path = $this->workdir() .'/'. $path;
        $filename = explode("/", $path);
        $filename = $filename[count($filename) -1];
        $directory = str_replace($filename,"", $path);
        if (! $this->sftp_libs->chdir($directory)) {
            throw new \Exception("your root directory : {$path} not found");
        }

        return $this->sftp_libs->put($path, $contents, SFTP::SOURCE_STRING);
    }

    public function getDataStream($resource)
    {
        $stream = fopen('php://temp', 'r+');  // Membuka stream sementara
        fwrite($stream, $resource);  // Menulis konten ke stream
        rewind($stream);  // Pindahkan posisi file pointer ke awal
        return $stream;
    }

    /***
     * notes : 
     * if you can note write, please check permission folder or run 'chmod 777 
     * <folder_name>' and call function : getDataStream first before use this function
     * 
    */
    public function writeStream($path, $resource_stream, $visibility = null) {
       
        $path = $this->workdir() .'/'. $path;
        $filename = explode("/", $path);
        $filename = $filename[count($filename) -1];
        $directory = str_replace($filename,"", $path);
        if (! $this->sftp_libs->chdir($directory)) {
            throw new \Exception("your root directory : {$path} not found");
        }
        return $this->sftp_libs->put($path, $resource_stream);
    }

    public function update($path, $contents, $visibility = null) {
        // Implementasi metode update
    }

    public function updateStream($path, $resource, $visibility = null) {
        // Implementasi metode writeStream
    }

    public function delete($path) {
        // Implementasi metode delete
    }

    public function read($path) {
        // Implementasi metode read
    }

    public function rename($path, $newpath) {
        // Implementasi metode read
    }

    public function copy($path, $newpath) {
        // Implementasi metode read
    }

    public function deleteDir($dirname) {
        // Implementasi metode read
    }

    public function has($path) {
        // Implementasi metode read
    }

    public function readStream($path) {
        // Implementasi metode read
    }

    public function createDir($directory = '', $recursive = false) {
        // Implementasi metode read
    }

    public function getSize($path) {
        // Implementasi metode read
    }

    public function getMimetype($path) {
        // Implementasi metode read
    }

    public function setVisibility($path, $visibility) {
        // Implementasi metode read
    }

    public function getVisibility($path) {
        // Implementasi metode read
    }

    public function getMetadata($path)
    {

    }

    public function getTimestamp($path)
    {

    }



}