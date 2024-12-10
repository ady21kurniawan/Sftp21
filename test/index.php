<?php

require_once __DIR__ . '/vendor/autoload.php';

use Ady21kurniawan\Sftp21\Sftp21;
use League\Flysystem\Filesystem;

$sftp_21 = new Sftp21([
    'host' => "<your-sftp-server>",
    'port' => "<your-server-port>",
    'username' => "<username-login>",
    'password' => "<username-password>",
    'root' => "<root-directory>",
    'timeout' => "<set-timeoot>"
]);

$filesystem = new Filesystem($sftp_21);


/**
 * use case testing
 */

//testCopy($filesystem);
//testDeleteDir($filesystem);
//testCreateDir($filesystem);
//testDelete($filesystem);
//testReadStream($filesystem);
//testHas($filesystem);
//testRead($filesystem);
//testListContents($filesystem);
//testWriteStream($filesystem, $sftp_21);
//testWrite($filesystem);



function testCopy($filesystem)
{
    $copy_file = $filesystem->copy('/example212.txt', 'folder_testing/example212_copy.txt');
    var_dump($copy_file).die();
}

/**
 * path directory delete should be the last folder and empty folder
 */
function testDeleteDir($filesystem)
{
    $deleteDir = $filesystem->deleteDir('testing2');
    var_dump($deleteDir).die();
}

function testCreateDir($filesystem)
{
    //$create_dir = $filesystem->createDir('testing2/subtest/dir1');
    $create_dir = $filesystem->createDir('testing2');
    var_dump($create_dir).die();
}

function testDelete($filesystem)
{
    $delete_file = $filesystem->delete('testing2/write-testing.txt');
    var_dump($delete_file).die();
}

function testReadStream($filesystem)
{
    $read_stream = $filesystem->readStream('example212.txt');
    var_dump( stream_get_contents($read_stream) ).die();
}

function testHas($filesystem)
{
    $isFileExists = $filesystem->has('example212.txt');
    var_dump($isFileExists).die();
}

function testRead($filesystem)
{
    $read_file = $filesystem->read('example212.txt');
    var_dump($read_file).die();
}

function testListContents($filesystem)
{
    $listFiles = $filesystem->listContents();
    var_dump($listFiles).die();
}

function testWriteStream($filesystem, $sftp_21)
{
    $data = 'This is a local file STREAM content yooo!';
    $data = $sftp_21->getDataStream($data);
    $results = $filesystem->writeStream("testing/write-stream-testing21.txt", $data);
    var_dump($results).die();
}

function testWrite($filesystem)
{
    $data = 'This is a local file content yooo!';
    $results = $filesystem->write("testing2/write-testing.txt", $data);
    var_dump($results).die();
}







