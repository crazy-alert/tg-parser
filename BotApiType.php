<?php
class BotApiType extends BotApiEntity{

    /**
     * @var string|null
     */
    public static ?string $EntityFolderName = 'Types' ;

    public function __construct(string $name, ?string $desc, array $params){
        $this->name   = $name;
        $this->desc   = $desc;
        $this->params = $params;
    }
    private function GiveMeMyFather():AbstractObject|false {
        $ArrayOfAbstractObjects = StorageSingleton::GetAbstractList();
        foreach ($ArrayOfAbstractObjects AS $object){
            if(in_array($this->name, $object->chields)){
                return $object;
            }
        }
        return false;
    }
    public static function ParseHtmlForCreateType($html):static|null {

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


        $params =   ParamForBotApiType::parseHtml($html);


        $NewSelf = new static(
            name: $name,
            desc: $desc,
            params: $params,
        );

        StorageSingleton::AddApiTypeList($NewSelf);

        return $NewSelf;
    }
    public function __Save(string $namespace, string $folder):bool {
        $otstup = '        ';
        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.'//R45'.PHP_EOL.PHP_EOL.$otstup;
        $data .= '/**  '.$this->desc;

        $childrens = AbstractObject::GetChildrens($this->name);
        if(is_array($childrens)){
            $data .= ' maybe: '.implode('|', $childrens);
        }
        $data .= ' */';



        $data .= '//R23465B'.PHP_EOL.$otstup;
        if(is_array($childrens)){
            $data .= $otstup.'abstract class '.$this->name.'{';
            $data .= '//RTAE4VT43'.PHP_EOL.$otstup;
            if($this->name == 'ChatMember'){
                $data .= 'abstract public function save($bot);';
                $data .= '//RW34TW345TW'.PHP_EOL.$otstup;
                $data .= '}'.'//R45SRTH'.PHP_EOL;
            }
        }
        else{


            $otstup = '            ';
            $data .= 'readonly class '.$this->name;
            $data .= (($MyFather =  $this->GiveMeMyFather()) ? ' extends '.$MyFather->name : '').'{'.'//R45GSERG'.PHP_EOL.PHP_EOL.$otstup;

            $data4constructor = $otstup.'public function __construct(array $input) {';

            foreach ($this->params AS $param){
                $data4constructor .='//R45SERTSE'.PHP_EOL. $otstup.'    ';

                $NamespacePath = '\\'.$namespace.'\\';

                $data4constructor .='$this->'.$param->Field.' = ';
                if($param->IsOptional){
                    $data4constructor .=' array_key_exists("'.$param->Field.'", $input ) ? ';
                    $type ='null|';
                }
                else{
                    $type ='';
                }

                if($param->Type == 'Integer'){
                    $type .='int';
                    $data4constructor .='(int)$input["'.$param->Field.'"]';
                }
                elseif($param->Type == 'Integer or String'){
                    $type .='int';
                    $data4constructor .='(int)$input["'.$param->Field.'"]';
                }
                elseif($param->Type == 'Boolean'){
                    $type .='bool';
                    $data4constructor .='(bool)$input["'.$param->Field.'"]';
                }
                elseif($param->Type == 'String'){
                    $type .='string';
                    $data4constructor .='(string)$input["'.$param->Field.'"]';
                }
                elseif($param->Type == 'Float'){
                    $type .='float';
                    $data4constructor .='(float)$input["'.$param->Field.'"]';
                }
                elseif($param->Type == 'True'){
                    $type .='bool';
                    $data4constructor .='(bool)$input["'.$param->Field.'"]';
                }
                elseif(array_key_exists($param->Type, StorageSingleton::GetAbstractList())){ //если это абстрактный тип
//                    $param-> ParamForBotApiType(
//                                [Field] => old_chat_member
//                                [Type] => ChatMember
//                                [IsOptional] => false
//                                [Description] => Previous information about the chat member
//                            )
                    //это жуткий костыль

                    $line = explode(PHP_EOL, $data4constructor);//Разбиваем строку по пробелам
                    array_pop($line);//Удаляем последний элемент массива
                    $data4constructor = implode(PHP_EOL, $line).'//R45AEWRTWE45T'.PHP_EOL;//собираем строку пробелами

                    $type .= $param->Type;

                    $ApiTypeList = StorageSingleton::GetApiTypeList();
                    $AbstractList = StorageSingleton::GetAbstractList();
                    $AbstractType = $AbstractList[$param->Type];
                    $Chields = $AbstractType->chields;
                    foreach($Chields AS $keychield => $chield){
                        $ThisType = $ApiTypeList[$chield];
                        /*$ThisType => BotApiType::(  'name' => 'MessageOriginUser',
                                                    'desc' => 'The message was originally sent by a known user.',
                                                    'params' => array ( 0 => \ParamForBotApiType::( 'Field' => 'type',
                                                                                                    'Type' => 'String',
                                                                                                    'IsOptional' => false,
                                                                                                    'Description' => 'Type of the message origin, always “user”',),
                                                                        1 => \ParamForBotApiType::( 'Field' => 'date',
                                                                                                    'Type' => 'Integer',
                                                                                                    'IsOptional' => false,
                                                                                                    'Description' => 'Date the message was sent originally in Unix time', ),
                                                                       2 => \ParamForBotApiType::( 'Field' => 'sender_user',
                                                                                                    'Type' => 'User',
                                                                                                    'IsOptional' => false,
                                                                                                    'Description' => 'User that sent the message originally',), ), ))*/
                        $data4constructor .= '                ';
                        if($keychield !=0){$data4constructor .='else';}
                        $data4constructor .= 'if(array_key_exists("'.$param->Field.'", $input) AND';
                        $data4constructor .= ' is_array($input["'.$param->Field.'"]) '.'//R4TYYYY5'.PHP_EOL;
                        foreach ($ThisType->params AS $keysubparam => $subparam){
                            if($subparam->IsOptional){ continue;}
//                            if($keysubparam != 0 ){ $data4constructor .= '                   AND ';}
                            $data4constructor .= '                   AND ';

                            $data4constructor .= 'array_key_exists(\''.$subparam->Field.'\', $input["'.$param->Field.'"])';
                            if(str_contains(mb_strtolower($subparam->Description), 'always')){
                                 preg_match_all('/`(.*?)`/', str_replace(['“', '”', '\'', '"'], '`', $subparam->Description), $matches);

                                if($subparam->Type === 'String' AND $matches[1][0] !== null){
                                    $data4constructor .= ' AND $input["'.$param->Field.'"]["'.$subparam->Field.'"] === \''.$matches[1][0].'\'';
                                }
                                elseif(str_contains(mb_strtolower($subparam->Description), 'always a positive number')){
                                    $data4constructor .= ' AND (int)$input["'.$param->Field.'"]["'.$subparam->Field.'"] > 0';
                                }
                                elseif(str_contains(mb_strtolower($subparam->Description), 'always 0.')){
                                    $data4constructor .= ' AND (int)$input["'.$param->Field.'"]["'.$subparam->Field.'"] == \'0\'';
                                }
                                else{
                                    $r = 0;
                                }

                            }

                            $data4constructor .='// '.$subparam->Description.PHP_EOL;


                        }
                        $data4constructor .= '                ){'.'//R4WE5YWE45YWE455'.PHP_EOL;
                        $data4constructor .= '                    $this->'.$param->Field.' = new '.$ThisType->name.'($input["'.$param->Field.'"]);';
                        $data4constructor .= '//R44TW45TYW45YT5'.PHP_EOL;
                        $data4constructor .= '                }';
                        $data4constructor .= '//R45WE45YEW45YE'.PHP_EOL;
                    }





                }
                elseif(str_starts_with($param->Type, 'Array')){
                    $type .='array';
                    $data4constructor .=' $input["'.$param->Field.'"]';
                }
                elseif(array_key_exists($param->Type,  StorageSingleton::GetApiTypeList())){
                    $type .=$param->Type;
                    $data4constructor .=' new '.$NamespacePath.$param->Type.'($input["'.$param->Field.'"])';
                }
                elseif($param->Type == 'InputFile or String'){
                    $type .='string';
                    $data4constructor .='(string)$input["'.$param->Field.'"]';
                }
                else{
                    Log::getInstance()->Add('Не получлось обработать: '.$param->Type.' line '.__LINE__.' file:'.__FILE__);
                    return false;
                }

                /** @var $param ParamForBotApiType */
                $data .= '/**   @var $'.$param->Field.' '.$type.' ('.$param->Type.') '.$param->Description.' */';
                $data .= '//R4E45Y4R5YER5Y5'.PHP_EOL.$otstup;
                $data .= 'public '.$type;
                $data .=' $'.$param->Field.';'.'//R434Q5W43TSERG5'.PHP_EOL.PHP_EOL.$otstup;



                //здесь добавляем else{
                //                     $this->forward_origin = NULL;
                //                }
                //                ИЛИ : NULL;
                if($param->IsOptional ){
                    if(array_key_exists($param->Type,  StorageSingleton::GetAbstractList())){
                        $data4constructor .= '                else{'.'//R459OY89OYUK'.PHP_EOL;
                        $data4constructor .= '                     $this->'.$param->Field.' = NULL;'.'//R45AFW34RW345YX'.PHP_EOL;
                        $data4constructor .= '                }'.'//RZSDGSE5Y45'.PHP_EOL;
                    }
                    else{
                        $data4constructor .=' : NULL;';
                    }

                }
                else{
                    $data4constructor .= ';';
                }

            }
            $data .= '//RA4WTAE4TWE45'.PHP_EOL.PHP_EOL;
            $data .=$data4constructor.'//RQ234R34WR3FAERGF45'.PHP_EOL. $otstup.'}'.'//A4TE54TR45'.PHP_EOL.'        }'.'//R44TAE4TGZDFG5'.PHP_EOL;
        }
        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';
        $saved = file_put_contents($filename, $data);
        Log::getInstance()->Add('  '.$filename.' Saved:'.($saved?'YES':'NO'));
        return $saved;
    }
}