<?php
class BotApiType extends BotApiEntity{

    /**
     * @var string|null
     */
    public static ?string $EntityFolderName = 'Types' ;
    /**
     * @var array
     */
    public static Array $AllTgTypes = [];
    public static function AddAllTgTypes(self $Type): void {
        static::$AllTgTypes [$Type->name] = $Type;
    }

    private function __construct(string $name, ?string $desc, array $params)
    {
        $this->name   = $name;
        $this->desc   = $desc;
        $this->params = $params;

        static::AddAllTgTypes($this);
    }
    public static function GetAllTgTypes():array
    {
        return self::$AllTgTypes;
    }
    public static function createType($html):static|null {

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

        return new static(
            name: $name,
            desc: $desc,
            params: $params,
        );
    }
    public function __Save(string $namespace, string $folder):bool {
        $otstup = '            ';

        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        $data .= '/**'.PHP_EOL.'*    '.$this->desc;
        if(is_array($IsAbstract = AbstractObject::itIsAbstract($this->name))){
            $data .= '*    maybe: '.implode('|', $IsAbstract);
        }
        $data .=PHP_EOL.'*/'.PHP_EOL;



        if(is_array($IsAbstract)){
            $data .= 'abstract class '.$this->name.'{'.PHP_EOL;
            if($this->name == 'ChatMember'){
                $data .= '            abstract public function save($bot);'.PHP_EOL;
                $data .= PHP_EOL.'}'.PHP_EOL;
            }

        }
        else{
//            Throw new Exception('Тут нужно упоямнуть что расширяем асбстрактный класс елси да');

            $data .= 'readonly class '.$this->name;
            if($MyFather =  AbstractObject::GiveMeMyFather($this->name)){
                $data .= ' extends '.$MyFather->name;
            }

            $data .= '{'.PHP_EOL;
            $data4constructor = 'public function __construct(array $input) {'.PHP_EOL;
            foreach ($this->params AS $param){

                $NamespacePath = '\\'.$namespace.'\\';

                $data4constructor .=$otstup.'$this->'.$param->Field.' = ';
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
                    $type .='true';
                    $data4constructor .='(bool)$input["'.$param->Field.'"]';
                }
                elseif(is_array($ArrayOfPossibleTypes = AbstractObject::itIsAbstract($param->Type))){ //если это абстрактный тип
                    $AllTgTypes = self::$AllTgTypes;
                    $type .= $param->Type;

                    //это жуткий костыль
                    $line = explode(PHP_EOL, $data4constructor);//Разбиваем строку по пробелам
                    array_pop($line);//Удаляем последний элемент массива
                    $data4constructor = implode(PHP_EOL, $line).PHP_EOL;//собираем строку пробелами

                    foreach ($ArrayOfPossibleTypes as $keyObject => $object) {

                        if (array_key_exists($object, $AllTgTypes) AND ($AllTgTypes[$object] instanceof self)){
//                            $data4constructor .= $otstup;
//                            if($keyObject != 0 ){
//                                $data4constructor .= 'else';
//                            }
//                            $data4constructor .= 'if(';
//                            foreach ($AllTgTypes[$object]->params AS $keysubparam => $subparam){
//                                if($keysubparam != 0 ){
//                                    $data4constructor .=' AND '.PHP_EOL. $otstup.'   ';
//                                }
//                                $data4constructor .=' array_key_exists(\''.$subparam->Field.'\', $input) ';
//                            }

                            $RequariedKeysString = '[';
                            foreach ($AllTgTypes[$object]->params AS $keysubparam => $subparam){
                                if($keysubparam != 0 ){
                                    $RequariedKeysString .=', ';
                                }
                                $RequariedKeysString .= '\''.$subparam->Field.'\'';
                            }
                            $RequariedKeysString .= ']';

                            $data4constructor .= $otstup;
                            if($keyObject != 0 ){
                                $data4constructor .= 'else';
                            }
                            $data4constructor .= 'if(count(array_diff_key('.$RequariedKeysString.', $input)) === 0){';
                        }

                        $data4constructor .= PHP_EOL.$otstup.$otstup.'$this->'.$param->Field.' =  new '.$NamespacePath.$object.'($input);'.PHP_EOL.$otstup.'}'.PHP_EOL;
                    }
                    if($param->IsOptional){
                        $data4constructor .=$otstup.'else{'.PHP_EOL.$otstup.$otstup.'$this->'.$param->Field.' =  NULL;'.PHP_EOL.$otstup.'}'.PHP_EOL;
                    }
                }
                elseif(str_starts_with($param->Type, 'Array')){
                    $type .='array';
                    $data4constructor .=' $input["'.$param->Field.'"]';
                }
                elseif(array_key_exists($param->Type,  BotApiType::GetAllTgTypes())){
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

                /**
                 * @var $param ParamForBotApiType
                 */
                $data .= '/**'.PHP_EOL.'* @var $'.$param->Field.' '.$type;
                $data .=' ('.$param->Type.') '.$param->Description.PHP_EOL.'*/'.PHP_EOL;
                $data .= 'public '.$type;
                $data .=' $'.$param->Field.';'.PHP_EOL;
                if(!is_array($ArrayOfPossibleTypes = AbstractObject::itIsAbstract($param->Type))){
                    if($param->IsOptional){
                        $data4constructor .=' : NULL';
                    }
                    $data4constructor .= ';'.PHP_EOL;
                }


            }
            $data .= PHP_EOL.PHP_EOL;
            $data .=$data4constructor.PHP_EOL.'}';
            $data .= PHP_EOL.'}'.PHP_EOL;
        }

        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';
        $saved = file_put_contents($filename, $data);
//        Log::getInstance()->Add('file '.$filename.' Saved:'.($saved?'YES':'NO'));
        return $saved;
    }
}