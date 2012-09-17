# JpMailer

phpmailerのラッパーです。

phpmailerの一部を書き換えているため、LGPLでの公開となります。

# 修正点

http://techblog.ecstudio.jp/tech-tips/mail-japanese-advance.html

を参考に修正を加えています。

メールのテストがPHPUnit上である程度できるように
testModeを追加しています。

PHPUnit等でメールのテストをする際には以下のようにしてください。

````
public function setup()
{
  JpMailer::$testMode = true;
  JpMailer::$testMails = [];
}

public function teatDown()
{
  JpMailer::$testMode = false;
  JpMailer::$testMails = [];
}
````

メールの送信方法、メールの取り出しは以下のように行います。

メール取り出し後の変換ライブラリは準備中です。

````
public function testMail()
{
  $mailer = new JpMailer;
  $mailer->addAddress('test@example.com', 'けんご');
  $mailer->setFrom('test@example.com', 'ケンゴ');
  $mailer->setBody("あいうえお\nアイウエオ\n藍上雄");
  $mailer->send();

  $mail = JpMailer::$testMails[0];
  $body = mb_convert_encoding($mail[1], 'UTF-8', 'JIS');
  $this->assertEquals("あいうえお\nアイウエオ\n藍上雄\n", $body);
}
````