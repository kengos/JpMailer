<?php

require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'phpmailer'.DIRECTORY_SEPARATOR.'class.phpmailer.php');

/**
 * JpMailer - PHPMailer Wrapper
 *
 * @license   LGPL
 * @link {{http://techblog.ecstudio.jp/tech-tips/mail-japanese-advance.html}}
 */
class JpMailer
{
  protected $_mailer = null;
  public $internalEncoding = 'UTF-8';
  public $mailCharset = 'iso-2022-jp';
  public $encoding = '7bit';
  public $throwException = false;
  /** phpmailer language (ar|br|ca|ch|cz|de|dk|es|et|fl|fo|fr|hu|it|ja|nl|no|pl|ro|ru|se|tr|zh|zh_cn|) */
  public $language = 'en';
  public static $testMode = false;
  public static $testMails = [];

  public function __construct($options = [])
  {
    if(isset($options['internalEncoding']))
      $this->internalEncoding = (string) $options['internalEncoding'];
    if(isset($options['mailCharset']))
      $this->mailCharset = (string) $options['mailCharset'];
    if(isset($options['encoding']))
      $this->encoding = (string) $options['encoding'];
    if(isset($options['throwException']))
      $this->throwException = (boolean) $options['throwException'];
    if(isset($options['language']))
      $this->language = (string) $options['language'];
    $this->buildMailer();
  }

  public function buildMailer()
  {
    $this->_mailer = new PHPMailer($this->throwException);
    $this->_mailer->CharSet = $this->mailCharset;
    $this->_mailer->encoding = $this->encoding;
    $this->_mailer->SetLanguage($this->language);
  }

  /**
   * PHP Magic method
   */
  public function __sleep(){ return []; }

  /**
   * PHP Magic method
   */
  public function __wakeup(){}

  public function addAddress($address, $name="")
  {
    if($name)
      $name = $this->encodeMimeHeader($name);
    $this->_mailer->addAddress($address, $name);
  }

  public function addTo($address, $name="")
  {
    $this->addAddress($address, $name);
  }

  public function addCc($address, $name="")
  {
    if($name)
      $name = $this->encodeMimeHeader($name);
    $this->_mailer->addCc($address,$name);
  }

  public function addBcc($address, $name="")
  {
    if($name)
      $name = $this->encodeMimeHeader($name);
    $this->_mailer->addBcc($address, $name);
  }

  public function addReplyTo($address, $name="")
  {
    if($name)
      $name = $this->encodeMimeHeader($name);
    $this->_mailer->addReplyTo($address,$name);
  }

  public function setSubject($subject)
  {
    $this->_mailer->Subject = $this->encodeMimeHeader($subject);
  }

  public function setFrom($address, $name="")
  {
    $this->_mailer->From = $address;
    if($name)
      $this->setFromName($name);
  }

  public function setFromName($name)
  {
    $this->_mailer->FromName = $this->encodeMimeHeader($name);
  }

  public function setBody($body)
  {
    $this->_mailer->Body = $this->convert_encoding($body);
    $this->_mailer->AltBody = "";
    $this->_mailer->IsHtml(false);
  }

  public function setHtmlBody($body, $altBody = '')
  {
    $this->_mailer->Body = $this->convert_encoding($body);
    $this->setAltBody($body);
    $this->_mailer->IsHtml(true);
  }

  public function setAltBody($body)
  {
    $this->_mailer->AltBody = $this->convert_encoding($body);
  }

  public function addHeader($key, $value)
  {
    if (!$value)
      return;

    $this->_mailer->addCustomHeader($key.":".$this->encodeMimeHeader($value));
  }

  public function send()
  {
    if(self::$testMode)
      $this->testSend();
    else
      $this->_mailer->Send();
  }

  /**
   * Override phpmailer#Send
   * Using __get
   */
  public function testSend() {
    try {
      if ((count($this->_mailer->to) + count($this->_mailer->cc) + count($this->_mailer->bcc)) < 1) {
        throw new phpmailerException($this->_mailer->Lang('provide_address'), PHPMailer::STOP_CRITICAL);
      }

      // Set whether the message is multipart/alternative
      if(!empty($this->_mailer->AltBody)) {
        $this->_mailer->ContentType = 'multipart/alternative';
      }

      $this->_mailer->error_count = 0; // reset errors
      $this->_mailer->SetMessageType();
      $header = $this->_mailer->CreateHeader();
      $body = $this->_mailer->CreateBody();

      if (empty($this->_mailer->Body)) {
        throw new phpmailerException($this->_mailer->Lang('empty_message'), PHPMailer::STOP_CRITICAL);
      }

      self::$testMails[] = [$header, $body];
      return true;

    } catch (phpmailerException $e) {
      $this->_mailer->SetError($e->getMessage());
      if ($this->_mailer->exceptions) {
        throw $e;
      }
      echo $e->getMessage()."\n";
      return false;
    }
  }
  /**
   * refer to JPHPMailer
   */
  protected function encodeMimeHeader($str, $charset=null, $linefeed="\r\n"){
    if (!strlen($str))
      return "";

    if (!$charset)
      $charset = $this->mailCharset;

    $str = $this->convert_encoding($str);
    $start = "=?$charset?B?";
    $end = "?=";
    $encoded = '';
  
    /* Each line must have length <= 75, including $start and $end */
    $length = 75 - strlen($start) - strlen($end);
    /* Average multi-byte ratio */
    $ratio = mb_strlen($str, $charset) / strlen($str);
    /* Base64 has a 4:3 ratio */
    $magic = $avglength = floor(3 * $length * $ratio / 4);
  
    for ($i=0; $i <= mb_strlen($str, $charset); $i+=$magic) {
      $magic = $avglength;
      $offset = 0;
      /* Recalculate magic for each line to be 100% sure */
      do {
        $magic -= $offset;
        $chunk = mb_substr($str, $i, $magic, $charset);
        $chunk = base64_encode($chunk);
        $offset++;
      } while (strlen($chunk) > $length);
      
      if ($chunk)
        $encoded .= ' '.$start.$chunk.$end.$linefeed;
    }
    /* Chomp the first space and the last linefeed */
    $encoded = substr($encoded, 1, -strlen($linefeed));
  
    return $encoded;
  }

  protected function convert_encoding($str)
  {
    if(strcasecmp($this->_mailer->CharSet, $this->internalEncoding) == 0)
      return $str;
    else
      return mb_convert_encoding($str, $this->_mailer->CharSet, $this->internalEncoding);
  }
}