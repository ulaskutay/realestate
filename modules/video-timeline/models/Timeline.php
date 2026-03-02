<?php
/**
 * Video Timeline - Composition model
 */
class VideoTimelineTimeline extends Model {
    protected $table = 'video_timelines';

    public function getByUser($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` WHERE user_id = ? ORDER BY updated_at DESC",
            [$userId]
        );
    }

    public function getAllOrdered() {
        return $this->db->fetchAll(
            "SELECT * FROM `{$this->table}` ORDER BY updated_at DESC"
        );
    }
}
