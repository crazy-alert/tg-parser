<?php



$InputUpdate = '{
"update_id":781185066,
"callback_query":{
	"id":"5004208886754512843",
	"from":{
		"id":1112223333333,
		"is_bot":false,
		"first_name":"userFirstName",
		"username":"user_Unique_username",
		"language_code":"ru"
	},
	"message":{
		"message_id":1708,
		"from":{
			"id":999900000,
			"is_bot":true,
			"first_name":"bot non unique title",
			"username":"bot_Unique_username"
		}
		,
		"chat":{
			"id":1112223333333,
			"first_name":"userFirstName",
			"username":"user_Unique_username",
			"type":"private"
		}
		,
		"date":1736247694,
		"reply_to_message":{
			"message_id":1707,
			"from":{
				"id":1112223333333,
				"is_bot":false,
				"first_name":"userFirstName",
				"username":"user_Unique_username",
				"language_code":"ru"
			}
			,
			"chat":{
				"id":1112223333333,
				"first_name":"userFirstName",
				"username":"user_Unique_username",
				"type":"private"
			}
			,
			"date":1736247691,
			"text":"text ppp"
		}
		,
		"text":"text gggg",
		"entities":[{
				"offset":47,
				"length":10,
				"type":"code"
			},{
				"offset":60,
				"length":39,
				"type":"italic"
			}],
		"reply_markup":{
			"inline_keyboard":[[{
				"text":"text hsdhsfds",
				"callback_data":"SEND_MESSAGE_FOR_USER_"
			}],[{
				"text":"text ttt",
				"callback_data":"CANCEL_MESSAGE_FOR_USER_"}
		]]}
	},
	"chat_instance":"7402812123110142873",
	"data":"SEND_MESSAGE_FOR_USER_"}
	}';

$InputUpdate = json_decode($InputUpdate, true);
$Update = new Parser\Update(input:$InputUpdate);

if($Update->callback_query->message->reply_to_message->from->id !== (int)1112223333333){
    Throw new Exception('               ❗️ ❗️ ❗️ Условие не выполнено  ❗️ ❗️ ❗');
}

