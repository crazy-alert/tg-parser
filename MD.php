<?php
readonly class MD{
    public string $title;
    public string  $body;
    /**
     * Превращаем HTML в MD
     *
     * @param string $html сам html
     * @param string $urlFoAbsolute ссылка для приведения относительных ссылок к абсолютному виду
     * @return string
     */
    public static function Html2Md(string $html, string $urlFoAbsolute):string
    {
        $data = preg_replace('/<h3><a[^>]*>(.*?)<\/a>(.*?)<\/h3>/', '# $2','<h3>'.$html);
        $data = preg_replace('/<h4>(.*?)<\/h4>/', '## $1',$data);
        $data = preg_replace('/<strong>(.*?)<\/strong>/', '__$1__',$data);
        $data = preg_replace('/<li>(.*?)<\/li>/', '* $1',$data);

        //делаем все ссылки абсолютными
        $baseUrl = rtrim($urlFoAbsolute, '/') . '/';
        $data = preg_replace_callback(
            pattern:    '/(href|src)=[\'"]?(?!https?:\/\/)(?!data:)([^\'" >]+)[\'"]?/i',
            callback:   function ($matches) use ($baseUrl) {
                // Составляем абсолютный URL
                $absoluteUrl = $baseUrl . ltrim($matches[2], '/');
                return $matches[1] . '="' . $absoluteUrl . '"';
            },
            subject:    $data
        );
        $data = str_replace('/bots/api/bots/', '/bots/', $data);
        // делаем все ссылки markdown
        $data = preg_replace_callback(
            '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/',
            function ($matches) {
                // $matches[1] — это URL ссылки, $matches[2] — текст ссылки
                return '[' . trim($matches[2]) . '](' . trim($matches[1]) . ')';
            },
            $data
        );

        return strip_tags($data);
    }

    public function __construct(string $title, string $html, string $urlFoAbsolute)
    {
        $this->title = $title;
        $this->body = static::Html2Md($html, $urlFoAbsolute);
    }
}