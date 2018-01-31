<?php

class Album {
    /**
     * @var $group_id - id группы владельца альбома(пишиться со знаком "-")
     * @var $album_id - id альбома группы
     * @var $access_token - токен приложения VK
     * @var $count - количество фотографий
     * @var $offset - сдвиг
     * @var $rev - сортировка (0/1)
     * @var $save_folder - дириктория для сохранения фотографий
     */

    public $group_id;
    public $album_id;
    public $access_token;
    public $count;
    public $offset;
    public $rev;
    public $save_folder;

    /**
     * @return int|string возврашаем количество скаченных фотографий
     */
    public function download_album(){

        /**
         * Массив параметоров для API
         */

        $request_params = [
            'owner_id' => $this->group_id,
            'album_id' => $this->album_id,
            'count' => $this->count,
            'offset' => $this->offset,
            'rev' => $this->rev,
            'photo_sizes' => 0,
            'access_token' => $this->access_token
        ];


        /**
         * Получаем json cо ссылками на скачивание у VK API
         */
        $method ="photos.get";
        $url = 'https://api.vk.com/method/'.$method.'?'.http_build_query($request_params);
        $responseJson = file_get_contents($url);
        $response = json_decode($responseJson, true);

        /**
         * выбираем самые большие из возможных фотографий
         */
        $img_urls = array();
        foreach($response['response'] as $key=>$item){
            if (array_key_exists('src_xxbig', $item)) {
                $img_urls[] = $item['src_xxbig'];
            } else {
                if (array_key_exists('src_xbig', $item)) {
                    $img_urls[] = $item['src_xbig'];
                } else {
                    if (array_key_exists('src_big', $item)) {
                        $img_urls[] = $item['src_big'];
                    }
                }
            }
        }

        /**
         * Обходим массив ссылок на фотографии и сохраняем
         */
        foreach($img_urls as $key=>$img_url){
            $arr = explode(".",substr($img_url, -15));
            $img_id = $arr[0];
            $ch = curl_init($img_url);
            $fp = fopen($this->save_folder.$img_id.'.jpg', 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_exec($ch);
            sleep(0.25);
            curl_close($ch);
            fclose($fp);
        }

        /**
         * Воврещаем количество скаченных фотографий
         */
        return ($key+1);
    }

}


/**
 * Example
 */

  $album = new Album;

  $album->group_id = '-60394803';
  $album->album_id = '187786149';
  $album->count = 50;
  $album->offset = 0;
  $album->rev = 1;
  $album->access_token = '{token}'; // получить в ВК
  $album->$save_folder = 'c:/images/';

  $text = $album->download_album();

  echo 'Скачано '. $text . ' фотографий';