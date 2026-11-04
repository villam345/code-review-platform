<?php
require_once __DIR__ . '/../../app/bootstrap.php';
require_method('GET'); require_admin();
$row = db()->query("SELECT COUNT(*) total,
    SUM(status IN ('pending','in_review')) pending,
    SUM(status IN ('reviewed','revision_requested','rejected')) completed
    FROM submissions")->fetch();
json_response(['success'=>true,'stats'=>['total'=>(int)$row['total'],'pending'=>(int)$row['pending'],'reviewed'=>(int)$row['completed']]]);
