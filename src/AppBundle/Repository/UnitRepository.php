<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UnitRepository extends EntityRepository
{
    public function setOrderCount(int $id_kingdom, int $unit_id, int $orderCount, int $timePU, string $now, string $readyOn){
        $db = $this->getEntityManager()->getConnection();

        $set_orderCount = $db->prepare("update kngdm_unit set order_count=? 
where kngdm_unit.id_kingdom=? and kngdm_unit.id_unit=?;");
        $set_orderCount->execute([ $orderCount, $id_kingdom, $unit_id]);

        $create_event = $db->prepare("create EVENT IF NOT EXISTS `OrderCount".$id_kingdom.$unit_id."`
        ON SCHEDULE 
            EVERY ".$timePU." SECOND 
            STARTS '".$now."' 
            ENDS '".$readyOn."'
        DO 
            update kngdm_unit set `count` = if((select order_count from kngdm_unit 
            where id_kingdom=".$id_kingdom." and id_unit=".$unit_id.") > 0, ((
            select `count` from kngdm_unit 
            where id_kingdom=".$id_kingdom." and id_unit=".$unit_id.") + 1), (
            select `count` from kngdm_unit 
            where id_kingdom=".$id_kingdom." and id_unit=".$unit_id."))
            where id_kingdom=".$id_kingdom." and id_unit=".$unit_id.";");
        $create_event->execute();
    }

    public function getNBLOfUnit(string $unit_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNBLOfUnit(?);");
        $query->execute([ $unit_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getNPUOfUnit(string $unit_name){
        $db = $this->getEntityManager()->getConnection();

        $query = $db->prepare("CALL GetNPUOfUnit(?);");
        $query->execute([ $unit_name ]);

        return $query->fetchAll(\PDO::FETCH_ASSOC);
    }
}