<?php

class Parser {

    private $url;
    private $path;

    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * This method parse your url.
     *
     */
    public function parserUrl()
    {
        $path = dirname(__FILE__) . '/images';
        $html = file_get_contents($this->url);
        preg_match_all('/<img.*?src=["\'](.*?)["\'].*?>/i', $html, $images, PREG_SET_ORDER);

        $this->url = parse_url($this->url);
        $this->path = rtrim($path, '/');

        $this->downloadedImages($images);
    }


    /**
     * This method downloaded all images.
     *
     * @param $images
     */
    public function downloadedImages($images)
    {
        $path = $this->path;
        $url = $this->url;

        foreach ($images as $image) {
            if (strpos($image[1], 'data:image/') !== false) {
                continue;
            }

            if (substr($image[1], 0, 2) == '//') {
                $image[1] = 'http:' . $image[1];
            }

            $ext = strtolower(substr(strrchr($image[1], '.'), 1));
            if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
                $img = parse_url($image[1]);

                if (is_file($path . $img['path'])) {
                    continue;
                }

                $path_img = $path . '/' .  dirname($img['path']);
                if (!is_dir($path_img)) {
                    mkdir($path_img, 0777, true);
                }

                if (empty($img['host']) && !empty($img['path'])) {
                    copy($url['scheme'] . '://' . $url['host'] . $img['path'], $path . $img['path']);
                }
                elseif ($img['host'] == $url['host']) {
                    copy($image[1], $path . $img['path']);
                }
            }
        }
    }
}

$obj = new Parser($argv[1]);
$obj->parserUrl();

