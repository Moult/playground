<?php
/**
 * TwitchTVLiveStreamChecker
 *
 * Checks whether or not a stream is live
 *
 * @license MIT
 *
 * Copyright (C) 2013 Dion Moult <dion@thinkmoult.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE
 * OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class TwitchTVLiveStreamChecker
{
    private $stream_name;

    public function __construct($stream_name)
    {
        $this->stream_name = strtolower($stream_name);
    }

    public function check()
    {
        if ($this->has_outdated_list_of_live_streams())
        {
            $this->write_live_stream_list();
        }

        return $this->has_stream_name_in_live_stream_list();
    }

    private function has_outdated_list_of_live_streams()
    {
        $live_streams = file_get_contents('list_of_live.txt');
        $last_updated = (int) substr($live_streams, 0, 10);
        return (bool) (time() - $last_updated > 30);
    }

    private function write_live_stream_list()
    {
        $file_handle = fopen('list_of_live.txt', 'w');
        fwrite($file_handle, time().' ');

        $stream_total = 100;
        $page = 0;
        while ($stream_total >= 100)
        {
            $stream_total = 0;
            $streams = json_decode($this->get_url_contents('https://api.twitch.tv/kraken/streams?game=Dota+2&offset='.$page*100));
            foreach ($streams->streams as $stream)
            {
                var_dump($stream->channel->name);
                fwrite($file_handle, $stream->channel->name.' ');
                $stream_total++;
            }
            $page++;
        }

        fclose($file_handle);
    }

    private function get_url_contents($url)
    {
        $crl = curl_init();
        $timeout = 60;
        curl_setopt ($crl, CURLOPT_URL,$url);
        curl_setopt ($crl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
        $ret = curl_exec($crl);
        curl_close($crl);
        return $ret;
    }

    private function has_stream_name_in_live_stream_list()
    {
        $live_streams = file_get_contents('list_of_live.txt');
        return (strpos($live_streams, ' '.$this->stream_name.' ') !== FALSE);
    }
}

$url_params = array_keys($_GET);
$stream_name = $url_params[0];
$live_stream = new TwitchTVLiveStreamChecker($stream_name);

$img = imagecreatetruecolor(60, 15);
imagealphablending($img, TRUE);
$background_color = imageColorAllocate($img, 217, 221, 224);
imagefill($img, 4, 4, $background_color);

if ($live_stream->check())
{
    imagettftext($img, 9, 0, 0, 12, imageColorAllocate($img, 95, 195, 56), 'LiberationSans-Bold.ttf', 'ONLINE :)');
}
else
{
    imagettftext($img, 9, 0, 0, 12, imageColorAllocate($img, 195, 32, 32), 'LiberationSans-Bold.ttf', 'OFFLINE :(');
}

header('Content-type: image/png');
imagepng($img);
