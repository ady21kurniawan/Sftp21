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

//testDownloadFile($sftp_21);
//testGetMymeType($filesystem, $sftp_21);
//testGetTimestamp($filesystem, $sftp_21);
//testGetmetaData($filesystem);
//setVisibility($filesystem);
//getVisibility($filesystem);
// testGetSize($filesystem, $sftp_21);
//testMove($sftp_21);
//testRename($sftp_21, $filesystem);
//testUpdateStream($sftp_21, $filesystem);
//testUpdate($sftp_21, $filesystem);
//testCopy21($sftp_21);
//testCopy($filesystem);
//testDeleteDir($filesystem);
//testCreateDir($filesystem, $sftp_21);
//testDelete($filesystem);
//testReadStream($filesystem, $sftp_21);
//testHas($filesystem);
//testRead($filesystem);
//testListContents($filesystem, $sftp_21);
//testWriteStream($filesystem, $sftp_21);
//testWrite($filesystem);
//testIsConnected($sftp_21);
//testGetDataStream($sftp_21);
//testGetDataStreamBiner($sftp_21);

function testGetDataStreamBiner($sftp_21)
{
    $new_content = "this is content of new coppier Stream 2010 with filesystem";
    $stream_content = $sftp_21->getDataStreamBiner($new_content);
    var_dump($stream_content).die();
}

function testGetDataStream($sftp_21)
{
    $new_content = "this is content of new coppier Stream 2010 with filesystem";
    $stream_content = $sftp_21->getDataStream($new_content);
    var_dump($stream_content).die();
}

function testIsConnected($sftp_21)
{
    $is_connect = $sftp_21->isConnected();
    var_dump($is_connect);
}

function testDownloadFile($sftp_21)
{
    $download_file = $sftp_21->downloadFile("folder_testing/image-image.png", "./download_file/image-image.png");
    var_dump($download_file).die();
}

function testGetMymeType($filesystem, $sftp_21)
{
    $mymeType = $filesystem->getMimetype("folder_testing/image-image.png");
    var_dump($mymeType).die(); 
}

function testGetTimestamp($filesystem, $sftp_21)
{
    $fileinfo = $filesystem->getTimestamp("daily_subscription_report_MSME_2024-11-01.csv");
    var_dump($fileinfo).die();
}

function testGetmetaData($filesystem)
{
    $meta_data = $filesystem->getMetaData("daily_subscription_report_MSME_2024-11-01.csv");
    var_dump($meta_data).die();
}

function setVisibility($filesystem)
{
    // public : 0644 | 0700
    $setVisible = $filesystem->setVisibility("daily_subscription_report_MSME_2024-11-01.csv", 0777);
    var_dump($setVisible).die();
}

function getVisibility($filesystem)
{
    $getVisible = $filesystem->getVisibility("daily_subscription_report_MSME_2024-11-01.csv");
    //$getVisible = $filesystem->getVisibility("example212_copy.txt");
    var_dump($getVisible).die();
}

//in byte
function testGetSize($filesystem, $sftp_21)
{
    // $filesize = $filesystem->getSize("daily_subscription_report_MSME_2024-11-01.csv");
    $filesize = $sftp_21->getSize("daily_subscription_report_MSME_2024-11-01.csv");
    var_dump($filesize).die();
}

function testMove($sftp_21)
{
    $moveFile = $sftp_21->move("/folder_testing/exampletest_stream.txt", "/");
    var_dump($moveFile).die();
}

function testRename($sftp_21, $filesystem)
{
    //$rename = $sftp_21->rename('folder_testing/example-rename.txt', 'folder_testing/example-rename-new.txt');
    $rename = $filesystem->rename('folder_testing/example-rename.txt', 'folder_testing/example-rename-new.txt');
    var_dump($rename).die();
}

function testUpdateStream($sftp_21, $filesystem)
{
    $new_content = "this is content of new coppier Stream 2010 with filesystem";
    $stream_content = $sftp_21->getDataStreamBiner($new_content);
    //$update_stream = $sftp_21->updateStream("folder_testing/copier-example.txt", $stream_content);
    $update_stream = $filesystem->update("folder_testing/copier-example.txt", $new_content);
    var_dump($update_stream).die();
}

function testUpdate($sftp_21, $filesystem)
{
    $new_content = "this is content of new coppier via filesystem";
    //$update = $sftp_21->update("folder_testing/copier-example.txt", $new_content);
    $update = $filesystem->update("folder_testing/copier-example.txt", $new_content);
    var_dump($update).die();
}

function testCopy21($sftp_21)
{
    $copier = $sftp_21->copy('/copier-example.txt', 'folder_testing/copier-example.txt');
    var_dump($copier).die();
}

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

function testCreateDir($filesystem, $sftp_21)
{
    //$create_dir = $filesystem->createDir('testing21/abc/123');
    $create_dir = $sftp_21->createDir('testing21/abc/123/dir1');
    var_dump($create_dir).die();
}

function testDelete($filesystem)
{
    $delete_file = $filesystem->delete('testing2/write-testing.txt');
    var_dump($delete_file).die();
}

function testReadStream($filesystem, $sftp_21)
{
    // $read_stream = $filesystem->readStream('example212.txt');
    //var_dump( stream_get_contents($read_stream) ).die();

    $read_stream = $sftp_21->readStream('example212.txt');
    var_dump($read_stream).die();
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

function testListContents($filesystem, $sftp_21)
{
    //$listFiles = $filesystem->listContents();
    $listFiles = $sftp_21->listContents();
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







