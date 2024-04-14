<?php

declare(strict_types=1);

namespace forms_demo;

use forms\CustomForm;
use forms\CustomFormResponse;
use forms\element\Dropdown;
use forms\element\Input;
use forms\element\Label;
use forms\element\Slider;
use forms\element\StepSlider;
use forms\element\Toggle;
use forms\menu\Button;
use forms\menu\Image;
use forms\MenuForm;
use forms\ModalForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		if($command->getName() === "form" and count($args) >= 1){
			switch($args[0]){
				case "modal":
					$form = $this->createModalForm();
					break;
				case "menu":
					$form = $this->createMenuForm();
					break;
				case "custom":
					$form = $this->createCustomForm();
					break;
				default:
					return false;
			}

			$players = [];
			for($argIdx = 1; isset($args[$argIdx]); ++$argIdx){
				$player = $this->getServer()->getPlayerExact($args[$argIdx]);
				if($player === null){
					$sender->sendMessage(TextFormat::RED . "Can't find a player by name " . $args[$argIdx]);
					return true;
				}
				$players[] = $player;
			}
			if(count($players) === 0){
				if(!($sender instanceof Player)){
					$sender->sendMessage(TextFormat::RED . "Please provide some players to send the form to!");
					return true;
				}
				$players[] = $sender;
			}
			foreach($players as $player){
				$player->sendForm($form);
			}
			return true;
		}
		return false;
	}

	private function createModalForm() : ModalForm{
		return new ModalForm("A small question", "Is our server cool?",
			//result of pressing the "yes" / "no" button is written to a variable $choice
			function(Player $player, bool $choice) : void{
				$player->sendMessage($choice ? "Thank you" : "We will try to become better");
			}
		);
	}

	private function createMenuForm() : MenuForm{
		return new MenuForm("Select server", "Choose server", [
			//buttons without icon
			new Button("SkyWars #1"),
			new Button("SkyWars #2"),
			//URL and path are supported for image
			new Button("SkyWars #3", Image::url("https://static.wikia.nocookie.net/minecraft_gamepedia/images/f/f0/Melon_JE2_BE2.png")),
			new Button("SkyWars #4", Image::path("textures/items/apple.png")),
		], function(Player $player, Button $selected) : void{
			$player->sendMessage("You selected: " . $selected->text);
			$player->sendMessage("Index of button: " . $selected->getValue());
		});
	}

	private function createCustomForm() : CustomForm{
		return new CustomForm("Enter data", [
			new Dropdown("Select product", ["beer", "cheese", "cola"]),
			new Input("Enter your name", "Bob"),
			new Label("I am label!"), //Note: get<BaseElement>() does not work with label
			new Slider("Select count", 0.0, 100.0, 1.0, 50.0),
			new StepSlider("Select product", ["beer", "cheese", "cola"]),
			new Toggle("Creative", true),
		], function(Player $player, CustomFormResponse $response) : void{
			/**
			 * type-hints for PHPStan
			 * @var string $product1
			 * @var string $username
			 * @var int|float $count
			 * @var string $product2
			 * @var bool $enableCreative
			 */
			[$product1, $username, $count, $product2, $enableCreative] = $response->getValues();

			//Note: `$count` can be of type `int|float`, so use type casting

			$player->sendMessage("You selected: $product1");
			$player->sendMessage("Your name is $username");
			$player->sendMessage("Count: $count");
			$player->sendMessage("You selected: $product2");
			$player->setGamemode($enableCreative ? GameMode::CREATIVE : GameMode::SURVIVAL);
		});
	}
}
