<?php
readonly class AbstractObject {
    public string $name;
    public array $chields;
    public string  $desc;

    public function __construct(string $name, ?array $chields, ?string $desc){
        $this->name   = $name;
        $this->chields   = $chields;
        $this->desc   = $desc;

    }

    public static function GetChildrens(string $name):array|false{
        $ArrayOfAbstarct = StorageSingleton::GetAbstractList();
        if(array_key_exists($name, $ArrayOfAbstarct) AND $ArrayOfAbstarct[$name] instanceof AbstractObject){
            if(is_array($ArrayOfAbstarct[$name]->chields)){
                return $ArrayOfAbstarct[$name]->chields;
            }

        }
        return false;

    }
    public function __Save(string $namespace, string $folder):bool{
        $otstup = '            ';
        $deascarray = explode('\n', $this->desc);

        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        $data .= '/**'.PHP_EOL.'*    '.implode(PHP_EOL.'*    ', explode("\n", $this->desc));
        $data .=PHP_EOL.'*/'.PHP_EOL;
        $data .= 'readonly abstract class '.$this->name.'{';


        $data .= PHP_EOL.'}';


        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';
        return file_put_contents($filename, $data);
    }




}