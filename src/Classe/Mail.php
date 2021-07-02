<?php

namespace App\Classe;

use Mailjet\Client;
use Mailjet\Resources;

class Mail
{
    private $api_key = '7794f52e75ef2d193eee5262b0ac6882';
    private $api_secret_key = 'e0f716cb06c3e23e73cdb4c2cb99c561';

    public function send($to_email,$to_name,$subject,$content)
    {
        $mj = new Client($this->api_key, $this->api_secret_key,true,['version' => 'v3.1']);

        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "pouroucheric@gmail.com",
                        'Name' => "Ecommerce"
                    ],
                    'To' => [
                        [
                            'Email' => $to_email,
                            'Name' => $to_name
                        ]
                    ],
                    'TemplateID' => 3019624,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => [
                        'content' => $content,
                    ]
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success();
    }
}