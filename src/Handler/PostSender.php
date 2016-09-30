<?php


namespace MohandesPlusBot\Handler;


use Longman\TelegramBot\Request;
use MohandesPlusBot\Model\Channel;
use MohandesPlusBot\Model\Post;
use MohandesPlusBot\utils\Time;

class PostSender
{

    private $post;
    private $channel;
    private $shamsi;

    public function __construct()
    {
        $this->post = new Post();
        $this->shamsi = new Time();
        $this->channel = new Channel();

    }

    public function sendToChannel()
    {
        $posts = $this->getPostField();

        foreach ($posts as $post)
        {
            if ( $this->checkDate($post) )
            {
                $this->sendPost($post);
                $this->post->setSent($post['id']);
            }

        }
    }

    private function sendPost($post)
    {
        switch ($post['type'])
        {
            case ('context'):

                $this->sendContext($post);
                break;

            case ('photo'):

                $this->sendPhoto($post);
                break;

            case ('document'):
            case ('gif'):

                $this->sendGif($post);
                break;

            case ('video'):

                $this->sendVideo($post);
                break;
        }
    }

    private function sendVideo($post)
    {
        $botResponse = [];
        $botResponse['chat_id'] = '@' . $post['channel'];
        $botResponse['video'] = $post['file_id'];
        if ( $post['content'] != null )
            $botResponse['caption'] = $post['content'];

        Request::sendVideo($botResponse);
    }

    private function sendPhoto($post)
    {
        $botResponse = [];
        $botResponse['chat_id'] = '@' . $post['channel'];
        $botResponse['photo'] = $post['file_id'];
        if ( $post['content'] != null )
            $botResponse['caption'] = $post['content'];

        Request::sendPhoto($botResponse);
    }

    private function sendGif($post)
    {
        $botResponse = [];
        $botResponse['chat_id'] = '@' . $post['channel'];
        $botResponse['document'] = $post['file_id'];
        if ( $post['content'] != null )
            $botResponse['caption'] = $post['content'];

        Request::sendDocument($botResponse);
    }

    private function sendContext($post)
    {
        $botResponse = [];
        $botResponse['chat_id'] = '@' . $post['channel'];
        $botResponse['text'] = $post['content'];
        Request::sendMessage($botResponse);
    }

    private function getPostField()
    {
        $posts = $this->post->fetchAll();

        $index = 0;
        $all = [];
        foreach ($posts as $post)
        {
            foreach ($post as $key => $value)
            {
                $all[$index][$key] = $value;
                if ( $key == 'channel_id' )
                {
                    $all[$index]['channel'] = $this->channel->findUsernameById($value);
                    unset($all[$index]['channel_id']);
                }
            }

            $index++;
        }

        return $all;
    }

    private function checkDate($post)
    {
        if ( $this->shamsi->getPresentMonth() == $post['month'] )
        {
            if ( $this->shamsi->getPresentDate() == $post['date'] )
            {
                if ( $this->shamsi->getPresentHour() == $post['hour'] )
                {
                    if ( $this->shamsi->getPresentMinute() == $post['minute'] )
                    {
                        return true;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    return false;
                }
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }


}