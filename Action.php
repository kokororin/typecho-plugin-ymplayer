<?php
if (!defined('__TYPECHO_ROOT_DIR__'))
{
    exit;
}

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
        $siteUrl = rtrim(Helper::options()->siteUrl, '/');
        header('Access-Control-Allow-Origin: ' . $siteUrl);
        header('Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With');
        header('Access-Control-Allow-Methods: GET');

        if (strpos($this->request->getReferer(), Helper::options()->siteUrl) === false)
        {
            $this->throw403();
        }

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

    protected function playlist()
    {
        $id = $this->request->get('id');
        if (is_null($id))
        {
            $this->throw404();
        }
        $cache = $this->get_cache($id, 'playlist');
        if (!$cache)
        {
            $url = 'http://music.163.com/api/playlist/detail/?id=' . $id;
            $json = $this->fetch($url);
            $data = json_decode($json, true);
            $array = array();
            if ($data['code'] == 200)
            {
                foreach ($data['result']['tracks'] as $value)
                {
                    $array[] = array(
                        'title' => $value['name'],
                        'song_id' => $value['id'],
                        'src' => $value['mp3Url'],
                        'album_id' => $value['album']['id'],
                        'cover' => $value['album']['picUrl'],
                        'artist' => $value['artists'][0]['name'],
                    );
                }
                $this->set_cache($id, 'playlist', json_encode($array));
                $this->response->throwJson($array);
            }
            else
            {
                $this->throw404();
            }
        }
        else
        {
            exit($cache);
        }

    }

    protected function song()
    {
        $id = $this->request->get('id');
        if (is_null($id))
        {
            $this->throw404();
        }
        $cache = $this->get_cache($id, 'song');
        if (!$cache)
        {
            $url = 'http://music.163.com/api/song/detail/?id=' . $id . '&ids=%5B' . $id . '%5D';
            $json = $this->fetch($url);
            $data = json_decode($json, true);
            $array = array();

            if ($data['code'] == 200)
            {
                $array = array(
                    'title' => $data['songs'][0]['name'],
                    'song_id' => $data['songs'][0]['id'],
                    'src' => $data['songs'][0]['mp3Url'],
                    'cover' => $data['songs'][0]['album']['picUrl'],
                    'artist' => $data['songs'][0]['artists'][0]['name'],
                );
                $this->set_cache($id, 'song', json_encode($array));
                $this->response->throwJson($array);
            }
            else
            {
                $this->throw404();
            }
        }
        else
        {
            exit($cache);
        }

    }

    protected function lyric()
    {
        $id = $this->request->get('id');
        if (is_null($id))
        {
            $this->throw404();
        }
        $cache = $this->get_cache($id, 'lyric');

        if (!$cache)
        {
            $url = 'http://music.163.com/api/song/media?id=' . $id;

            $json = $this->fetch($url);

            $result = json_decode($json, true);
            if ($result['code'] == 200)
            {
                if ($result['lyric'])
                {
                    $array = array(
                        'status' => true,
                        'lyric' => $result['lyric'],
                    );
                    $this->set_cache($id, 'lyric', $array['lyric']);
                    $this->response->throwJson($array);
                }
                else
                {
                    $this->response->throwJson(array(
                        'status' => true,
                        'lyric' => 'not found',
                    ));
                }
            }
        }
        else
        {
            $this->response->throwJson(array(
                'status' => true,
                'lyric' => $cache,
            ));
        }

        $this->throw404();

    }

    protected function fetch($url)
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

    protected function throw404()
    {
        Typecho_Response::setStatus(404);
        $this->response->throwJson(array('status' => '404 Not Found.'));
    }

    protected function throw403()
    {
        Typecho_Response::setStatus(403);
        $this->response->throwJson(array('status' => '403 Forbidden.'));
    }

    protected function get_cache($id, $type)
    {
        $filename = $this->get_cache_name($id, $type);
        if (is_file($filename))
        {
            return file_get_contents($filename);
        }
        else
        {
            return false;
        }

    }

    protected function set_cache($id, $type, $content)
    {
        $filename = $this->get_cache_name($id, $type);
        file_put_contents($filename, $content);
    }

    protected function get_cache_name($id, $type)
    {
        $cache_dir = dirname(__FILE__) . '/cache';
        if ($type == 'song')
        {
            return $cache_dir . '/' . $id . '.json';
        }
        elseif ($type == 'lyric')
        {
            return $cache_dir . '/' . $id . '.lrc';
        }
        elseif ($type == 'playlist')
        {
            return $cache_dir . '/' . $id . '.playlist.json';
        }
        else
        {
            return false;
        }
    }

    protected function checkUpdate()
    {
        $remote = 'https://kotori.sinaapp.com/ymplayer/latest?path=Plugin.php';
        $local = dirname(__FILE__) . '/Plugin.php';
        $info = Typecho_Plugin::parseInfo($remote);
        $latest_version = $info['version'];
        $info = Typecho_Plugin::parseInfo($local);
        $current_version = $info['version'];
        $text = '你的版本是' . $current_version . '，GitHub上游版本是' . $latest_version . '，';
        if ($current_version >= $latest_version)
        {
            $status = false;
            $text .= '无需更新。';
        }
        else
        {
            $status = true;
            $text .= '再次点击按钮进行更新。';
        }
        $this->response->throwJson(array(
            'status' => $status,
            'text' => $text,
        ));
    }

    protected function downloadUpdate()
    {
        $array = array(
            $this->downloadFile('Plugin.php'),
            $this->downloadFile('Action.php'),
            $this->downloadFile('force.css'),
            $this->downloadFile('init.js'),
            $this->downloadFile('dist/ymplayer.css'),
            $this->downloadFile('dist/ymplayer.min.js'),
        );
        foreach ($array as $value)
        {
            if ($value == false)
            {
                exit('failure');
            }
        }
        exit('success');
    }

    protected function downloadFile($path)
    {
        $url = 'https://kotori.sinaapp.com/ymplayer/latest?path=' . $path;
        $path = dirname(__FILE__) . '/' . $path;
        try {
            $ch = curl_init();
            $fp = fopen($path, 'wb');
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
            return true;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

}
