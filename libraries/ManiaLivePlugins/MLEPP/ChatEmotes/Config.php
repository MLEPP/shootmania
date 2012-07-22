<?php

namespace ManiaLivePlugins\MLEPP\ChatEmotes;

class Config extends \ManiaLib\Utils\Singleton
{
    public $bootme = array("chooses the life, bye all!", "has the illusion that he has got something better to do!", "takes the blue pill!", "exits, stage left!", "chooses to boot to the life!", "has seen the light. Oh the enlightenment!",
		"Beam me up Scotty!", "has left the building");
	public $hi = array("Hi!", "Yo!", "Aloha!", "Hola!", "Hey!", "Hello!", "Hi to all!", "Yo Duudz!", "Aloha everybody?", "Hola!", "Ahoi sailors!", "Hey jude.. ehem.. all :)", "Hello strangers!", "O Hai, can I come in?", "Yop!", "Greetings Earthlings!");
	public $hi2 = array("Hi", "Yo", "Hoi", "Aloha", "Hola", "Hey", "Hello", "Hola", "How are you doing", "Nice to see you here", "Yop");
	public $bb = array("Bye Bye!", "Latorz!", "See ya later!", "Goodbye!", "Hasta la vista!", "Ciao!", "is out of here, bye!", "bye all!!");
	public $bb2 = array("Bye Bye", "Latorz", "See ya later", "Goodbye", "Hasta la vista", "Ciao", "is out of here, bye", "hope to see you soon");
	public $thx = array("Thanks!", "Thank you!", "Many thanks!", "Shows gratitude", "is grateful", "bows deep");
	public $thx2 = array("Thanks", "Thank you", "Many thanks", "That is very kind of you", "How thoughtfull of you", "Great! I needed that");
	public $lol = array("is rolling on the floor, laughing his *ss off", "Lo0o0ol", "is having a lot of laugh", "is laughing", "is laughing out loud", "xD", "Lulz", "That is just sooo funny!", "Hahahaha", "Hehehe", "lol", "heh");
	public $lol2 = array("is rolling on the floor, laughing his *ss off because of", "Lo0o0ol", "is having a lot of laugh", "is laughing about", "is laughing out loud thanks to", "xD", "Lulz", "That is just sooo funny", "hahaha, you're killing me",
		"you're making me laugh");
	public $brb = array("Be Right Back!", "Back in a sec!", "One moment, please...", "I'll be back!", "Hold on...", "Have to let you alone for a sec...");
	public $brb2 = array("Be Right Back", "Back in a sec", "One moment, please", "I'll be back", "Hold on...", "Have to let you alone for a sec", "Please wait, i'll be right back");
	public $afk = array("Away From Keyboard", "Where did my keyboard go?", "Letting my game spec itself for a moment",
		"I'll be speccing, but i'm not here so don't panic");
	public $afk2 = array("Away From Keyboard", "Wait,wait, the phone just rang", "Needs to press anykey on keyboard, but can't find it", "Letting my game spec itself for a moment", "I'll be speccing, but i'm not here so don't panic");
	public $gg = array("Congratulations!", "Good Game!", "Well Done!", "Great Game!");
	public $gg2 = array("Congratulations", "Good Game", "Well Done", "Great Game");
	public $nl = array("Nice one!", "Well driven, mate!", "Nice Line!", "How on earth you did that time?", "Damn. You are pro and i'm.. well i'm noob ;(", "Wo0o0o0t! You rule!", "You're a Pro!", "Now that is some great skills!", "Very impressing!",
		"O Master, please teach me!", "Where did you learn how to do that?");
	public $nl2 = array("Nice one", "Well driven", "Nice Line", "How on earth you did that time", "Damn. You are pro and i'm.. well i'm noob ;( ", "Wo0o0o0t! You rule", "Have you ever thought of becoming Pro", "That was some skilled driving",
		"Very impressing", "Please teach me how to do that", "You're the master", "Can I have the address of your driving school");
	public $bgm = array("Bad game for me!", "Ai, something went terribly wrong here!", "That wasn't me, it was my little brother playing...", "Did we begin yet?", "mumbles something about lag and bugs...", "You are great drivers, teach me please!",
		"is on the phone with a driving school!", "This is not my kind of track!", "That went sooo wrong!", "Just leave me alone now please :(", "Give....me....vitamins...", "That was not the way it supposed to go....");
	public $bgm2 = array("Bad game for me", "Ai, something went terribly wrong here", "That wasn't me, it was my little brother playing...", "Did we begin yet", "You are a great driver, teach me please",
		"is on the phone with a driving school", "This is not my kind of track", "That went sooo wrong", "Just leave me alone now please", "Give....me....vitamins...");
	public $sry = array("Sorry!", "Excuse me!", "I beg your pardon!");
	public $sry2 = array("Sorry", "Excuse me", "I beg your pardon", "sends flowers to");
	public $glhf = array("Good Luck & Have Fun!");
	public $glhf2 = array("Good Luck & Have Fun");
	public $wb = array("Welcome Back!");
	public $wb2 = array("Welcome back");
	public $omg = array("Oh My God!");
	public $omg2 = array("Oh My God");
	public $buzz = array("Wake up!", "Is anybody home?", "Yohooo, are you there?", "Echo Charly Delta, do you read me, please come in - over!", "This is Earth calling!", "Bzzzzzzzzzzz", "starts to get impatient!");
	public $buzz2 = array("Wake up", "Are you there", "This is Earth calling", "Did i miss your /afk", "Are you still with me", "is calling", "starts to get impatient");
	public $eat = array("is going to eat something", "I'm hungry, i need to go eat something!", "is going to take a bite!", "is grabbing something to snack!");
	public $eat2 = array("I'm going to eat something", "Be right back, i grab something to eat");
	public $drink = array("is going to drink something", "I'm thirsty, i need to go drink something!", "is going to take a drink!", "is grabbing something to drink!");
	public $drink2 = array("I'm going to drink something", "Wait a sec, i'm taking something to drink");
	public $rant = array("?#@*&%!", "\$f00\$oAaaaarrrghhhh!", "omg!", "Grrrrrr", "Noooooooo!", "Son of a bean dip, mother frito!", "oh, poo!", "***Explicit language***", "I've had a perfectly wonderful day. But this wasn't it.", "A Life? Cool! Where can I download one of those from?",
		"I don't suffer from insanity. I enjoy every minute of it.", "I feel like I'm diagonally parked in a parallel universe.", "Okay, who put a stop payment on my reality check?");
	public $rant2 = array("May you never get more than one sheet of toilet paper at a time", "You can't fight ya way out of a paper bag", "If at first you don't succeed, give up", "Try, try and try again until you are ready for bed", "May the force be without you",
		"I'm busy now. Can I ignore you some other time? ", "It is only too easy to catch people's attention by doing something worse than anyone else has dared to do it before", "You are so computer illiterate, you shake your laptop to get the cookies out",
		"You're not important - you're just an NPC", "Wow, you're so slow, is your ping at 999", "Keep talking, someday you'll say something intelligent", "If you had another brain, it would be lonely", "If your brain was chocolate it wouldn't fill an M&M",
		"Genius does what it must, talent does what it can, and you had best do what you're told", "I didn't say it was your fault. I said I was going to blame you", "It IS as bad as you think, and they ARE out to get you", "Nothing is foolproof to a talented fool.",
		"It may be that your sole purpose in life is simply to serve as a warning to others");
	public $ragequit = array("Ragequits!", "Has used the ejecting seat to get out of a tight situation!", "Out of my mind. Back in five minutes.", "Couldn't stand it anymore!", "Banged his head too hard on his keyboard!", "Quits, to take a valium!", "\$f00\$oRoaaarrrrrr!!!");
}
?>
