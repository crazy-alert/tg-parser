<?php


class StorageSingleton
{
    private static ?StorageSingleton $instance = null;
    private array $AbstractList;
    private array $ApiTypeList;
    private array $MethodsList;
    public static function AddAbstractToList(AbstractObject $abstractObject):void {
        (self::getInstance())->AbstractList[$abstractObject->name] = $abstractObject;
    }
    public static function AddApiTypeList(BotApiType $botApiType):void {
        (self::getInstance())->ApiTypeList[$botApiType->name] = $botApiType;
    }
    public static function AddMethod(BotApiMethod $method):void {
        (self::getInstance())->MethodsList[$method->name] = $method;
    }
    static public function GetAbstractList():array|null{
        return self::getInstance()->AbstractList;
    }
    static public function GetApiTypeList():array|null{
        return self::getInstance()->ApiTypeList;
    }
    static public function GetMethodsList():array|null{
        return self::getInstance()->MethodsList;
    }
    static public function GetAbstractAndNonAbstractTypes():array|null{
        return array_merge(self::getInstance()->AbstractList, self::getInstance()->ApiTypeList);
    }
    private function __construct(){}
    private function __clone(){}

    private static function getInstance(): StorageSingleton{
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}



