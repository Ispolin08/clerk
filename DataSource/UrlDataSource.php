<?php

namespace Ispolin08\ClerkBundle\DataSource;

class UrlDataSource implements DataSourceInterface {

    public function getData($options){

        $ch = curl_init($options['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//                curl_setopt($ch,CURLOPT_HEADER, true);
        $source = curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        if (mb_strpos($contentType, 'application/json') !== false) {
            return json_decode($source, true);
        }

        return $source;
    }


}