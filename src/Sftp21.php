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

    public function downloadFile($pathFileRemote, $localPath)
    {
        if(! $this->has("{$this->root}/$pathFileRemote") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$pathFileRemote");
        }
        
        $folderPath = dirname(__DIR__) ."/". dirname($localPath);
        if( ! is_dir($folderPath) )
        {
            throw new \Exception("Local Path Download Not exists : {$folderPath}");
        }
        return $this->sftp_libs->get("{$this->root}/$pathFileRemote", $localPath);
    }

    public function getMimetype($path) 
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$path");
        }
        $get_file = $this->sftp_libs->get("{$this->root}/$path");
        $tempfile = tmpfile();
        fwrite($tempfile, $get_file);
        rewind($tempfile);
        $mimetype = mime_content_type($tempfile);
        fclose($tempfile);
        return [
            "mimetype" => $mimetype
        ];
        
    }

    public function getMetadata($path)
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        }
        
        return $this->sftp_libs->stat($path);
    }

    /**
     * return access time
     */
    public function getTimestamp($path)
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        } 

        $file_info = $this->sftp_libs->stat($path);
        return [
            "timestamp" => $file_info["atime"]
        ];
    }

    public function setVisibility($path, $visibility) 
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        }

        if(! preg_match('/^[0-7]+$/', $visibility) === 1)
        {
            throw new \Exception("please use octal number ! ");
        }

        if(is_string($visibility))
        {
            throw new \Exception("please use octal number not string ! ");
        }
        return $this->sftp_libs->chmod($visibility, $path);
    }

    public function getVisibility($path) 
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        }
        
        $fileinfo = $this->sftp_libs->stat($path);
        $mode = decoct($fileinfo["mode"]);
        $owner_permission = $mode & 00400;
        $others_permission = $mode & 00004;
        if ($owner_permission && $others_permission) 
        {
            $fileinfo["visibility"] = "public";
        } else 
        {
            $fileinfo["visibility"] = "private";
        }
        return $fileinfo;
    }

    public function getFileInfo($remoteFile) 
    {
        
        if(! $this->has("{$this->root}/$remoteFile") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        }
        return $this->sftp_libs->stat($remoteFile);
    }

    public function getSize($remoteFile) 
    {
        
        if(! $this->has("{$this->root}/$remoteFile") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$remoteFile");
        }
        return $this->sftp_libs->stat($remoteFile);
    }

    public function move($pathFile, $destinationDirectory = null)
    {
        if(is_null($destinationDirectory) || empty($destinationDirectory))
        {
            $destinationDirectory = "/";
        }
        if(! $this->has("{$this->root}/$pathFile") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$pathFile");
        }

        if($destinationDirectory[0] !== "/")
        {
            $destinationDirectory = "/{$destinationDirectory }";
        }

        if( ! $this->sftp_libs->is_dir("{$this->root}{$destinationDirectory}") )
        {
            throw new \Exception("Destination Directory Not found : {$this->root}/$destinationDirectory");
        }
        $pathFileName = explode("/", $pathFile);
        $filename = $pathFileName[count($pathFileName) -1];
        
        return  $this->sftp_libs->rename("{$this->root}/$pathFile", "{$this->root}/{$destinationDirectory}/{$filename}");
    } 

    public function rename($path, $newpath) 
    {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$path");
        }
        
        $pathCheck = explode("/", $path);
        $newPathCheck = explode("/", $newpath);
        
        if( count($pathCheck) != count($newPathCheck) )
        {
            throw new \Exception("file should be in one same folder1 !");
        }

        for($index = 0 ; $index < count($pathCheck); $index++ )
        {
            if($index == count($pathCheck) -1 )
            {
                break;
            }

            if($pathCheck[$index] != $newPathCheck[$index] )
            {
                throw new \Exception("file should be in one same folder2 !");
            }
        }

        return  $this->sftp_libs->rename("{$this->root}/$path", "{$this->root}/$newpath");
    }

    public function update($path, $contents, $visibility = null) {
        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$path");
        }
        return $this->sftp_libs->put("{$this->root}/$path" , $contents, SFTP::SOURCE_STRING);
    }

    public function updateStream($path, $resource, $visibility = null) {
        
        if ( ! is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new \Exception("resource should be Stream ! or use getDataStream or getDataStreamBiner function First");
        }

        if(! $this->has("{$this->root}/$path") )
        {
            throw new \Exception("File Not found On path : {$this->root}/$path");
        }

        return $this->sftp_libs->put("{$this->root}/$path" , $resource, SFTP::SOURCE_STRING);
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
            throw new \Exception("failed read file on : " . $path);
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
        
        if(empty($dirname))
        {
            throw new \Exception("directory name should not be empty");
        }

        $isDirExists = $this->sftp_libs->chdir($dirname);
        if( $isDirExists )
        {
            throw new \Exception("directory already exists");
        }
        if($recursive)
        {
            return $this->sftp_libs->mkdir($dirname,0777, true);
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

    public function getDataStreamBiner($resource)
    {
        $stream = fopen('php://temp', 'rb+');  // Membuka stream sementara
        fwrite($stream, $resource);  // Menulis konten ke stream
        rewind($stream);  // Pindahkan posisi file pointer ke awal
        return $stream;
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

}