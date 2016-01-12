<?php
class ymplayer_Action extends Typecho_Widget implements Widget_Interface_Do
{
    public function execute()
    {
    }

    public function action()
    {
    }

    public function ajax()
    {
        $type = $this->request->get('type');
        if (method_exists($this, $type))
        {
            $this->$type();
        }
        else
        {
            $this->throw404();
        }
    }

    private function song()
    {
        $id = $this->request->get('id');
        if (is_null($id))
        {
            $this->throw404();
        }
        $url   = 'http://music.163.com/api/song/detail/?id=' . $id . '&ids=%5B' . $id . '%5D';
        $json  = $this->fetch($url);
        $data  = json_decode($json, true);
        $array = array();

        if ($data['code'] == 200)
        {
            $array = array(
                'title'   => $data['songs'][0]['name'],
                'song_id' => $data['songs'][0]['id'],
                'src'     => $data['songs'][0]['mp3Url'],
                'cover'   => $data['songs'][0]['album']['picUrl'],
                'artist'  => $data['songs'][0]['artists'][0]['name'],
            );
        }
        else
        {
            $this->throw404();
        }
        $this->response->throwJson($array);
    }

    private function lyric()
    {
        $id  = $this->request->get('id');
        $url = 'http://music.163.com/api/song/media?id=' . $id;

        $json = $this->fetch($url);

        $result = json_decode($json, true);
        if ($result['code'] == 200)
        {
            if ($result['lyric'])
            {
                $this->response->throwJson(array(
                    'status' => true,
                    'lyric'  => $result['lyric'],
                ));
            }
            else
            {
                $this->response->throwJson(array(
                    'status' => true,
                    'lyric'  => 'not found',
                ));
            }
        }

        $this->throw404();

    }

    private function fetch($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private function throw404()
    {
        Typecho_Response::setStatus(404);
        $this->response->throwJson(array('status' => '404 Not Found.'));
    }

    private function throw403()
    {
        Typecho_Response::setStatus(403);
        $this->response->throwJson(array('status' => '403 Forbidden.'));
    }

    private function substr($str, $start = 0, $length, $charset = "utf-8", $suffix = true)
    {
        if (function_exists("mb_substr"))
        {
            if ($suffix && strlen($str) > $length)
            {
                return mb_substr($str, $start, $length, $charset) . "...";
            }
            else
            {
                return mb_substr($str, $start, $length, $charset);
            }
        }
        elseif (function_exists('iconv_substr'))
        {
            if ($suffix && strlen($str) > $length)
            {
                return iconv_substr($str, $start, $length, $charset) . "...";
            }
            else
            {
                return iconv_substr($str, $start, $length, $charset);
            }
        }
        $re['utf-8']  = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        if ($suffix)
        {
            return $slice . "…";
        }
        return $slice;
    }

}
