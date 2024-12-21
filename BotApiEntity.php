<?php


abstract class BotApiEntity{

    static string $ParserFolderName = 'TelegramApi';
    public  static ?string $EntityFolderName = null ;
    public string $name;
    public ?string $desc;
    public array $params;


    public function __construct(string $name, string|null $desc, array $params)
    {
        global $AbstractObjects;
        $this->name = $name;
        $this->desc = $desc;
        $this->params = $params;
    }

    abstract public function __Save(string $namespace, string $folder):bool;

}