<?php

namespace App\Traits;

trait TimeElapsed
{
    public function timeToString(\DateTime $time): string
    {
        $now = new \DateTime('now', new \DateTimeZone('Asia/Tokyo'));
        $diff = $now->diff($time);

        if ($diff->y > 0) {
            return $time->format('Y年n月j日');
        } elseif ($diff->m > 0) {
            return $time->format('n月j日');
        } elseif ($diff->d > 0) {
            return $diff->format('%d') . '日前';
        } elseif ($diff->h > 0) {
            return $diff->format('%h') . '時間前';
        } elseif ($diff->i > 0) {
            return $diff->format('%i') . '分前';
        } elseif ($diff->s > 0) {
            return $diff->format('%s') . '秒前';
        }

        return '今';
    }
}