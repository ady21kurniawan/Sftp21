

## How To use Sftp21


**Configuration :**

```
$config = [
    'host' => "<your-sftp-server>",
    'port' => "<your-server-port>", // optional: default is 22
    'username' => "<username-login>",
    'password' => "<username-password>",
    'root' => "<root-directory>",
    'timeout' => "<set-timeoot>" // optional: default is 30
];


$sftp_21 = new \Ady21kurniawan\Sftp21\Sftp21($config);
```


#### Connection test :


```
$sftp_21 = new Sftp21($config);
sftp_21->isConnected(); // returning boolean
```

## Working with filesytem

```
$filesystem = new \League\Flysystem\Filesystem($sftp_21);
```

*noted : Now all interface function will be implemented* 


## Lists Functions

*1. give information for connection status*
```
$sftp_21->connectionStatus(); 
```

*2. give your working directory that you setting in Configuration*
```
$sftp_21->workdir(); 
```

*3. Download File*
```
$sftp_21->downloadFile($pathFileRemote, $localPath); 
```

*4. Get Mime Type File*
```
$sftp_21->getMimetype($path);  // returning an array [ "mimetype" => <<mimetype>> ]
```
```
$filesystem->getMimetype($path);  // returning <<mimetype>>
```

*5. Get Metadata file*
```

$sftp_21->getMetadata($pathFileRemote);

or

$filesystem->getMetadata($pathFileRemote);

// returning an array assoc of metadata
```

*6. Get Timestamp file*

```
$sftp_21->getTimestamp($pathFileRemote); // returning an array  ["timestamp" => <<last access file timestamp>>]
```

```
$filesystem->getTimestamp($pathFileRemote); // returning last access file timestamp
```

*7. set File Visibility*


```
$sftp_21->setVisibility($pathFileRemote, $octal_number);

or

$filesystem->setVisibility($pathFileRemote, $octal_number);

//returning boolean;
noted : set args octal_number with octal number , example : instead '0644' you have to set 0644
```

*8. get File Visibility*

```
$sftp_21->getVisibility($pathFileRemote); // returning an array assoc of metadata file with info Visibility

```

```
$filesystem->getVisibility($pathFileRemote); // returning public or private 

```

*9. get File Info*

```
$sftp_21->getFileInfo($pathFileRemote); // returning metadata's file

```

*10. get File Size*

```
$sftp_21->getSize($pathFileRemote);

or

$filesystem->getSize($pathFileRemote);

// returning filesize on byte

```

*11. Moving File*

```
$sftp_21->move($pathFile, $destinationDirectory);
//returning boolean

Note : default $destinationDirectory is '/' and only directory, you dont have permit to give new file name

example : $sftp_21->move('remote_file/example.csv', 'remote_file/move')


```

*12. Renaming File*

```
$sftp_21->rename($path, $newpath);

or

$filesystem->rename($path, $newpath);

//returning boolean
Note : file should be same in one folder

```

*13. Update Content File*

```
$sftp_21->update($path, $contents, $visibility );

or

$filesystem->update($path, $contents, $visibility);

//returning boolean
Note : update process will be replacing contents, and visibility is optional, if you want to set visibility , set with octal number (example : 0644 not '0644')

```


*14. Update Content File (Stream)*
```
$sftp_21->updateStream($path, $resource, $visibility );

or

$filesystem->updateStream($path, $resource, $visibility);

//returning boolean
Note : same with update, diference is resource is should be Stream data, you can use function getDataStreamBiner or getDataStream

```

*14. Copy File*

```
$sftp_21->copy($path, $newpath);

or

$filesystem->copy($path, $newpath);

//returning boolean
```

*15. remove Directory*

```
$sftp_21->deleteDir($path_dir);

or

$filesystem->deleteDir($path_dir);

//returning boolean

note: directory only can be deleted, if directory is empty. make sure directory is empty
```

*16. Create Directory*

```
$filesystem->createDir($path_dir, $recursive);

or

$sftp_21->createDir($path_dir, $recursive);

//returning boolean

note : recursive set default is false
```

*17. Delete File*

```
$filesystem->delete($path_file);

or

$sftp_21->delete($path_file);

//returning boolean


```

*18. Read File Stream*

```
$filesystem->readStream($path_file);
//returning a stream file

```
```
$sftp_21->readStream($path_file);

//returning an array assoc ["stream" => <<stream file>> ]


```


*19. Read File*

```
$filesystem->read($path_file);
//returning a stream file

```
```
$sftp_21->read($path_file);

//returning an array assoc ["contents" => <<stream file>> ]

```

*20. Check File is Exists*

```
$filesystem->has($path_file);

or

$sftp_21->has($path_file);

//returning boolean

```

*21. Get List Files*

```
$filesystem->listContents($path_directory);

or

$sftp_21->listContents($path_directory);

//returning an array of list files

```

*22. write file*

```
$contents = 'This is a local file content yooo!';
$filesystem->write($path_file, $contents);

or

$contents = 'This is a local file content yooo!';
$sftp_21->write($path_file, $contents);

//returning boolean

```


*23. write file stream*

```
$contents = 'This is a local file content yooo!';
// convert to stream
$resource_stream = $sftp_21->getDataStream($contents);

$filesystem->writeStream($path_file, $resource_stream);

or

$contents = 'This is a local file content yooo!';
// convert to stream
$resource_stream = $sftp_21->getDataStream($contents);

$sftp_21->writeStream($path_file, $resource_stream);

//returning boolean

```

*24. get Data Stream*

```
$contents = 'This is a local file content yooo!';
$sftp_21->getDataStream($contents); // returning stream data

```
