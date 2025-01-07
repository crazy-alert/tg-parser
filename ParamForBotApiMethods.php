<?php


class ParamForBotApiMethods {
    public string $Parameter;
    public string $Type;
    public string $Required;
    public string $Description;
    public function __construct($Parameter, $Type, $Required, $Description)
    {
        $this->Parameter = $Parameter;
        $this->Type = $Type;
        $this->Required = $Required;
        $this->Description = $Description;
    }


}