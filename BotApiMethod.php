<?php
class BotApiMethod extends BotApiEntity {
    public function __construct(string $name, string|null $desc, array $params)
    {
        $this->name = $name;
        $this->desc = $desc;
        $this->params = $params;

    }



    /**
     * @throws Exception
     */
    public static function parseHtml($html):static|null
    {
        $html = str_replace(array("\r", "\n"), '', $html);

        preg_match_all( '~</a>[\w\s]+</h4>~', $html, $matches );
        if(!isset($matches[0][0])){
            Throw new Exception('Не найден name of Class');
        }
        $name  = strip_tags($matches[0][0]);

        preg_match_all( '~<p>.+</p>~', $html, $matches );
        if(isset($matches[0][0])){
            $desc  = strip_tags($matches[0][0]);
        }
        else{
            $desc  = null;
        }



        $params = [];
        preg_match_all('/<table.*?>(.*?)<\/table>/is', $html, $tableMatches);
        if(isset($tableMatches[1][0])){
            $table = $tableMatches[1][0];
            preg_match_all('/<tbody.*?>(.*?)<\/tbody>/is', $table, $tbodyMatches);
            if(isset($tbodyMatches[1][0])){
                $tbody = $tbodyMatches[1][0];
                preg_match_all('/<tr.*?>(.*?)<\/tr>/is', $tbody, $trMatches);
                if(isset($trMatches[1]) AND is_array($trMatches[1])) {
                    foreach ($trMatches[1] as $tr) {
                        preg_match_all('/<td.*?>(.*?)<\/td>/is', $tr, $tdMatches);
                        if(isset($tdMatches[1])){
                            $params[] = new ParamForBotApiMethods(  Parameter: $tdMatches[1][0],
                                Type:strip_tags($tdMatches[1][1]) ,
                                Required:$tdMatches[1][2],
                                Description:$tdMatches[1][3]
                            );

                        }
                    }
                }
            }
        }


        $newMethod =  new self(
            name: $name,
            desc: $desc,
            params: $params
        );

        StorageSingleton::AddMethod($newMethod);

        return $newMethod;
    }
    public function __Save(string $namespace, string $folder): bool{

        $DOCBlockForClass = $this->desc;
        $dataImport = '';

        if(static::$EntityFolderName != null){
            $folder .= DIRECTORY_SEPARATOR.static::$EntityFolderName;
            $namespace .= '\\'.static::$EntityFolderName;
        }

        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';


        $docBlockForConstruct = '        /**'.PHP_EOL;

        $data4constructor = '      public function __construct( '.PHP_EOL;
        if(count($this->params)>0){
            foreach ($this->params AS $param ){

                if($param->Type == 'String'){
                    $typeStr = 'string';
                }
                elseif ($param->Type == 'Boolean'){
                    $typeStr = 'bool';
                }
                elseif ($param->Type == 'Integer'){
                    $typeStr = 'int';
                }
                elseif ($param->Type == 'Integer or String'){
                    $typeStr = 'int';
                }
                elseif ($param->Type == 'Float'){
                    $typeStr = 'float|int';
                }
                elseif (str_starts_with($param->Type, 'Array of')){
                    $typeStr = 'array';
                }
                elseif (array_key_exists($param->Type, BotApiType::GetAllTgTypes())){
                    $typeStr = $param->Type;
                    $dataImport .= 'use \\'.BotApiType::$ParserFolderName.'\\'.BotApiType::$EntityFolderName.'\\'.$typeStr.';'.PHP_EOL;


                }
                elseif ($param->Type == 'InlineKeyboardMarkup or ReplyKeyboardMarkup or ReplyKeyboardRemove or ForceReply'){
                    $newType = 'InlineKeyboardMarkup';
                    $dataImport .= 'use \\'.BotApiType::$ParserFolderName.'\\'.BotApiType::$EntityFolderName.'\\'.$newType.';'.PHP_EOL;
                    $typeStr =  $newType;
                    $typeStr .= '|';

                    $newType = 'ReplyKeyboardMarkup';
                    $dataImport .= 'use \\'.BotApiType::$ParserFolderName.'\\'.BotApiType::$EntityFolderName.'\\'.$newType.';'.PHP_EOL;
                    $typeStr .=  $newType;
                    $typeStr .= '|';

                    $newType = 'ReplyKeyboardRemove';
                    $dataImport .= 'use \\'.BotApiType::$ParserFolderName.'\\'.BotApiType::$EntityFolderName.'\\'.$newType.';'.PHP_EOL;
                    $typeStr .=  $newType;
                    $typeStr .= '|';

                    $newType = 'ForceReply';
                    $dataImport .= 'use \\'.BotApiType::$ParserFolderName.'\\'.BotApiType::$EntityFolderName.'\\'.$newType.';'.PHP_EOL;
                    $typeStr .=  $newType;

                }
                elseif (str_ends_with($param->Type, ' or String')){
                    $typeStr = 'string';
                }
                elseif($param->Type == 'InputFile'){
                    $typeStr = 'string';
                }
                elseif($param->Type == 'BotCommandScope'){
                    $typeStr = 'string';
                }
                else{
                    $typeStr = 'string //но это не точно';
//                       Throw new Exception('Такого мы ждали. Очень неожиданно');
                }

                $data4constructor .= '         '.$typeStr.' $'.$param->Parameter;
                if($param->Required == 'Optional'){
                    $data4constructor .=' = NULL';
                    $AddQuot = '?';
                }
                else{
                    $AddQuot = '';
                }
                $docBlockForConstruct .= '        * @param $'.$param->Parameter.' '.$AddQuot.$typeStr.' '.$param->Required.' '.$param->Description.PHP_EOL;
                $data4constructor .= ','.PHP_EOL;

            }
            $docBlockForConstruct .= '        */'.PHP_EOL;

        }
        $data4constructor .= '         ) {'.PHP_EOL;
        if(count($this->params)>0){
            foreach ($this->params AS $param ){
                $data4constructor .= '                  $this->parameters[\''.$param->Parameter.'\'] = ';
                $data4constructor .= '$'.$param->Parameter.' ;'.PHP_EOL;
            }

        }
        else{
            $data4constructor .= '                  $this->parameters = [];'.PHP_EOL;
        }


        $data4constructor .= '      }'.PHP_EOL;


        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        $data .= $dataImport;
        $data .= '/**'.PHP_EOL;
        $data .= '* '. $DOCBlockForClass.PHP_EOL;
        $data .= '*/'.PHP_EOL;
        $data .= 'readonly class '.$this->name.'{'.PHP_EOL;
        $data .= '      public array $parameters;'.PHP_EOL;


        if(count($this->params)>0){
            $data .= $docBlockForConstruct;
        }

        $data .= $data4constructor ;
        $data .= '}';

        return file_put_contents($filename, $data);
    }

    public function __ToString():string{
        $otstup = '    ';
        $ArgumentForFunction = '';
        $ArrayParamsForSend = $otstup.'$parameters = [];'.PHP_EOL;
        $return ='/**'.PHP_EOL;
        $return .=' * '. $this->desc.PHP_EOL;




        $count = 0;
        foreach ($this->params as $param) {
            /**
             * @var \ParamForBotApiMethods $param
             */

            if($param->Type == 'String'){
                $typeStr = 'string';
            }
            elseif ($param->Type == 'Boolean'){
                $typeStr = 'bool';
            }
            elseif ($param->Type == 'Integer'){
                $typeStr = 'int';
            }
            elseif ($param->Type == 'Integer or String'){
                $typeStr = 'int';
            }
            elseif ($param->Type == 'Float'){
                $typeStr = 'float|int';
            }
            elseif (str_starts_with($param->Type, 'Array of')){
                $typeStr = 'array';
            }
            elseif (array_key_exists($param->Type, StorageSingleton::GetAbstractAndNonAbstractTypes())){
                $typeStr = $param->Type;
            }
            elseif( $param->Type == 'InlineKeyboardMarkup or ReplyKeyboardMarkup or ReplyKeyboardRemove or ForceReply'){
                $typeStr = 'InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply';
            }
            elseif($param->Type =='InputFile or String'){
                $typeStr = 'string';
            }
            elseif($param->Type == 'MenuButton'){
                $typeStr = 'string';
            }
            else{
                Throw new Exception('Передан неизвестный тип аргумента');
            }


            if($count != 0 ){
                $ArgumentForFunction .= ', ';
            }

            $return .= ' * @param '.$typeStr;

            $ArgumentForFunction .= $typeStr.' $'.$param->Parameter;
            if($param->Required === 'Optional'){
//                $ArgumentForFunction .= ' ?';
                $return .= '|null ';
                $ArgumentForFunction .= ' = NULL';
            }


            $return .= ' $'.$param->Parameter.' '.$param->Description.PHP_EOL;

            $ArrayParamsForSend .= $otstup.'if($'.$param->Parameter.' != NULL){ $parameters[\''.$param->Parameter.'\'] = $'.$param->Parameter.' ;}'.PHP_EOL;
            

            $count++;

        }




        $return .= ' */'.PHP_EOL;
        $return .= 'public function '.$this->name.'('.$ArgumentForFunction.'):array{'.PHP_EOL;
        $return .= $ArrayParamsForSend;
        $return .= PHP_EOL.$otstup.'return $this->send(\''.$this->name.'\', $parameters );';
        $return .= PHP_EOL.'}'.PHP_EOL;



        return $return;
    }
}