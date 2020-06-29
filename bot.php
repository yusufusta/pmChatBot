<?php
require "./vendor/autoload.php";
use Jajo\JSONDB;

$Bot = new Boting\Boting();
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}


if (empty(getenv("ADMIN_ID"))) {
    echo "Please give admin id";
    $Admin = 452321614;
} else {
    $Admin = $_ENV["ADMIN_ID"];
}

if (empty(getenv("DB_TUR"))) {
    $tur = 0;
    $db = new JSONDB(__DIR__);
} else {
    if (getenv("DB_TUR") == "json") {
        $tur = 0;
        $db = new JSONDB( __DIR__ );
    } elseif (getenv("DB_TUR") == "db") {
        $tur = 1;
        if (empty(getenv("DATABASE_URL"))) {
            $url = parse_url($_ENV["CLEARDB_DATABASE_URL"]);
        } else {
            $url = parse_url($_ENV["DATABASE_URL"]);
        }
        try {        
            $db = new PDO("pgsql:host=" . $url["host"] . ";port=" . $url["port"] . ";dbname=" . ltrim($url["path"], "/"), $url["user"], $url["pass"]);
            $db->exec("CREATE TABLE IF NOT EXISTS msg(
                mid integer,
                fid integer,
                fmid integer,
                fname text,
                fusername text,
                time integer);");
        } catch ( PDOException $e ){
            print $e->getMessage();
        }        
    }
}

$START = "Merhaba! Bu botu @Fusuf'a ulaşmak için kullanabilirsiniz. Mesaj/ses/sticker/gif/dosya/fotoğraf atabilirsiniz. Admin'im bunu en yakın zamanda görüp cevaplıyacaktır.";

$Bot->command("/\/start/", function ($Update, $Match) use ($Bot, $START) {
    $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "parse_mode" => "markdown", "text" => $START]);
});

$Bot->command("/\/link/", function ($Update, $Match) use ($Bot, $db, $tur) {
    if (empty($Update["message"]["reply_to_message"]["message_id"])) {
        $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "parse_mode" => "markdown", "text" => "*Lütfen bir mesaja yanıt verin.*"]);   
    }
    $MId = $Update["message"]["reply_to_message"]["message_id"];
    if ($tur == 0) {
        $fid = $db->select('fid, fmid, fname, fusername')
        ->from('msg.json')
        ->where(['mid' => $MId])
        ->get()[0];
    } else {
        $query = $db->prepare("SELECT fid, fmid, fname, fusername FROM msg WHERE mid = " . $MId);
        $query->execute();
        $fid = $query->fetch();
    }
if (empty($fid)) {
        $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "reply_to_message_id" => $Update["message"]["message_id"], "parse_mode" => "markdown", "text" => "*Mesaj veritabanında bulunamadı. Yanıt gönderemezsiniz.*"]); 
        return;
    }
        
    $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "parse_mode" => "markdown", "text" => "*İşte aradığınız kullanıcı:* [" . $fid["fname"] . "](tg://user?id=" . $fid["fid"] . ")\n*Kullanıcı adı:* @" . $fid["fusername"]]);
});

$Bot->on(["animation", "audio", "document", "photo", "sticker", "video", "video_note", "voice", "contact", "dice", "location", "text"], function ($Update) use ($Bot, $Admin, $db, $tur) {
    if ($Update["message"]["chat"]["type"] !== "private") {
        return;
    }
    if ($Update["message"]["chat"]["id"] == $Admin) {
        if(empty($Update["message"]["reply_to_message"])) {
            $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "reply_to_message_id" => $Update["message"]["message_id"], "parse_mode" => "markdown", "text" => "**Lütfen bir mesaja yanıt ver.**"]); 
            return;
        } else {
            $MId = $Update["message"]["reply_to_message"]["message_id"];
            if ($tur == 0) {
                $fid = $db->select('fid, fmid')
	            ->from('msg.json')
	            ->where(['mid' => $MId])
                ->get()[0];
            } else {
                $query = $db->prepare("SELECT fid, fmid FROM msg WHERE mid = " . $MId);
                $query->execute();
                $fid = $query->fetch();
            }
            if (empty($fid)) {
                $Bot->sendMessage(["chat_id" => $Update["message"]["chat"]["id"], "reply_to_message_id" => $Update["message"]["message_id"], "parse_mode" => "markdown", "text" => "*Mesaj veritabanında bulunamadı. Yanıt gönderemezsiniz.*"]); 
                return;
            }
            
            if (!empty($Update["message"]["animation"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "animation" => $Update["message"]["animation"]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendAnimation($Array); 
            } elseif (!empty($Update["message"]["audio"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "audio" => $Update["message"]["audio"]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendAudio($Array); 
            } elseif (!empty($Update["message"]["document"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "document" => $Update["message"]["document"]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendDocument($Array); 
            } elseif (!empty($Update["message"]["photo"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "photo" => array_reverse($Update["message"]["photo"])[0]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendPhoto($Array); 
            } elseif (!empty($Update["message"]["sticker"])) {
                $Bot->sendSticker(["chat_id" => $fid["fid"], "reply_to_message_id" => $fid["fmid"], "sticker" => $Update["message"]["sticker"]["file_id"]]); 
            } elseif (!empty($Update["message"]["video"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "video" => $Update["message"]["video"]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendVideo($Array); 
            } elseif (!empty($Update["message"]["video_note"])) {
                $Bot->sendVideoNote(["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "video_note" => $Update["message"]["video_note"]["file_id"]]); 
            } elseif (!empty($Update["message"]["voice"])) {
                $Array = ["chat_id" => $fid["fid"], "parse_mode" => "markdown", "reply_to_message_id" => $fid["fmid"], "video_note" => $Update["message"]["voice"]["file_id"]];
                if (!empty($Update["message"]["caption"])) $Array["caption"] = $Update["message"]["caption"];
                $Bot->sendVoice($Array); 
            } elseif (!empty($Update["message"]["contact"])) {
                $Bot->sendContact(["chat_id" => $fid["fid"], "reply_to_message_id" => $fid["fmid"], "phone_number" => $Update["message"]["contact"]["phone_number"], "first_name" => $Update["message"]["contact"]["first_name"]]); 
            } elseif (!empty($Update["message"]["dice"])) {
                $Bot->sendDice(["chat_id" => $fid["fid"], "reply_to_message_id" => $fid["fmid"], "dice" => $Update["message"]["dice"]["emoji"]]); 
            } elseif (!empty($Update["message"]["location"])) {
                $Bot->sendLocation(["chat_id" => $fid["fid"], "reply_to_message_id" => $fid["fmid"], "latitude" => $Update["message"]["location"]["latitude"], "longitude" => $Update["message"]["location"]["longitude"]]); 
            } elseif (!empty($Update["message"]["text"])) {
                $Bot->sendMessage(["chat_id" => $fid["fid"], "reply_to_message_id" => $fid["fmid"], "text" => $Update["message"]["text"],"parse_mode" => "markdown",]); 
            } 
        } 
    } else {
        $id = $Bot->forwardMessage(["chat_id" => $Admin, "from_chat_id" => $Update["message"]["chat"]["id"], "message_id" => $Update["message"]["message_id"]])["result"]["message_id"];
        if (!empty($Update["message"]["sticker"])) {
            $Bot->sendMessage(["chat_id" => $Admin, "parse_mode" => "markdown", "reply_to_message_id" => $id, "text" => "*Gönderen kişi:* [" . $Update["message"]["from"]["first_name"] . "](tg://user?id=" . $Update["message"]["chat"]["id"] . ")"]); 
        }
        if (!empty($Update["message"]["from"]["username"])) {
            $username = $Update["message"]["from"]["username"];
        } else {
            $username = "No";
        }
        if ($tur == 0) {
            $db->insert('msg.json', ["mid" => $id, "fmid" => $Update["message"]["message_id"], "fid" => $Update["message"]["from"]["id"], "fname" => $Update["message"]["from"]["first_name"], "fusername" => $username, "time" => time()]);
        } else {
            $db->prepare("INSERT INTO msg (mid, fmid, fid, fname, fusername, time) VALUES (?,?,?,?,?,?)")->execute([$id, $Update["message"]["message_id"], $Update["message"]["from"]["id"], $Update["message"]["from"]["first_name"], $username, time()]);
        }
    }
});

if (empty(getenv("BOT_TOKEN"))) {
    echo "Please add token";
    die();
} else {
    $Bot->Handler(getenv("BOT_TOKEN"));
}
