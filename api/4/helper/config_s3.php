<?php
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

Class Config_s3
{

  // Bucket Name
  public $bucket = "jimbouploads";
  public $bucket_items = "items/";
  public $bucket_profiles = "profiles/";
  
  public $client = null;

  // Client usage
  public $key;
  public $sourcefile;
  public $storageclass = "REDUCED_REDUNDANCY";
  public $acl = 'public-read';

  // tmp location
  public $upload_api_3_base = '';
  public $testbot_testuploads = '';

  public function __construct() {
    //AWS access info
    if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAITZ6O72YLYCX5A2A');
    if (!defined('awsSecretKey')) define('awsSecretKey', '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');
     
        // Set Amazon s3 credentials
          $this->client = S3Client::factory(
          array(
          'credentials' => array(
            'key'    => awsAccessKey,
            'secret' => awsSecretKey,
          ),
          'region' => 'us-west-2',
          'version' => '2006-03-01',
          'signature_version' => 'v4',
           )
          );
    
    $this->setTmpLocation();
    $this->setTestbotTmpLocation();
  }

  public function getTest(){
    return $this->upload_api_3_base;
  }

  public function getTestbotTmp(){
    return $this->testbot_testuploads;
  }

  /**
   * Set tmp location
   */
  public function setTmpLocation() {
  //  include '../../config.inc';
  //echo  dirname(dirname(dirname(dirname(__FILE__))));
    include  dirname(dirname(dirname(dirname(__FILE__)))). '/config.inc';
    $this->upload_api_3_base = $upload_api_3_base;
  }
  /**
   * Set testbot/tmp location
   */
  public function setTestbotTmpLocation() {
    //  include '../../config.inc';
     include  dirname(dirname(dirname(dirname(__FILE__)))).'/config.inc';
    $this->testbot_testuploads = $testbot_testuploads;
  }
  /**
   * Make a tmp file
   */
  public function __makeTmpFile($content, $filename, $dir) {
    if (!file_exists($dir)) {
      mkdir($dir, 0777, true);
    }
    $fh = fopen($dir.$filename, 'w') or die("can't open file");    
    fwrite($fh, $content);
    fclose($fh);
    $file= $dir.$filename;
    return $file;
  }

  /**
   * Open a file
   */
  public function __fetchFile($filename, $dir) {
    $file= $dir.$filename;
    return $file; 
  }

  /**
   * Remove tmp file
   */
  public function __unlinkFile($filepath) {
    /*chown($filepath, 666);*/
    /*$fh = fopen($filepath, 'w') or die("can't open file");
    fclose($fh);*/
    unlink($filepath);
  }

  /**
   * Set Put object variables
   */
  public function _setPutObjectVariables($key, $sourcefile) {
    $this->key = $key;
    $this->sourcefile = $sourcefile;
  }

  /**
   * Put Object
   * Using putObject method from aws
   */
  public function _putObject($will_unlink = false) {
    try {
          $this->client->putObject(array(
               'Bucket'=> $this->bucket,
               'Key' => $this->key,
               'SourceFile' => $this->sourcefile,
               'StorageClass' => $this->storageclass,
               'ACL' => $this->acl
			                   
            )
          );

          if($will_unlink){
            gc_collect_cycles();
            unlink($this->sourcefile); 
          }
          // echo $this->sourcefile;

          $message = "S3 Upload Successful.";
          $s3file='http://'.$this->bucket.'.s3.amazonaws.com/'.$this->key;
          return ['status' => true, 'result' => $s3file];
      } catch (S3Exception $e) {
           // Catch an S3 specific exception.
          $message = $e->getMessage();
          return ['status' => false, 'result' => $message];
      }
  }

}
?>