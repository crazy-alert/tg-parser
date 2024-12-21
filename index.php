<?php
$url = 'https://core.telegram.org/bots/api';
$OnlyMDs = [
    'Recent changes',
    'Authorizing your bot',
    'Making requests',
    'Getting updates',
    'Formatting options',
    'Using a Local Bot API Server'
];
spl_autoload_register(function($name) {$file = __DIR__ . DIRECTORY_SEPARATOR . $name . '.php';if(file_exists($file)){require_once $file;}});
require_once 'convertToCamelCase.php';

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

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);
$html = curl_exec($ch);
curl_close($ch);

$Types = [];
$Methods = [];
$MDs = [];
$h4exploaded = [];
$h3exploded = explode('<h3>', $html);
foreach ($h3exploded as $item => $h3){
    preg_match_all( '~</a>[\w\s]+</h3>~', $h3, $h3item );

    if(isset($h3item[0][0])){
        $key =  strip_tags($h3item[0][0]);
        $body = $h3;
        $H3[$key] = $body;
    }

}

foreach ($H3 AS $key => $value) {
    $MDs[] = new MD(title: $key, html: '<h3>'.$value,  urlFoAbsolute: $url);
    if (in_array($key, $OnlyMDs)) { continue; }
    $h4exploaded = array_merge($h4exploaded, explode('<h4>', $value));
}

foreach ($h4exploaded as $h4){
    preg_match_all( '~<li><a href="([^"]+)">([^<]+)</a></li>~', $h4, $extendeds);

        if ((str_contains($h4, '<th>Field</th>') and str_contains($h4, '<th>Type</th>')) OR (str_contains($h4, '<p>This object represents'))  ) {
            try {
                $Types[] = BotApiType::createType($h4);
            }
            catch (Throwable $e) {
                echo $h4, ' Исключение: ', $e->getMessage(), PHP_EOL;
            }
        }
        elseif ((str_contains($h4, '<th>Parameter</th>') and str_contains($h4, '<th>Required</th>')) OR str_contains($h4, 'Use this method') OR str_contains($h4, 'A simple method')) {
            try {
                $Methods[] = BotApiMethod::parseHtml($h4);
            }
            catch (Throwable $e) {
                echo $h4, ' Исключение: ', $e->getMessage(), PHP_EOL;
            }
        }
        elseif(is_array($extendeds[2]) AND count($extendeds[2])){
             preg_match_all( '~</a>[\w\s]+</h4>~', $h4, $matches );

            AbstractObject::Add(strip_tags($matches[0][0]), $extendeds[2], strip_tags($h4));

       }
        else {
            $undefindEntity [] = $h4;
        }
}

$rootDir = BotApiEntity::$ParserFolderName;
if(!file_exists($rootDir)){  mkdir($rootDir); }
foreach ($MDs AS $MD){
    $file = $rootDir.DIRECTORY_SEPARATOR.convertToCamelCase($MD->title).'.md';
    $result = file_put_contents($file, $MD->body);
}

$dirForTypes =  $rootDir.DIRECTORY_SEPARATOR.BotApiType::$EntityFolderName;
if(!file_exists($dirForTypes)){ mkdir($dirForTypes); }
foreach($Types AS $Type){
    if($Type instanceof BotApiType){
        $result = $Type -> __Save( namespace: $dirForTypes, folder: $dirForTypes);
    }
}
$AbstractTypes = AbstractObject::GetList();
foreach($AbstractTypes AS $AbstractType){
    if($AbstractType instanceof AbstractObject){
        $result = $AbstractType -> __Save( namespace: $dirForTypes, folder: $dirForTypes);
    }
}



$dirForMethods =  $rootDir.DIRECTORY_SEPARATOR.BotApiMethod::$EntityFolderName;
if(!file_exists($dirForMethods)){ mkdir($dirForMethods); }
foreach($Methods AS $Method){
    if($Method instanceof  BotApiMethod){
        $result = $Method -> __Save( namespace: $dirForMethods, folder: $dirForMethods);
    }
}


$r = $undefindEntity;
Log::getInstance()->Echo();


