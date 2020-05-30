<?php
namespace Kkevin14;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ServerSettingsRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ServerSettingsResponsePacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pocketmine\Server;

use pocketmine\Player;

use onebone\economyapi\EconomyAPI;

class KCM extends PluginBase implements Listener{

  public $tit = '§f§l[ §8system §d| §8돈보내기 §f]';

  public $id = [
    1231235656
  ];
  public $dd = [];

  public function onEnable(){
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }

  public function sendUI (Player $player, $code, $data) {
    $packet = new ModalFormRequestPacket();
    $packet->formId = $code;
    $packet->formData = json_encode ($data);
    $player->dataPacket ($packet);
  }

  public function onDamage(EntityDamageByEntityEvent $event){
    $entity = $event->getEntity();
    $damager = $event->getDamager();
    if($entity instanceof Player && $damager instanceof Player){
      $this->dd[$damager->getName()] = $entity->getName();
      if($damager->isSneaking()){
        $this->sendUI($damager, $this->id[0], [
          'type' => 'custom_form',
          'title' => '돈보내기UI',
          'content' => [
						    [
							       'type' => 'label',
							       'text' => '아래에 금액을 입력!!'
						      ],
						      [
							       'type' => 'input',
							       'text' => '돈보내기 | 금액을 입력하세요!',
							       'placeholder' => '예) 1000000'
						      ]
					     ]
        ]);
      }
    }
  }

  public function Datareceive(DataPacketReceiveEvent $event){
    $player = $event->getPlayer();
    $packet = $event->getPacket();
    $name = $player->getName();
     if($packet instanceof ModalFormResponsePacket){
       $id = $packet->formId;
       $val = json_decode($packet->formData, true);
       if($id === $this->id[0]){
         if($val === null){
           $player->sendMessage('§l§c금액을 정확하게 입력해주세요!');
           return true;
         }
         if($val[1] < 0){
           $player->sendMessage('§l§c금액은 0보다 커야합니다!');
           return true;
         }
         if(!is_numeric($val[1])){
           $player->sendMessage('§l§c금액은 숫자여야 합니다!');
           return true;
         }
         $wasd = $this->dd[$name];
         $www = Server::getInstance()->getPlayer($wasd);
         EconomyAPI::getInstance()->reduceMoney($player, $val[1]);
         EconomyAPI::getInstance()->addMoney($wasd, $val[1]);
         $player->sendMessage('§l§f' . $wasd . '님에게 ' . $val[1] . '원을 보냈습니다!');
         $www->sendMessage('§l§f' . $name . '님께서 ' . $val[1] . '원을 보내셨습니다!');
       }
     }
  }

}
