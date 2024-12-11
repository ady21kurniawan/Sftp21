<?php

namespace Ady21kurniawan\Sftp21;
use phpseclib3\Net\SFTP;
use phpseclib3\Exception\UnableToConnectException;
use League\Flysystem\AdapterInterface;

class Sftp21 implements AdapterInterface{
    private $host;
    private $port = 22;
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

    public function copy($path, $newpath) 
    {
        if( $this->sftp_libs->file_exists($newpath) )
        {
            throw new \Exception("new path :". $newpath ."already exists");
        }

        $file_content = $this->readStream($path);
        if(! isset($file_content["stream"]) )
        {
            throw new \Exception("failed read file on : " . $$path);
        }

        return $this->writeStream($newpath, $file_content["stream"]);
    }

    public function deleteDir($pathname = '') {
        $isDirExists = $this->sftp_libs->chdir($pathname);

        if( !$isDirExists )
        {
            throw new \Exception("directory not exists");
        }
        $isEmptyDir = $this->listContents($pathname);
        if(count($isEmptyDir))
        {
            throw new \Exception("directory not empty !!");
        }
        
        $pathname = $this->root ."/". $pathname."/";
        // always use full path !!
        return $this->sftp_libs->rmdir($pathname);
    }

    public function createDir($dirname = '', $recursive = false) {
        
        $isDirExists = $this->sftp_libs->chdir($dirname);
        if( $isDirExists )
        {
            throw new \Exception("directory already exists");
        }
        return $this->sftp_libs->mkdir($dirname);
    }

    public function delete($path = '') {
        return $this->sftp_libs->delete($path);
    }

    public function readStream($path = '') {
        $isReadAble = $this->sftp_libs->is_readable($path);    
        if($isReadAble)
        {
            $content = $this->sftp_libs->get($path);
            return [ 'stream' => $this->getDataStream($content) ];
        }
        return [ 'stream' => '' ];
    }

    public function read($path = '') {
        $isReadAble = $this->sftp_libs->is_readable($path);    
        if($isReadAble)
        {
            $content = $this->sftp_libs->get($path);
            return [ 'contents' => $content ];
        }
        return [ 'contents' => '' ];
    }

    public function has($path = '') 
    {
        return $this->sftp_libs->file_exists($path);
    }

    public function listContents($directory = '' , $recursive = false) {

        if(empty($directory || is_null($directory)))
        {
            $directory = $this->workdir();
        }else{
            $directory = $this->workdir()."/".$directory;
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

    public function rename($path, $newpath) {
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