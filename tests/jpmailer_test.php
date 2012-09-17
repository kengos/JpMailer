<?php

class JpMailerTest extends PHPUnit_Framework_TestCase
{
  public function tearDown()
  {
    JpMailer::$testMode = false;
    JpMailer::$testMails = [];
  }

  public function testConstruct()
  {
    $mailer = new JpMailer();
    $this->assertEquals('UTF-8', $mailer->internalEncoding);
    $this->assertEquals('iso-2022-jp', $mailer->mailCharset);
    $this->assertEquals('7bit', $mailer->encoding);
    $this->assertFalse($mailer->throwException);
    $this->assertEquals('en', $mailer->language);
  }

  public function testConstructWithOptions()
  {
    $mailer = new JpMailer(
      array(
        'internalEncoding' => 'SJIS',
        'mailCharset' => 'UTF-8',
        'encoding' => '8bit',
        'throwException' => true,
        'language' => 'ja'
      )
    );

    $this->assertEquals('SJIS', $mailer->internalEncoding);
    $this->assertEquals('UTF-8', $mailer->mailCharset);
    $this->assertEquals('8bit', $mailer->encoding);
    $this->assertTrue($mailer->throwException);
    $this->assertEquals('ja', $mailer->language);
  }

  public function testJpMailerTestMode()
  {
    JpMailer::$testMode = true;
    $mailer = new JpMailer;
    $mailer->addAddress('test@example.com', 'けんご');
    $mailer->setFrom('test@example.com', 'ケンゴ');
    $mailer->setBody("あいうえお\nアイウエオ\n藍上雄");
    $mailer->send();

    $mail = JpMailer::$testMails[0];
    $body = mb_convert_encoding($mail[1], 'UTF-8', 'JIS');
    $this->assertEquals("あいうえお\nアイウエオ\n藍上雄\n", $body);
  }
}