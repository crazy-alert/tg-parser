<?php


class AbstractObject {
    public readonly string $name;
    public readonly array $childers;
    public readonly string  $desc;

    public static function itIsAbstract(string $name):array|false {
        $array = ArrayOfAbstractObjects::getInstance()->GetList();
        if(is_array($array) AND array_key_exists($name, $array) AND ($array[$name] instanceof self)){
            return $array[$name]->childers;
        }
        return false;
    }
    public function __construct(string $name, ?array $childers, ?string $desc){
        $this->name   = $name;
        $this->childers   = $childers;
        $this->desc   = $desc;


//        $this->ObjectsList = [  'ChatMember' ,
//                                'MessageOrigin',
//                                'PaidMedia',
//                                'BackgroundFill',
//                                'BackgroundType',
//                                'ReactionType',
//                                'ChatBoostSource',
//                                'InputPaidMedia',
//                                'RevenueWithdrawalState',
//                                'PassportElementError',
//                                'InlineQueryResult',
//            ];


    }
    public function __Save(string $namespace, string $folder):bool
    {
        $otstup = '            ';
        $deascarray = explode('\n', $this->desc);

        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        $data .= '/**'.PHP_EOL.'*    '.implode(PHP_EOL.'*    ', explode("\n", $this->desc));
        $data .=PHP_EOL.'*/'.PHP_EOL;
        $data .= 'abstract class '.$this->name.'{'.PHP_EOL.'}';


        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';
       return file_put_contents($filename, $data);
    }
    static public function GetList():array{
        return ArrayOfAbstractObjects::getInstance()->GetList();
    }
    static public function Add(string $name, array $childrens, ?string $desc):void{
        ArrayOfAbstractObjects::Add($name, $childrens, $desc);
    }


}