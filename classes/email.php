<?php defined('SYSPATH') or die('No direct script access.');
class Email {

    private $swift = NULL;
    private $msg = NULL;

    # constants
    const TO = 0;
    const CC = 1;
    const BCC = 2;

    public static function instance($subject){
        return new Email($subject);
    }
    # view file, eg. View::factory('email-template')
    # variable to replace, eg. array(":name"=>"ginad",...)
    public static function template($view,$variables){
        return __($view, $variables);
    }

    public function __construct($subject){
        $config = Kohana::config("email");
        require Kohana::find_file('vendor', 'swift/swift_required');
        $cn = Swift_SmtpTransport::newInstance($config['host'], $config['port'], $config['security'])
        ->setUsername($config['username'])
        ->setPassword($config['password']);
        
        $this->swift = Swift_Mailer::newInstance($cn);
        $this->msg = Swift_Message::newInstance($subject);

        # swift flood control
        # disconnect and reconnect every 100 emails
        # pause for 30 seconds
        $this->swift->registerPlugin(new Swift_Plugins_AntiFloodPlugin(100, 30));
    }

    # email messages
    public function message($message,$type = 'text/html'){
            $this->msg->setBody($message,$type);
        return $this;
    }
    # alternative body
    public function altmessage($message,$type = 'text/plain'){
            $this->msg->addPart($message,$type);
            return $this;
    }

    # assoc array, eg. array("hello@google.com"=>"name")
    # $type can be Email::TO, Email::CC, Email::BCC
    public function to(array $to,$type = Email::TO){
        $type = strtolower($type);
        switch($type):
            case 1:
                # Cc:
                $this->msg->setCc($to);
            break;
            case 2:
                # Bcc:
                $this->msg->setBcc($to);
            break;
            default:
                # To:
                $this->msg->setTo($to);
        endswitch;

        return $this;
    }

     # From:
     public function from($from){
         $this->msg->setFrom($from);
         return $this;
     }

     # use for attachments
     # @file should be the absolute path of the file
    public function attach($file,$inline = false){
        $f = Swift_Attachment::fromPath($file);
        if($inline):
            $f->setDisposition("inline");
        endif;
        $this->msg->attach($f);
        return $this;
    }

    # return array of failed receipients
    public function send($batchSend = FALSE){
        if($batchSend):
            # send email, sends a separate message to each recipient
            # each recipient receives a message containing only their own address
            $this->swift->batchSend($this->msg,$failed);
        else:
            $this->swift->send($this->msg,$failed);
        endif;
        return $failed;
    }
}
?>
