<?php
/*

Project Name: ChatTag
Project Release: 10.03.2020 22:20
Project Last Update: 03.05.2020 22:21

Comment: Заказать плагин vk.com/anideadjp.

*/

namespace ChatTag;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

/** @var Config */
public $config;

public $purechat;
 
  public function onEnable(){
    @mkdir($this->getDataFolder());
        $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML, [
            "PureChat" => false,
            "seconds" => 10
        ]);
    
    $this->purechat = $this->getServer()->getPluginManager()->getPlugin("PureChat");

    if($this->config->get("PureChat") == true){
     if(!$this->purechat){
      $this->getLogger()->info('PureChat plugin not found');
      $this->getServer()->getPluginManager()->disablePlugin($this);
     }
    }else{
     $this->purechat = false;
    }

    $this->getServer()->getPluginManager()->registerEvents($this,$this);
  }

  public function onChat(PlayerChatEvent $event){
   $player = $event->getPlayer();
   $message = $event->getMessage();
   $config = $this->config;
  
   if($config->get("PureChat") == true){
    $player->setNameTag($message . "\n" . $player->getNameTag());
    $this->getScheduler()->scheduleRepeatingTask(new ChatTask($this, $player, $config, $this->purechat), 20);
   }else{
    $player->setNameTag($message);
    $this->getScheduler()->scheduleRepeatingTask(new ChatTask($this, $player, $config), 20);
   }

  }

}

class ChatTask extends Task {

  public function __construct($plugin, $player, $config, $purechat = null)
  {
    $this->config = $config;
    $this->seconds = 0;
    $this->player = $player;
    $this->plugin = $plugin;
    $this->purechat = $purechat;
  }

  public function onRun(int $currentTick){
  
   if($this->seconds == $this->config->get("seconds")) {

    if($this->purechat == null){
     $this->player->setNameTag($this->player->getName());
    }else{
     $this->player->setNameTag($this->purechat->getNametag($this->player));
    }

    $this->plugin->getScheduler()->cancelTask($this->getTaskId());
    $this->seconds = 0;
   }

   $this->seconds++;
  }

}
