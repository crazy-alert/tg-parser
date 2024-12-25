<?php


class ArrayOfAbstractObjects
{
    private static ?ArrayOfAbstractObjects $instance = null;
    private array $list;
    public static function Add(string $name, ?array $childrens, ?string $desc):void {
        (self::getInstance())->list[$name] = new AbstractObject($name,  $childrens, $desc);

    }
    public function GetList():array|null
    {
        return $this->list;
    }
    // Закрытый конструктор, чтобы предотвратить создание новых экземпляров
    private function __construct()
    {
        // Инициализация
    }

    // Закрытый метод клонирования, чтобы предотвратить клонирование экземпляра
    private function __clone(){}

    // Закрытый метод десериализации, чтобы предотвратить создание экземпляра через десериализацию
//    private function __wakeup(){}

    // Метод для получения единственного экземпляра класса
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

}



