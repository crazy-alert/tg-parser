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
        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL.$otstup;
        $data .= '/**  '.$this->desc;

        $childrens = AbstractObject::GetChildrens($this->name);
        if(is_array($childrens)){
            $data .= ' maybe: '.implode('|', $childrens);
        }
        $data .= ' */';
        $data .= PHP_EOL.$otstup;

        if(is_array($childrens)){
            $data .= $otstup.'abstract class '.$this->name.'{';
            $data .= PHP_EOL.$otstup;
            if($this->name == 'ChatMember'){
                $data .= 'abstract public function save($bot);';
                $data .= PHP_EOL.$otstup;
                $data .= '}'.PHP_EOL;
            }
        }
        else{
            $data .= 'readonly class '.$this->name;
            if($MyFather =  $this->GiveMeMyFather()){ $data .= ' extends '.$MyFather->name; }

            $otstup = '            ';
            $data .= '{'.PHP_EOL.PHP_EOL.$otstup;

            $data4constructor = $otstup.'public function __construct(array $input) {'.PHP_EOL.$otstup.'    ';
            foreach ($this->params AS $param){
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
                    $type .= $param->Type;

                    //это жуткий костыль
                    $line = explode(PHP_EOL, $data4constructor);//Разбиваем строку по пробелам
                    array_pop($line);//Удаляем последний элемент массива
                    $data4constructor = implode(PHP_EOL, $line).PHP_EOL.$otstup.'    ';//собираем строку пробелами

                    $AllTgTypes = StorageSingleton::GetAbstractAndNonAbstractTypes();
                    foreach (StorageSingleton::GetApiTypeList() as $keyObject => $object) {
                        if (array_key_exists($object->name, $AllTgTypes) AND ($AllTgTypes[$object->name] instanceof self)){
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
                            foreach ($AllTgTypes[$object->name]->params AS $keysubparam => $subparam){
                                if($keysubparam != 0 ){
                                    $RequariedKeysString .=', ';
                                }
                                $RequariedKeysString .= '\''.$subparam->Field.'\'=>\'\'';
                            }
                            $RequariedKeysString .= ']';

                            $data4constructor .= $otstup.'    ';
                            if($keyObject != 0 ){
                                $data4constructor .= 'else';
                            }
                            $data4constructor .= 'if(';
                            if($param->IsOptional){$data4constructor .= 'array_key_exists(\''.$param->Field.'\', $input) AND '; }
                            $data4constructor .= 'count(array_diff_key('.$RequariedKeysString.', $input[\''.$param->Field.'\'])) === 0){';
                        }

                        $data4constructor .= PHP_EOL.$otstup.'    '.'$this->'.$param->Field.' =  new '.$NamespacePath.$object->name.'($input[\''.$param->Field.'\']);'.PHP_EOL.$otstup.'    '.'}'.PHP_EOL;
                    }
                    if($param->IsOptional){
                        $data4constructor .=$otstup.'    '.'else{'.PHP_EOL.$otstup.$otstup.'$this->'.$param->Field.' =  NULL;'.PHP_EOL.$otstup.'    '.'}'.PHP_EOL;
                    }
                    else{
                        $data4constructor .=$otstup.'    '.'else{'.PHP_EOL.$otstup.$otstup.'Throw new \Exception(\'ашипко\');'.PHP_EOL.$otstup.'    '.'}'.PHP_EOL.PHP_EOL;
                    }
                }
                elseif(str_starts_with($param->Type, 'Array')){
                    $type .='array';
                    $data4constructor .=' $input["'.$param->Field.'"]';
                }
                elseif(array_key_exists($param->Type,  StorageSingleton::GetAbstractAndNonAbstractTypes())){
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
                $data .= PHP_EOL.$otstup;
                $data .= 'public '.$type;
                $data .=' $'.$param->Field.';'.PHP_EOL.PHP_EOL.$otstup;

//                $ArrayOfAbstractObjects = StorageSingleton::GetAbstractList();
//                if(array_key_exists($param->Type, $ArrayOfAbstractObjects)){
//                    if($param->IsOptional){
//                        $data4constructor .=' : NULL';
//                    }
//                    $data4constructor .= ';'.PHP_EOL;
//                }
                if($param->IsOptional){ $data4constructor .=' : NULL'; }
                $data4constructor .= ';'.PHP_EOL. $otstup.'    ';
            }
            $data .= PHP_EOL.PHP_EOL;
            $data .=$data4constructor.PHP_EOL. $otstup.'}'.PHP_EOL.'        }'.PHP_EOL;
        }
        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';
        $saved = file_put_contents($filename, $data);
        Log::getInstance()->Add('  '.$filename.' Saved:'.($saved?'YES':'NO'));
        return $saved;
    }
}