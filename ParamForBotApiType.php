<?php
class ParamForBotApiType{
    public string $Field;
    public string $Type;
    public bool $IsOptional;
    public string $Description;


    public static Array $AllTypes = [];

    /**
     * @param string $Field
     * @param string $Type
     * @param bool $IsOptional
     * @param string $Description
     */
    public function __construct(string $Field, string $Type, bool $IsOptional, string $Description)
    {
        $this->Field = $Field;
        $this->Type = $Type;
        $this->IsOptional = $IsOptional;
        $this->Description = $Description;
    }
    public static function parseHtml($htmlWithTable):array{
        $Array = [];
        preg_match_all( '~<tbody>[\w|\W]+</tbody>~', $htmlWithTable, $matches );
        if(isset($matches[0][0])){
            $tbody  = $matches[0][0];
            $explodedTr = explode('<tr>', $matches[0][0]);
            foreach ($explodedTr AS $tr){
                $exploadedtd = explode('<td>', $tr);
                if(count($exploadedtd) > 3){
                    $Field = strip_tags($exploadedtd[1]);
                    $Type = strip_tags($exploadedtd[2]);
                    $Description = strip_tags($exploadedtd[3]);
                    if(str_contains($Description, 'Optional')){
                        $IsOptional = true;
                    }
                    else{
                        $IsOptional = false;
                    }

                    $Array[] = new static( $Field, $Type, $IsOptional, $Description);
                }

            }

        }
        return $Array;
    }
}