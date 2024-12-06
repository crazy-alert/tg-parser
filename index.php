<?php
class Log {
    private string $log;
    public function Add(mixed $log){
        $this->log .= $log.PHP_EOL;
    }
    public function Echo()    {
        echo $this->log;
    }
    private static ?Log $instance = null;
    public static function getInstance(): Log
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()    {
        $this->log = '';
    }
    private function __clone()    {
    }

}



$url = 'https://core.telegram.org/bots/api';

/*
 FOR ABSTARCT

                ChatMember
This object contains information about one member of a chat. Currently, the following 6 types of chat members are supported:
                ChatMemberOwner
                ChatMemberAdministrator
                ChatMemberMember
                ChatMemberRestricted
                ChatMemberLeft
                ChatMemberBanned

                MessageOrigin
This object describes the origin of a message. It can be one of
        MessageOriginUser
        MessageOriginHiddenUser
        MessageOriginChat
        MessageOriginChannel
        MessageOriginUser

                PaidMedia
This object describes paid media. Currently, it can be one of
            PaidMediaPreview
            PaidMediaPhoto
            PaidMediaVideo

    BackgroundFill
This object describes the way a background is filled based on the selected colors. Currently, it can be one of
    BackgroundFillSolid
    BackgroundFillGradient
    BackgroundFillFreeformGradient

    BackgroundType
This object describes the type of a background. Currently, it can be one of
    BackgroundTypeFill
    BackgroundTypeWallpaper
    BackgroundTypePattern
    BackgroundTypeChatTheme

        ReactionType
This object describes the type of a reaction. Currently, it can be one of
        ReactionTypeEmoji
        ReactionTypeCustomEmoji
        ReactionTypePaid

            ChatBoostSource
This object describes the source of a chat boost. It can be one of
        ChatBoostSourcePremium
        ChatBoostSourceGiftCode
        ChatBoostSourceGiveaway


        InputPaidMedia
This object describes the paid media to be sent. Currently, it can be one of
    InputPaidMediaPhoto
    InputPaidMediaVideo

            RevenueWithdrawalState
This object describes the state of a revenue withdrawal operation. Currently, it can be one of
    RevenueWithdrawalStatePending
    RevenueWithdrawalStateSucceeded
    RevenueWithdrawalStateFailed

        TransactionPartner
This object describes the source of a transaction, or its recipient for outgoing transactions. Currently, it can be one of
        TransactionPartnerUser
        TransactionPartnerFragment
        TransactionPartnerTelegramAds
        TransactionPartnerOther

        PassportElementError
This object represents an error in the Telegram Passport element which was submitted that should be resolved by the user. It should be one of:
        PassportElementErrorDataField
        PassportElementErrorFrontSide
        PassportElementErrorReverseSide
        PassportElementErrorSelfie
        PassportElementErrorFile
        PassportElementErrorFiles
        PassportElementErrorTranslationFile
        PassportElementErrorTranslationFiles
        PassportElementErrorUnspecified

        InlineQueryResult
This object represents one result of an inline query. Telegram clients currently support results of the following 20 types:
        InlineQueryResultCachedAudio
        InlineQueryResultCachedDocument
        InlineQueryResultCachedGif
        InlineQueryResultCachedMpeg4Gif
        InlineQueryResultCachedPhoto
        InlineQueryResultCachedSticker
        InlineQueryResultCachedVideo
        InlineQueryResultCachedVoice
        InlineQueryResultArticle
        InlineQueryResultAudio
        InlineQueryResultContact
        InlineQueryResultGame
        InlineQueryResultDocument
        InlineQueryResultGif
        InlineQueryResultLocation
        InlineQueryResultMpeg4Gif
        InlineQueryResultPhoto
        InlineQueryResultVenue
        InlineQueryResultVideo
        InlineQueryResultVoice
*/
$AbstractObjects = [
    'ChatMember',
    'MessageOrigin',
    'PaidMedia',
    'BackgroundFill',
    'BackgroundType',
    'ReactionType',
    'ChatBoostSource',
    'InputPaidMedia',
    'RevenueWithdrawalState',
    'PassportElementError',
];
function HTMLl2MD(string $html, string $urlFoAbsolute):string{

    $data = preg_replace('/<h3><a[^>]*>(.*?)<\/a>(.*?)<\/h3>/', '# $2','<h3>'.$html);
    $data = preg_replace('/<h4>(.*?)<\/h4>/', '## $1',$data);
    $data = preg_replace('/<strong>(.*?)<\/strong>/', '__$1__',$data);
    $data = preg_replace('/<li>(.*?)<\/li>/', '* $1',$data);

    //делаем все ссылки абсолютными
    $baseUrl = rtrim($urlFoAbsolute, '/') . '/';
    $data = preg_replace_callback(
        pattern:    '/(href|src)=[\'"]?(?!https?:\/\/)(?!data:)([^\'" >]+)[\'"]?/i',
        callback:   function ($matches) use ($baseUrl) {
            // Составляем абсолютный URL
            $absoluteUrl = $baseUrl . ltrim($matches[2], '/');
            return $matches[1] . '="' . $absoluteUrl . '"';
        },
        subject:    $data
    );
    $data = str_replace('/bots/api/bots/', '/bots/', $data);
    // делаем все ссылки markdown
    $data = preg_replace_callback(
        '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/',
        function ($matches) {
            // $matches[1] — это URL ссылки, $matches[2] — текст ссылки
            return '[' . trim($matches[2]) . '](' . trim($matches[1]) . ')';
        },
        $data
    );

    return strip_tags($data);
}
function convertToCamelCase(string $string):string {
    // Удаляем лишние пробелы
    $string = trim($string);

    // Разбиваем строку на слова
    $words = explode(' ', $string);

    // Преобразуем каждое слово к формату UpperCamelCase
    $words = array_map('ucfirst', $words);

    // Объединяем слова в одну строку
    return implode('', $words);
}
abstract class BotApiEntity{
    protected array $NotForSave ;
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
        $this->NotForSave = array_merge( $AbstractObjects, ['Accent colors', 'Profile accent colors', 'Inline mode objects', 'Sending files']);
    }

    abstract public function __Save():bool;

}
class BotApiType extends BotApiEntity
{
    /**
     * @var string|null
     */
    public static ?string $EntityFolderName = 'Types' ;
    /**
     * @var array
     */
    public static Array $AllTgTypes = [];
    public static function GetAllTgTypes():array
    {
        return self::$AllTgTypes;
    }

    public static function parseHtml($html):static|null
    {
        $html = str_replace(array("\r", "\n"), '', $html);
        try {
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

            self::$AllTgTypes[] = $name;
            return new static(  name: $name,
                                desc: $desc,
                                params: ParamForBotApiType::parseHtml($html)
                        );
        }
        catch (Throwable $e){
            return null;
        }
    }
    public function __Save():bool {
        if(in_array($this->name, $this->NotForSave)){
            return false;
        }
//
        $folder = self::$ParserFolderName;
        $namespace = $folder;
        if(!file_exists(static::$ParserFolderName)){
            mkdir(self::$ParserFolderName);
        }
        if(static::$EntityFolderName != null){
            $folder .= DIRECTORY_SEPARATOR.static::$EntityFolderName;
            $namespace .= '\\'.static::$EntityFolderName;
            if(!file_exists($folder)){
                mkdir($folder);
            }
        }

        $filename = $folder.DIRECTORY_SEPARATOR.$this->name.'.php';



        $data = '<?php'.PHP_EOL.PHP_EOL.'namespace '.$namespace.';'.PHP_EOL.PHP_EOL;
        $data .= '/**'.PHP_EOL.'*    '.$this->desc.PHP_EOL.'*/'.PHP_EOL;
        $data .= 'readonly class '.$this->name.'{'.PHP_EOL;

        $data4constructor = 'public function __construct(array $input) {'.PHP_EOL;

        foreach ($this->params AS $param){

            $data4constructor .='            $this->'.$param->Field.' = ';
            if($param->IsOptional){
                $data4constructor .=' array_key_exists("'.$param->Field.'", $input ) ? ';
                $type ='?';
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
            elseif(str_starts_with($param->Type, 'Array')){
                $type .='array';
                $data4constructor .=' $input["'.$param->Field.'"]';
            }
            elseif(in_array($param->Type, static::GetAllTgTypes())){
                $type .=$param->Type;
                $data4constructor .=' new \\'.$namespace.'\\'.$param->Type.'($input["'.$param->Field.'"])';
            }
            elseif($param->Type == 'InputFile or String'){
                $type .='string';
                $data4constructor .='(string)$input["'.$param->Field.'"]';
            }
            else{
                Throw new Exception('Упс! Неизвестный тип: '.$param->Type.', параметра: '.$param->Field.', в классе: '.$this->name);
            }

            /**
             * @var $param ParamForBotApiType
             */
            $data .= '/**'.PHP_EOL.'* @var $'.$param->Field.' '.$type;
            $data .=' ('.$param->Type.') '.$param->Description.PHP_EOL.'*/'.PHP_EOL;
            $data .= 'public '.$type;
            $data .=' $'.$param->Field.';'.PHP_EOL;
            if($param->IsOptional){
                $data4constructor .=' : NULL';
            }
            $data4constructor .= ';'.PHP_EOL;
        }
        $data .= PHP_EOL.PHP_EOL;
        $data .=$data4constructor.PHP_EOL.'}';
        $data .= PHP_EOL.'}'.PHP_EOL;

        $log = Log::getInstance();

        $saved = file_put_contents($filename, $data);
        $log->Add('file '.$filename.' Saved:'.($saved?'YES':'NO'));
        return $saved;
    }
}
class ParamForBotApiType{
    public string $Field;
    public string $Type;
    public bool $IsOptional;
    public string $Description;


    public static Array $AllTypes = [];

    /**
     * @param string $Field
     * @param string $Type
     * @param bool $IsOptional
     * @param string $Description
     */
    public function __construct(string $Field, string $Type, bool $IsOptional, string $Description)
    {

        $this->Field = $Field;
        $this->Type = $Type;
        $this->IsOptional = $IsOptional;
        $this->Description = $Description;

        static::$AllTypes[] = $Type;

    }
    public static function GetAllTypes():array
    {
        return array_unique(static::$AllTypes);
    }

    public static function parseHtml($htmlWithTable):array{
        $Array = [];
        preg_match_all( '~<tbody>[\w|\W]+</tbody>~', $htmlWithTable, $matches );
        if(isset($matches[0][0])){
            $tbody  = $matches[0][0];
            $explodedTr = explode('<tr>', $matches[0][0]);
            foreach ($explodedTr AS $tr){
                $exploadedtd = explode('<td>', $tr);
                if(count($exploadedtd) > 3){
                    $Field = strip_tags($exploadedtd[1]);
                    $Type = strip_tags($exploadedtd[2]);
                    $Description = strip_tags($exploadedtd[3]);
                    if(str_contains($Description, 'Optional')){
                        $IsOptional = true;
                    }
                    else{
                        $IsOptional = false;
                    }

                    $Array[] = new static( $Field, $Type, $IsOptional, $Description);
                }

            }

        }
        return $Array;
    }
}
class MethodForBotApi extends BotApiEntity {
    public function __construct(string $name, string|null $desc, array $params)
    {
        global $AbstractObjects;
        $this->name = $name;
        $this->desc = $desc;
        $this->params = $params;
        $this->NotForSave = array_merge( $AbstractObjects,  ['Formatting options', 'Inline mode methods']);
    }


    public static array $AllTgMethods = [];
    public static ?string $EntityFolderName = 'Methods' ;
    public static function GetAllTgMethods():array
    {
        return self::$AllTgMethods;
    }
    public static function parseHtml($html):static|null
    {
        $html = str_replace(array("\r", "\n"), '', $html);
        try {
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


            self::$AllTgMethods[] = $name;
            return new static(  name: $name,
                desc: $desc,
                params: $params
            );
        }
        catch (Throwable $e){
            return null;
        }
    }
    public function __Save(): bool{

        if(in_array($this->name, $this->NotForSave)){
            return false;
        }

        $folder = self::$ParserFolderName;
        $namespace = $folder;
        $DOCBlockForClass = $this->desc;
        $dataImport = '';
        if(!file_exists(static::$ParserFolderName)){
            mkdir(self::$ParserFolderName);
        }
        if(static::$EntityFolderName != null){
            $folder .= DIRECTORY_SEPARATOR.static::$EntityFolderName;
            $namespace .= '\\'.static::$EntityFolderName;
            if(!file_exists($folder)){
                mkdir($folder);
            }
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
                    elseif (in_array($param->Type, BotApiType::GetAllTgTypes())){
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
                    else{
                       Throw new Exception('Такого мы ждали. Очень неожиданно');
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
}
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

    public static Array $AllTypes = [];
}
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);
curl_close($ch);

$Types = [];
$Methods = [];
$h3exploded = explode('<h3>', $html);
foreach ($h3exploded as $item => $h3){
    preg_match_all( '~</a>[\w\s]+</h3>~', $h3, $h3item );
    if(isset($h3item[0][0])){
        $H3[strip_tags($h3item[0][0])] = $h3;
    }

}

foreach ($H3 AS $key => $value){

    if($key == 'Recent changes' OR $key == 'Authorizing your bot' OR $key == 'Making requests' OR $key == 'Using a Local Bot API Server'){
        $data = HTMLl2MD('<h3>'.$value, $url);
        if(!file_exists(BotApiEntity::$ParserFolderName)){
            mkdir(BotApiEntity::$ParserFolderName);
        }
        $file = BotApiEntity::$ParserFolderName.DIRECTORY_SEPARATOR.convertToCamelCase($key).'.md';
        file_put_contents($file, $data);
    }
    elseif ($key == 'Available methods' OR $key == 'Updating messages'){
        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            if($newMethod = MethodForBotApi::parseHtml($h4)){
                $Methods[] = $newMethod;
            }
        }

    }
    elseif($key == 'Available types'){
        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            if($newType = BotApiType::parseHtml($h4)){
                $Types[] = $newType;
            }
        }
    }
    elseif($key == 'Stickers'){
        $TypesOfStickers = [ 'Sticker', 'StickerSet', 'MaskPosition', 'InputSticker', ];
        $MethodOfStickers = [ 'sendSticker', 'getStickerSet', 'getCustomEmojiStickers', 'uploadStickerFile', 'createNewStickerSet', 'addStickerToSet', 'setStickerPositionInSet', 'deleteStickerFromSet', 'replaceStickerInSet', 'setStickerEmojiList', 'setStickerKeywords', 'setStickerMaskPosition', 'setStickerSetTitle', 'setStickerSetThumbnail', 'setCustomEmojiStickerSetThumbnail', 'deleteStickerSet', ];

        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            if($newType = BotApiType::parseHtml($h4) AND in_array($newType->name, $TypesOfStickers)){
                $Types[] = $newType;
            }
        }
        echo 'Stickers будут сохранены как Types ';
    }
    elseif($key == 'Payments'){
        $TypesOfPayments = [
            'LabeledPrice',
            'Invoice',
            'ShippingAddress',
            'OrderInfo',
            'ShippingOption',
            'SuccessfulPayment',
            'RefundedPayment',
            'ShippingQuery',
            'PreCheckoutQuery',
            'PaidMediaPurchased',
            'RevenueWithdrawalStatePending',
            'RevenueWithdrawalStateSucceeded',
            'RevenueWithdrawalStateFailed',
            'TransactionPartnerUser',
            'TransactionPartnerFragment',
            'TransactionPartnerTelegramAds',
            'TransactionPartnerOther',
            'StarTransaction',
            'StarTransactions',
        ];
        $MethodOfPayments = [
            'sendInvoice',
            'createInvoiceLink',
            'answerShippingQuery',
            'answerPreCheckoutQuery',
            'getStarTransactions',
            'refundStarPayment',
            ];

        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            if($newType = BotApiType::parseHtml($h4) AND in_array($newType->name, $TypesOfPayments)){
                $Types[] = $newType;
            }
        }
        echo 'Payments будут сохранены как Types ';
    }
    elseif($key == 'Games'){
        $TypesOfGames = ['Game', 'CallbackGame', 'GameHighScore',  ];
        $MethodOfGames = [ 'setGameScore', 'getGameHighScores', ];

        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            if($newType = BotApiType::parseHtml($h4) AND in_array($newType->name, $TypesOfGames)){
                $Types[] = $newType;
            }
        }

    }
    elseif($key == 'Inline mode'){

        $MethodOfInline = [
            'answerInlineQuery',
            'answerWebAppQuery',
            ];
        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            //Тут от обратного. Много обьектов и только 2 метода
            if($newType = BotApiType::parseHtml($h4) AND !in_array($newType->name, $MethodOfInline)){
                $Types[] = $newType;
            }
        }

    }
    elseif($key == 'Getting updates'){
        $TypesOfUpdates = [
            'Update',
            'WebhookInfo',
        ];
        $MethodOfUpdates = [
            'getUpdates',
            'setWebhook',
            'deleteWebhook',
            'getWebhookInfo',
        ];
        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            if($newType = BotApiType::parseHtml($h4) AND in_array($newType->name, $TypesOfUpdates)){
                $Types[] = $newType;
            }
        }
        echo ' тут нужен обработчик по сохраниению методов. Строка: '.__LINE__.PHP_EOL;
    }
    elseif ($key == 'Telegram Passport'){//Telegram Passport
        $TypesOfPassport = [
            'PassportData',
            'PassportFile',
            'EncryptedPassportElement',
            'EncryptedCredentials',
            'PassportElementErrorDataField',
            'PassportElementErrorFrontSide',
            'PassportElementErrorReverseSide',
            'PassportElementErrorSelfie',
            'PassportElementErrorFile',
            'PassportElementErrorFiles',
            'PassportElementErrorTranslationFile',
            'PassportElementErrorTranslationFiles',
            'PassportElementErrorUnspecified',
            ];
        $MethodOfPassport = [
            'setPassportDataErrors',
            ];

        $h4exploaded =  explode('<h4>', $value);
        foreach ($h4exploaded as $h4){
            $r = $h4;
            if($newType = BotApiType::parseHtml($h4) AND in_array($newType->name, $TypesOfPassport)){
                $Types[] = $newType;
            }
        }

    }


}

foreach($Types AS $Type){
    if($Type instanceof BotApiEntity){
        $Type->__Save();
    }
}
foreach($Methods AS $Method){
    if($Method instanceof MethodForBotApi){
        $Method->__Save();
    }
}

$AllEntityTypes = ParamForBotApiType::GetAllTypes();
$AllTgTypes = BotApiType::GetAllTgTypes();

Log::getInstance()->Echo();


