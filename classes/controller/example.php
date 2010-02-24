<?php defined('SYSPATH') or die("No direct access");
class Controller_Example extends Controller{
        public function action_index(){
                $file1 = DOCROOT . "uploads/100_1032.jpg";
                $file2 = DOCROOT . "uploads/background.jpg";
                $temp = Email::instance("This is the subject")
                        ->to(array("hello@gmail.com"=>"name","hi@gmail.com"=>name))
                        ->message(Email::template(View::factory("email-template"), array(":name"=>"Ginad 4 Ever")))
                        ->attach($file1)
                        ->attach($file2,true)
                        ->from("no-reply@gmail.com")
                        ->send(TRUE);
                     echo Kohana::debug($temp);

            }
}
