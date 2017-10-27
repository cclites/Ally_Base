<?php
namespace App\Traits;

trait PreventsDelete {
    public function delete() {
        throw new \Exception('Delete not permitted.');
    }
}
